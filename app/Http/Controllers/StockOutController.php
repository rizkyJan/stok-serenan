<?php
namespace App\Http\Controllers;
use App\Models\{Product,ProductBatch,StockOut,StockMovement};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
class StockOutController extends Controller {
    public function index(Request $request){
        $q=StockOut::with(['product','creator']);
        if($s=trim((string)$request->query('q',''))){$q->whereHas('product',fn($b)=>$b->where('name','like',"%{$s}%")->orWhere('sku','like',"%{$s}%"));}
        return view('stock-outs.index',['outs'=>$q->latest()->paginate(15)->withQueryString()]);
    }
    public function create(){
        $products=Product::where('is_active',true)
            ->withSum(['batches as sellable_stock'=>fn($q)=>$q->where(fn($q)=>$q->whereNull('expires_at')->orWhereDate('expires_at','>=',today()))],'quantity_available')
            ->orderBy('name')->get();
        return view('stock-outs.create',compact('products'));
    }
    public function store(Request $request){
        $data=$request->validate([
            'product_id'=>['required',Rule::exists('products','id')->where('is_active',true)],
            'quantity'=>'required|integer|min:1|max:100000000',
            'reason'=>['required',Rule::in(['penjualan','pemakaian','retur_pbf','rusak','lainnya'])],
            'recipient'=>'nullable|string|max:255','notes'=>'nullable|string|max:3000',
        ]);
        DB::transaction(function()use($data){
            $qty=(int)$data['quantity'];
            // Urutan FEFO, batch tanpa masa kedaluwarsa dipakai terakhir.
            $batches=ProductBatch::where('product_id',$data['product_id'])
                ->where('quantity_available','>',0)
                ->where(fn($q)=>$q->whereNull('expires_at')->orWhereDate('expires_at','>=',today()))
                ->orderByRaw('CASE WHEN expires_at IS NULL THEN 1 ELSE 0 END')
                ->orderBy('expires_at')->orderBy('id')->lockForUpdate()->get();
            if($batches->sum('quantity_available')<$qty){
                throw ValidationException::withMessages(['quantity'=>'Stok layak keluar tidak cukup. Periksa jumlah dan batch kedaluwarsa.']);
            }
            $out=StockOut::create($data+['created_by'=>auth()->id()]);
            foreach($batches as $batch){
                if($qty===0)break;
                $take=min($qty,$batch->quantity_available);
                $batch->decrement('quantity_available',$take);
                StockMovement::create(['product_id'=>$data['product_id'],'product_batch_id'=>$batch->id,'type'=>'out',
                    'quantity_change'=>-$take,'reference_type'=>'stock_out','reference_id'=>$out->id,
                    'notes'=>$data['notes']??null,'created_by'=>auth()->id()]);
                $qty-=$take;
            }
        },3);
        return redirect()->route('stock-outs.index')->with('success','Barang keluar tercatat dan stok otomatis berkurang (FEFO).');
    }
}
