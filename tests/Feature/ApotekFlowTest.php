<?php

use App\Models\{Product,PurchaseInvoice,ProductBatch,Supplier,User};
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('mengarahkan tamu ke halaman login', function () {
    $this->get('/dashboard')->assertRedirect('/login');
});

it('mencatat barang masuk, mengurangi stok FEFO, dan menolak stok tidak cukup', function () {
    $user=User::factory()->create(['role'=>'admin','is_active'=>true]);
    $supplier=Supplier::create(['name'=>'PBF Uji']);
    $product=Product::create(['sku'=>'TEST-001','name'=>'Paracetamol','unit'=>'tablet','minimum_stock'=>10]);
    $this->actingAs($user)->post(route('purchases.store'),[
        'supplier_id'=>$supplier->id,'invoice_no'=>'F-TEST-01',
        'invoice_date'=>'2026-10-08','received_date'=>'2026-10-08','due_date'=>'2026-11-08',
        'discount'=>0,'tax'=>0,'shipping'=>0,
        'items'=>[['product_id'=>$product->id,'purchase_quantity'=>2,'purchase_unit'=>'box',
            'unit_multiplier'=>100,'purchase_unit_cost'=>15000,'batch_number'=>'UJI-01','expires_at'=>'2028-01-01']],
    ])->assertRedirect();
    expect(ProductBatch::where('product_id',$product->id)->sum('quantity_available'))->toBe(200);
    expect((float)PurchaseInvoice::first()->total)->toBe(30000.0);

    $this->actingAs($user)->post(route('stock-outs.store'),[
        'product_id'=>$product->id,'quantity'=>80,'reason'=>'penjualan',
    ])->assertRedirect(route('stock-outs.index'));
    expect(ProductBatch::where('product_id',$product->id)->sum('quantity_available'))->toBe(120);

    $this->actingAs($user)->post(route('stock-outs.store'),[
        'product_id'=>$product->id,'quantity'=>121,'reason'=>'penjualan',
    ])->assertSessionHasErrors('quantity');
    expect(ProductBatch::where('product_id',$product->id)->sum('quantity_available'))->toBe(120);
});

it('mendukung cicilan faktur dengan validasi agar tidak kelebihan bayar', function () {
    $admin=User::factory()->create(['role'=>'admin']);
    $supplier=Supplier::create(['name'=>'PBF Dua']);
    $invoice=PurchaseInvoice::create([
        'supplier_id'=>$supplier->id,'invoice_no'=>'INV-02',
        'invoice_date'=>'2026-10-08','received_date'=>'2026-10-08','due_date'=>'2026-11-08',
        'subtotal'=>100000,'discount'=>0,'tax'=>0,'shipping'=>0,'total'=>100000,'paid_amount'=>0,
        'created_by'=>$admin->id,
    ]);
    $this->actingAs($admin)->post(route('payments.store',$invoice),[
        'paid_at'=>'2026-10-09','amount'=>40000,'method'=>'transfer',
    ])->assertRedirect(route('purchases.show',$invoice));
    expect((float)$invoice->fresh()->paid_amount)->toBe(40000.0);
    $this->actingAs($admin)->post(route('payments.store',$invoice),[
        'paid_at'=>'2026-10-10','amount'=>70000,'method'=>'tunai',
    ])->assertSessionHasErrors('amount');
    expect((float)$invoice->fresh()->paid_amount)->toBe(40000.0);
});

it('melarang petugas mencatat pembayaran dan mengelola akun', function () {
    $staff=User::factory()->create(['role'=>'petugas']);
    $this->actingAs($staff)->get(route('users.index'))->assertForbidden();
    $supplier=Supplier::create(['name'=>'PBF Tiga']);
    $invoice=PurchaseInvoice::create([
        'supplier_id'=>$supplier->id,'invoice_no'=>'INV-03',
        'invoice_date'=>'2026-10-08','received_date'=>'2026-10-08','due_date'=>'2026-11-08',
        'subtotal'=>50000,'discount'=>0,'tax'=>0,'shipping'=>0,'total'=>50000,'paid_amount'=>0,
        'created_by'=>$staff->id,
    ]);
    $this->actingAs($staff)->post(route('payments.store',$invoice),[
        'paid_at'=>'2026-10-09','amount'=>10000,'method'=>'tunai',
    ])->assertForbidden();
});
