<?php
namespace App\Http\Controllers;
use App\Models\Supplier;
use Illuminate\Http\Request;
class SupplierController extends Controller {
    public function index(Request $request){
        $q=Supplier::query();
        if($search=trim((string)$request->query('q','')))$q->where('name','like',"%{$search}%");
        return view('suppliers.index',['suppliers'=>$q->orderBy('name')->paginate(15)->withQueryString()]);
    }
    public function create(){return view('suppliers.form',['supplier'=>new Supplier()]);}
    private function validated(Request $r):array{return $r->validate([
        'name'=>'required|string|max:255','npwp'=>'nullable|string|max:40','phone'=>'nullable|string|max:40','contact_name'=>'nullable|string|max:255',
        'address'=>'nullable|string|max:2000','notes'=>'nullable|string|max:2000','is_active'=>'required|boolean',
    ]);}
    public function store(Request $r){Supplier::create($this->validated($r));return redirect()->route('suppliers.index')->with('success','PBF berhasil ditambah.');}
    public function edit(Supplier $supplier){return view('suppliers.form',compact('supplier'));}
    public function update(Request $r,Supplier $supplier){$supplier->update($this->validated($r));return redirect()->route('suppliers.index')->with('success','Data PBF diperbarui.');}
}
