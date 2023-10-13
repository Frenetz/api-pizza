<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        "user_id",
        "delivery_method_id",
        "payment_method_id",
        "address_id",
        "status",
        "total_amount"
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function deliveryMethod(){
        return $this->belongsTo(DeliveryMethod::class);
    }

    public function paymentMethod(){
        return $this->belongsTo(PaymentMethod::class);
    }

    public function address(){
        return $this->belongsTo(Address::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'order_product', 'order_id', 'product_id')
            ->withPivot('quantity')->select(['products.id', 'products.name', 'products.price']); 
    }
}
