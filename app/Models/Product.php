<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'composition',
        'calories',
        'category_id',
        'price'
    ];

    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id')->select(['product_categories.id', 'product_categories.name']);
    }

    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_product', 'product_id', 'order_id')
            ->withPivot('quantity'); 
    }

}
