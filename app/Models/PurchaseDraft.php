<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PurchaseDraft extends Model {
    protected $fillable=['created_by','invoice_no','payload'];
    protected function casts():array{return ['payload'=>'array'];}
}
