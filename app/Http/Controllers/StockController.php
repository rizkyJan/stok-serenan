<?php
namespace App\Http\Controllers;
use App\Models\{Product,ProductBatch,StockMovement,StockAdjustment};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class StockController extends Controller {
    public function index(Request $request){
        $q=Product::withSum('batches as total_stock','quantity_available')
            ->withSum(['batches as sellable_stock'=>fn($q)=>$q->where(fn($q)=>$q->whereNull('expires_at')->orWhereDate('expires_at','>=',today()))],'quantity_available');
        if($s=trim((string)$request->query('q','')))$q->where(fn($b)=>$b->where('sku','like',"%{$s}%")->orWhere('name','like',"%{$s}%"));
        return view('stock.index',['products'=>$q->orderBy('name')->paginate(15)->withQueryString()]);
    }
    public function show(Product $product){
        $product->load(['batches'=>fn($q)=>$q->orderByRaw('CASE WHEN expires_at IS NULL THEN 1 ELSE 0 END')->orderBy('expires_at')]);
        $movements=StockMovement::with(['creator','batch'])->where('product_id',$product->id)->latest()->paginate(15);
        return view('stock.show',compact('product','movements'));
    }
    public function adjust(Request $request, ProductBatch $batch){
        $data=$request->validate(['new_quantity'=>'required|integer|min:0|max:100000000','reason'=>'required|string|min:8|max:2000']);
        DB::transaction(function()use($batch,$data){
            $locked=ProductBatch::whereKey($batch->id)->lockForUpdate()->firstOrFail();
            $old=$locked->quantity_available;$new=(int)$data['new_quantity'];$delta=$new-$old;
            if($delta===0)return;
            $adjustment=StockAdjustment::create(['product_batch_id'=>$locked->id,'old_quantity'=>$old,'new_quantity'=>$new,
                'delta'=>$delta,'reason'=>$data['reason'],'created_by'=>auth()->id()]);
            $locked->update(['quantity_available'=>$new]);
            StockMovement::create(['product_id'=>$locked->product_id,'product_batch_id'=>$locked->id,
                'type'=>$delta>0?'adjustment_in':'adjustment_out','quantity_change'=>$delta,
                'reference_type'=>'stock_adjustment','reference_id'=>$adjustment->id,'notes'=>$data['reason'],'created_by'=>auth()->id()]);
        },3);
        return back()->with('success','Hasil stok opname disimpan bersama riwayat koreksi.');
    }
}
