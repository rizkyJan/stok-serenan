<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Product extends Model {
    protected $fillable=['sku','name','category','unit','minimum_stock','is_active','notes'];
    protected function casts():array{return ['is_active'=>'boolean'];}
    public function batches():HasMany{return $this->hasMany(ProductBatch::class);}
}
