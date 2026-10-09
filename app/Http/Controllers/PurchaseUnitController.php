<?php
namespace App\Http\Controllers;

use App\Models\PurchaseUnit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PurchaseUnitController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $name = strtolower(trim((string) $request->input('name', '')));
        $request->merge(['name' => $name]);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:50', 'regex:/^[\pL\pN .\/_-]+$/u', Rule::unique('purchase_units', 'name')],
        ], [
            'name.unique' => 'Satuan sudah tersedia. Silakan pilih dari daftar.',
            'name.regex' => 'Nama satuan hanya boleh menggunakan huruf, angka, spasi, titik, garis miring atau tanda hubung.',
        ]);
        $unit = PurchaseUnit::create($data);
        return response()->json(['id' => $unit->id, 'name' => $unit->name], 201);
    }
}
