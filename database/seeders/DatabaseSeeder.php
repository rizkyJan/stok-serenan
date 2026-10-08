<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
class DatabaseSeeder extends Seeder {
    public function run():void {
        // Tidak membuat akun, stok, maupun transaksi secara otomatis.
        // Gunakan `php artisan apotek:admin` untuk membuat akun administrator.
        // Contoh master data tersedia lewat `php artisan db:seed --class=DemoSeeder`.
    }
}
