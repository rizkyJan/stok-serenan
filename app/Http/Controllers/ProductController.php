<?php
namespace App\Http\Controllers;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
class ProductController extends Controller {
    private function stocks($q) {
        return $q->withSum('batches as total_stock','quantity_available')
            ->withSum(['batches as sellable_stock'=>fn($q)=>$q->where(fn($q)=>$q->whereNull('expires_at')->orWhereDate('expires_at','>=',today()))],'quantity_available');
    }
    public function index(Request $request){
        $q=$this->stocks(Product::query());
        if($search=trim((string)$request->query('q',''))){$q->where(fn($b)=>$b->where('sku','like',"%{$search}%")->orWhere('name','like',"%{$search}%"));}
        return view('products.index',['products'=>$q->orderBy('name')->paginate(15)->withQueryString()]);
    }
    public function create(){return view('products.form',['product'=>new Product()]);}
    private function validated(Request $request,?Product $product=null):array {
        return $request->validate([
            'sku'=>['required','string','max:60',Rule::unique('products','sku')->ignore($product?->id)],
            'name'=>'required|string|max:255','category'=>'nullable|string|max:255','unit'=>'required|string|max:50',
            'minimum_stock'=>'required|integer|min:0|max:100000000','notes'=>'nullable|string|max:2000',
            'is_active'=>'required|boolean',
        ]);
    }
    public function store(Request $request){$product=Product::create($this->validated($request));return redirect()->route('products.index')->with('success','Barang '.$product->name.' berhasil ditambah.');}
    public function edit(Product $product){return view('products.form',compact('product'));}
    public function update(Request $request,Product $product){
        $data=$this->validated($request,$product);
        if($product->batches()->exists() && $product->unit!==$data['unit']){return back()->withErrors(['unit'=>'Satuan dasar tidak boleh diubah setelah ada transaksi stok.'])->withInput();}
        $product->update($data);return redirect()->route('products.index')->with('success','Data barang diperbarui.');
    }
}
