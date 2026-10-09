<?php

use App\Models\{User,Supplier,Product,PurchaseDraft,PurchaseInvoice,ProductBatch,SupplierPayment};
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('menyimpan draft tanpa menambah stok dan tagihan', function () {
    $user = User::factory()->create(['role'=>'admin','is_active'=>true]);
    $this->actingAs($user)->post(route('purchases.draft.store'), [
        'invoice_no'=>'DRAFT-UJI', 'notes'=>'Belum final',
        'items'=>[['batch_number'=>'BT-UJI','purchase_quantity'=>4]],
    ])->assertRedirect();
    expect(PurchaseDraft::count())->toBe(1)
        ->and(PurchaseInvoice::count())->toBe(0)
        ->and(ProductBatch::count())->toBe(0);
});

it('tidak mengambil harga jual dari item dan tetap bisa langsung lunas saat finalisasi', function () {
    $user = User::factory()->create(['role'=>'admin','is_active'=>true]);
    $supplier = Supplier::create(['name'=>'PBF Uji V3']);
    $product = Product::create(['sku'=>'V3-001','name'=>'Produk Uji V3','unit'=>'tablet']);
    $this->actingAs($user)->post(route('purchases.store'), [
        'supplier_id'=>$supplier->id,'invoice_no'=>'V3-LUNAS-001',
        'invoice_date'=>'2026-10-09','received_date'=>'2026-10-09','due_date'=>'2026-11-09',
        'recipient_name'=>'Petugas A', 'discount_mode'=>'per_item','payment_mode'=>'lunas',
        'payment_date'=>'2026-10-09','settlement_date'=>'2026-10-09',
        'payment_method'=>'transfer','discount'=>0,'tax_mode'=>'none','shipping'=>0,
        'items'=>[[
            'product_id'=>$product->id,'purchase_quantity'=>2,'unit_multiplier'=>10,
            'purchase_unit'=>'strip','purchase_unit_cost'=>50000,'selling_unit_price'=>6500,
            'batch_number'=>'BATCH-V3','line_discount_percent'=>0,'line_discount_amount'=>0,
        ]],
    ])->assertRedirect();
    $invoice = PurchaseInvoice::firstOrFail();
    expect($invoice->payment_status)->toBe('Lunas')
        ->and((float)$invoice->paid_amount)->toBe(100000.0)
        ->and($invoice->items()->first()->selling_unit_price)->toBeNull()
        ->and(SupplierPayment::count())->toBe(1)
        ->and((int)ProductBatch::sum('quantity_available'))->toBe(20);
});

it('menolak diskon invoice dalam mode khusus diskon item', function () {
    $admin=User::factory()->create(['role'=>'admin','is_active'=>true]);
    $supplier=Supplier::create(['name'=>'PBF Uji Tolak']);
    $product=Product::create(['sku'=>'V3-002','name'=>'Produk Uji','unit'=>'pcs']);
    $this->actingAs($admin)->post(route('purchases.store'),[
        'supplier_id'=>$supplier->id,'invoice_no'=>'V3-MODE-002',
        'invoice_date'=>'2026-10-09','received_date'=>'2026-10-09','due_date'=>'2026-11-09',
        'discount_mode'=>'per_item','discount'=>1000,'tax_mode'=>'none','shipping'=>0,
        'items'=>[ ['product_id'=>$product->id,'purchase_quantity'=>1,'unit_multiplier'=>1,
            'purchase_unit'=>'pcs','purchase_unit_cost'=>10000,'batch_number'=>'BT-V3'] ],
    ])->assertSessionHasErrors('discount_mode');
    expect(PurchaseInvoice::count())->toBe(0);
});
