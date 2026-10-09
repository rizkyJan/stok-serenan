<?php

use App\Models\{User, Product, ProductBatch, PurchaseUnit, StockOut, PurchaseInvoice, Supplier};
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('menambah satuan baru langsung dari form dan menolak duplikat', function () {
    $user = User::factory()->create(['role'=>'admin','is_active'=>true]);
    $this->actingAs($user)->postJson(route('purchase-units.store'), ['name'=>'  Lembar  '])
        ->assertCreated()->assertJsonPath('name','lembar');
    $this->assertDatabaseHas('purchase_units', ['name'=>'lembar']);
    $this->actingAs($user)->postJson(route('purchase-units.store'), ['name'=>'LEMBAR'])
        ->assertStatus(422)->assertJsonValidationErrors('name');
});

it('harga jual di master bisa diubah dan histori barang keluar tidak ikut berubah', function () {
    $admin = User::factory()->create(['role'=>'admin','is_active'=>true]);
    $product = Product::create(['sku'=>'PRICE-V4','name'=>'Paracetamol V4','unit'=>'tablet','selling_price'=>1500]);
    ProductBatch::create([
        'product_id'=>$product->id,'batch_number'=>'BT-V4','quantity_received'=>100,
        'quantity_available'=>100,'unit_cost'=>1000,
    ]);
    $this->actingAs($admin)->post(route('stock-outs.store'), [
        'product_id'=>$product->id,'quantity'=>2,'reason'=>'penjualan','recipient'=>'Tester',
    ])->assertRedirect();
    $first = StockOut::firstOrFail();
    expect((float)$first->selling_price_snapshot)->toBe(1500.0)
        ->and((float)$first->sale_total)->toBe(3000.0);

    $product->update(['selling_price'=>2000]);
    $this->actingAs($admin)->post(route('stock-outs.store'), [
        'product_id'=>$product->id,'quantity'=>3,'reason'=>'penjualan','recipient'=>'Tester 2',
    ])->assertRedirect();
    $first->refresh();
    $second = StockOut::latest('id')->firstOrFail();
    expect((float)$first->selling_price_snapshot)->toBe(1500.0)
        ->and((float)$first->sale_total)->toBe(3000.0)
        ->and((float)$second->selling_price_snapshot)->toBe(2000.0)
        ->and((float)$second->sale_total)->toBe(6000.0)
        ->and((int)$product->batches()->sum('quantity_available'))->toBe(95);
});

it('menolak penjualan ketika harga jual master belum diisi, tetapi pemakaian internal boleh', function () {
    $admin = User::factory()->create(['role'=>'admin','is_active'=>true]);
    $product = Product::create(['sku'=>'NO-PRICE-V4','name'=>'Barang tanpa harga','unit'=>'pcs']);
    ProductBatch::create([
        'product_id'=>$product->id,'batch_number'=>'B-PRICE','quantity_received'=>10,
        'quantity_available'=>10,'unit_cost'=>200,
    ]);
    $this->actingAs($admin)->post(route('stock-outs.store'), [
        'product_id'=>$product->id,'quantity'=>2,'reason'=>'penjualan',
    ])->assertSessionHasErrors('product_id');
    expect(StockOut::count())->toBe(0)
        ->and((int)$product->batches()->sum('quantity_available'))->toBe(10);

    $this->actingAs($admin)->post(route('stock-outs.store'), [
        'product_id'=>$product->id,'quantity'=>2,'reason'=>'pemakaian',
    ])->assertRedirect();
    expect(StockOut::count())->toBe(1)
        ->and((int)$product->batches()->sum('quantity_available'))->toBe(8);
});

it('harga jual yang diselundupkan ke form faktur tidak memengaruhi master produk', function () {
    $admin = User::factory()->create(['role'=>'admin','is_active'=>true]);
    $supplier = Supplier::create(['name'=>'PBF V4 Test']);
    $product = Product::create(['sku'=>'INV-V4','name'=>'Obat V4','unit'=>'tablet','selling_price'=>2500]);
    $this->actingAs($admin)->post(route('purchases.store'), [
        'supplier_id'=>$supplier->id,'invoice_no'=>'FAKTUR-V4-1',
        'invoice_date'=>'2026-10-09','received_date'=>'2026-10-09','due_date'=>'2026-11-09',
        'discount_mode'=>'none','discount'=>0,'tax_mode'=>'none','shipping'=>0,
        'items'=>[[
            'product_id'=>$product->id,'purchase_quantity'=>1,'unit_multiplier'=>20,
            'purchase_unit'=>'strip','purchase_unit_cost'=>20000,
            'selling_unit_price'=>999999,'batch_number'=>'B-INV-V4',
        ]],
    ])->assertRedirect();
    expect((float)$product->fresh()->selling_price)->toBe(2500.0)
        ->and(PurchaseInvoice::count())->toBe(1)
        ->and(PurchaseInvoice::firstOrFail()->items()->first()->selling_unit_price)->toBeNull();
});
