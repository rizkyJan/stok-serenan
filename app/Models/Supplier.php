<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Supplier extends Model {
    protected $fillable = ['name','npwp','phone','contact_name','address','notes','is_active'];
    protected function casts():array { return ['is_active'=>'boolean']; }
    public function invoices():HasMany {return $this->hasMany(PurchaseInvoice::class);}
}
