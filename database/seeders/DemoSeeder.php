<?php
namespace Database\Seeders;
use App\Models\{Product,Supplier};
use Illuminate\Database\Seeder;
class DemoSeeder extends Seeder {
    public function run():void {
        Supplier::firstOrCreate(['name'=>'PBF Contoh Sehat'],['phone'=>'0271-000000','contact_name'=>'Kontak Demo','notes'=>'DATA SIMULASI — ganti dengan data asli']);
        foreach([
            ['sku'=>'DEMO-001','name'=>'Paracetamol 500 mg','category'=>'Obat','unit'=>'tablet','minimum_stock'=>30],
            ['sku'=>'DEMO-002','name'=>'Vitamin C 500 mg','category'=>'Vitamin','unit'=>'tablet','minimum_stock'=>20],
            ['sku'=>'DEMO-003','name'=>'Masker Medis','category'=>'Alat kesehatan','unit'=>'pcs','minimum_stock'=>50],
        ] as $row) Product::firstOrCreate(['sku'=>$row['sku']],$row);
    }
}
