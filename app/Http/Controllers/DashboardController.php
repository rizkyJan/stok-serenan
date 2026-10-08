<?php
namespace App\Http\Controllers;
use App\Models\{Product,ProductBatch,PurchaseInvoice,StockMovement,StockOut};
use Illuminate\Support\Carbon;
class DashboardController extends Controller {
    public function __invoke(){
        $today=Carbon::today();
        $products=Product::withSum(['batches as sellable_stock'=>fn($q)=>$q->where(fn($q)=>$q->whereNull('expires_at')->orWhereDate('expires_at','>=',$today))],'quantity_available')->get();
        $lowStock=$products->filter(fn($p)=>$p->is_active && (int)$p->sellable_stock <= $p->minimum_stock);
        $expiring=ProductBatch::with('product')->where('quantity_available','>',0)->whereDate('expires_at','>=',$today)->whereDate('expires_at','<=',$today->copy()->addDays(90))->orderBy('expires_at')->limit(6)->get();
        $overdue=PurchaseInvoice::with('supplier')->whereColumn('paid_amount','<','total')->whereDate('due_date','<',$today)->orderBy('due_date')->get();
        $dueSoon=PurchaseInvoice::with('supplier')->whereColumn('paid_amount','<','total')->whereBetween('due_date',[$today,$today->copy()->addDays(7)])->orderBy('due_date')->limit(6)->get();
        return view('dashboard.index',[
            'productCount'=>Product::count(), 'lowStockCount'=>$lowStock->count(), 'lowStock'=>$lowStock->take(6),
            'expiringCount'=>ProductBatch::where('quantity_available','>',0)->whereBetween('expires_at',[$today,$today->copy()->addDays(90)])->count(),
            'expiring'=>$expiring,'overdueCount'=>$overdue->count(),'overdueBalance'=>$overdue->sum(fn($p)=>$p->balance),
            'dueSoon'=>$dueSoon,
            'incomingThisMonth'=>\App\Models\PurchaseInvoice::whereYear('received_date',$today->year)->whereMonth('received_date',$today->month)->count(),
            'outgoingThisMonth'=>StockOut::whereYear('created_at',$today->year)->whereMonth('created_at',$today->month)->sum('quantity'),
            'recentMovements'=>StockMovement::with(['product','creator'])->latest()->take(8)->get(),
        ]);
    }
}
