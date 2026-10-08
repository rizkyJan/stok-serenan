<?php
namespace App\Http\Controllers;
use App\Models\{StockMovement,Product};
use Illuminate\Http\Request;
class ReportController extends Controller {
    private function data(Request $request){
        $filters=$request->validate([
            'from'=>'nullable|date','to'=>'nullable|date|after_or_equal:from',
            'product_id'=>'nullable|integer|exists:products,id',
            'type'=>'nullable|in:in,out,adjustment_in,adjustment_out',
        ]);
        $q=StockMovement::with(['product','batch','creator']);
        if(!empty($filters['from']))$q->whereDate('created_at','>=',$filters['from']);
        if(!empty($filters['to']))$q->whereDate('created_at','<=',$filters['to']);
        if(!empty($filters['product_id']))$q->where('product_id',$filters['product_id']);
        if(!empty($filters['type']))$q->where('type',$filters['type']);
        return $q->latest();
    }
    public function index(Request $request){return view('reports.index',['movements'=>$this->data($request)->paginate(20)->withQueryString(),'products'=>Product::orderBy('name')->get()]);}
    public function export(Request $request){
        $q=$this->data($request);
        return response()->streamDownload(function()use($q){
            $f=fopen('php://output','w');fwrite($f,"\xEF\xBB\xBF");
            fputcsv($f,['Tanggal','Kode barang','Nama barang','Batch','Jenis','Perubahan Stok','Satuan','Petugas','Keterangan'], ';');
            $safe=fn($v)=>preg_match('/^[\s]*[=+\-@\t\r]/u',(string)$v)?"'".$v:$v;
            foreach($q->cursor() as $m){
                fputcsv($f,[$m->created_at->format('Y-m-d H:i'),$safe($m->product->sku),$safe($m->product->name),
                    $safe($m->batch->batch_number),$m->type,$m->quantity_change,$safe($m->product->unit),
                    $safe($m->creator?->name??'-'),$safe($m->notes??'')], ';');
            }
            fclose($f);
        },'laporan-stok-'.now()->format('Ymd-His').'.csv',['Content-Type'=>'text/csv; charset=UTF-8']);
    }
}
