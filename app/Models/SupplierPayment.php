<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class SupplierPayment extends Model {
    protected $fillable=['purchase_invoice_id','paid_at','amount','method','reference','notes','created_by'];
    protected function casts():array{return ['paid_at'=>'date'];}
    public function invoice():BelongsTo{return $this->belongsTo(PurchaseInvoice::class,'purchase_invoice_id');}
}
