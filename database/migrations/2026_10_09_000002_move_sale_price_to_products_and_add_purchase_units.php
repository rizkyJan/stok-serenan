<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Harga jual aktif per satuan dasar, bukan harga pada setiap pembelian PBF.
            $table->decimal('selling_price', 16, 2)->nullable();
        });
        Schema::table('stock_outs', function (Blueprint $table) {
            // Snapshot saat pengeluaran, sehingga perubahan harga master tidak mengubah transaksi lama.
            $table->decimal('selling_price_snapshot', 16, 2)->nullable();
            $table->decimal('sale_total', 16, 2)->nullable();
        });
        Schema::create('purchase_units', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();
            $table->timestamps();
        });
        $units = ['box', 'strip', 'tablet', 'pcs', 'botol', 'vial', 'ampul', 'sachet', 'pak', 'dus', 'tube'];
        // Daftarkan juga satuan lama supaya riwayat pembelian tetap cocok dengan pilihan dropdown.
        foreach (DB::table('purchase_items')->distinct()->pluck('purchase_unit') as $oldUnit) {
            $oldUnit = trim((string) $oldUnit);
            if ($oldUnit !== '' && strlen($oldUnit) <= 50) $units[] = $oldUnit;
        }
        foreach (array_unique($units) as $name) {
            DB::table('purchase_units')->insertOrIgnore([
                'name' => $name, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
        // Harga historis pada purchase_items.selling_unit_price V3 sengaja dipertahankan sebagai arsip,
        // TIDAK disalin otomatis menjadi harga jual master karena mungkin berbeda antar faktur.
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_units');
        Schema::table('stock_outs', fn (Blueprint $table) => $table->dropColumn(['selling_price_snapshot', 'sale_total']));
        Schema::table('products', fn (Blueprint $table) => $table->dropColumn('selling_price'));
    }
};
