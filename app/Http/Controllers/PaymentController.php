<?php
namespace App\Http\Controllers;
use App\Models\{PurchaseInvoice,SupplierPayment};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
class PaymentController extends Controller {
    public function index(Request $request){
        $q=PurchaseInvoice::with('supplier');
        if($status=$request->query('status')){
            if($status==='unpaid')$q->whereColumn('paid_amount','<','total');
            if($status==='paid')$q->whereColumn('paid_amount','>=','total');
            if($status==='overdue')$q->whereColumn('paid_amount','<','total')->whereDate('due_date','<',today());
        }
        return view('payments.index',['invoices'=>$q->orderBy('due_date')->paginate(15)->withQueryString()]);
    }
    public function store(Request $request, PurchaseInvoice $invoice){
        $data=$request->validate([
            'paid_at'=>'required|date','amount'=>'required|numeric|gt:0|max:999999999999',
            'method'=>['required',Rule::in(['transfer','tunai','lainnya'])],
            'reference'=>'nullable|string|max:255','notes'=>'nullable|string|max:3000',
        ]);
        DB::transaction(function()use($invoice,$data){
            $locked=PurchaseInvoice::whereKey($invoice->id)->lockForUpdate()->firstOrFail();
            $amount=round((float)$data['amount'],2);
            if($amount<0.01 || $amount > round($locked->balance,2)){
                throw ValidationException::withMessages(['amount'=>'Nominal pembayaran harus lebih dari nol dan tidak melebihi sisa tagihan.']);
            }
            SupplierPayment::create($data+['purchase_invoice_id'=>$locked->id,'created_by'=>auth()->id(),'amount'=>$amount]);
             $newPaid = round((float)$locked->paid_amount+$amount,2);
            $fields = ['paid_amount'=>$newPaid];
            if ($newPaid >= (float)$locked->total-0.009) $fields['settlement_date'] = $data['paid_at'];
            $locked->update($fields);
        },3);
        return redirect()->route('purchases.show',$invoice)->with('success','Pembayaran berhasil dicatat.');
    }
}
