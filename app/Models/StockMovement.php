<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class StockMovement extends Model {
    protected $fillable=['product_id','product_batch_id','type','quantity_change','reference_type','reference_id','notes','created_by'];
    public function product():BelongsTo{return $this->belongsTo(Product::class);}
    public function batch():BelongsTo{return $this->belongsTo(ProductBatch::class,'product_batch_id');}
    public function creator():BelongsTo{return $this->belongsTo(User::class,'created_by');}
}
