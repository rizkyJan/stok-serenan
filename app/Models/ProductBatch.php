<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class ProductBatch extends Model {
    protected $fillable=['product_id','batch_number','expires_at','quantity_received','quantity_available','unit_cost'];
    protected function casts():array{return ['expires_at'=>'date','unit_cost'=>'decimal:4'];}
    public function product():BelongsTo{return $this->belongsTo(Product::class);}
}
