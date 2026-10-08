<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class PurchaseInvoice extends Model {
    protected $fillable=['supplier_id','invoice_no','invoice_date','received_date','due_date','subtotal','line_discount_total','net_items_total','discount','dpp','other_dpp','tax_mode','tax_rate','tax','shipping','total','paid_amount','notes','recipient_name','document_total','attachment_path','attachment_filename','attachment_mime','created_by'];
    protected function casts():array{return ['invoice_date'=>'date','received_date'=>'date','due_date'=>'date'];}
    public function supplier():BelongsTo{return $this->belongsTo(Supplier::class);}
    public function items():HasMany{return $this->hasMany(PurchaseItem::class);}
    public function payments():HasMany{return $this->hasMany(SupplierPayment::class);}
    public function creator():BelongsTo{return $this->belongsTo(User::class,'created_by');}
    public function getBalanceAttribute():float{return max(0,round((float)$this->total-(float)$this->paid_amount,2));}
    public function getPaymentStatusAttribute():string {
        return $this->balance <= 0.009 ? 'Lunas' : ((float)$this->paid_amount > 0 ? 'Sebagian' : 'Belum Lunas');
    }
}
