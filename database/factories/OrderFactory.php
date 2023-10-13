<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Address;
use App\Models\DeliveryMethod;
use App\Models\PaymentMethod;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition()
    {
        $user = User::factory()->create();
        $address = Address::factory()->create(['user_id' => $user->id]);
        $payment = PaymentMethod::factory()->create();
        $delivery = DeliveryMethod::factory()->create();
        return [
            'status' => $this->faker->text,
            'total_amount' => 0, 
            'user_id' => $user->id,
            'address_id' => $address->id,
            'payment_method_id' => $payment->id,
            'delivery_method_id' => $delivery->id, 
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Order $order) {
            $products = Product::factory(1)->create();
            $order->products()->attach($products, ['quantity' => 1]);
    
            $totalAmount = 0;
    
            foreach ($order->products as $product) {
                if ($product->pivot && isset($product->pivot->quantity)) {
                    $totalAmount += $product->price * $product->pivot->quantity;
                }
            }

            $order->update(['total_amount' => $totalAmount]);
        });
    }
    
}
