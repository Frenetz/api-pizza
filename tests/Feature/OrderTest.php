<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Address;
use App\Models\DeliveryMethod;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Order;
use Faker\Factory as Faker;

class OrderTest extends TestCase
{
    use DatabaseTransactions;

    // Админы могут получить список всех заказом
    public function testIndexForAdminUser(): void
    {
        $user = User::factory()->create();
        $user->assignRole("Admin");
        $order = Order::factory()->create();
        $response = $this->actingAs($user, 'sanctum')->get('/api/orders');
        $response->assertStatus(200)->assertJsonStructure([
            'orders' => [
                '*' => [
                    'id',
                    'status',
                    'total_amount',
                    'created_at',
                    'updated_at',
                    'user_id',
                    'user',
                    'address',
                    'products',
                    'payment_method',
                    'delivery_method'
                ] 
            ]
        ]);
    }

    // Клиенты могут получить список всех своих заказов
    public function testIndexForClientUser(): void
    {
        $user = User::factory()->create();
        $user->assignRole("Client");
        $order = Order::factory()->create(['user_id' => $user->id]);
        $response = $this->actingAs($user, 'sanctum')->get('/api/orders');
        $response->assertStatus(200)->assertJsonStructure([
            'orders' => [
                '*' => [
                    'id',
                    'status',
                    'total_amount',
                    'created_at',
                    'updated_at',
                    'user_id',
                    'user',
                    'address',
                    'products',
                    'payment_method',
                    'delivery_method'
                ] 
            ]
        ]);
        $response->assertJsonFragment(['user_id' => $user->id]);
    }   
    
    // Гости не могут получить список заказов
    public function testIndexForGuestUser(){
        $user = User::factory()->create();
        $user->assignRole("Guest");
        $response = $this->actingAs($user, 'sanctum')->get('/api/orders');
        $response->assertStatus(403)->assertJsonFragment(["message" => "Отказано в доступе"]);
    }

    // Админы могут смотреть конкретный заказ (свой)
    public function testShowForAdminUserWithOwnOrder(){
        $user = User::factory()->create();
        $user->assignRole('Admin');
        $order = Order::factory()->create(['user_id' => $user->id]);
        $response = $this->actingAs($user, 'sanctum')->get('/api/orders/' . $order->id);
        $response->assertStatus(200)->assertJsonStructure(["order" => [
            'id',
            'status',
            'total_amount',
            'created_at',
            'updated_at',
            'user_id',
            'user',
            'address',
            'products',
            'payment_method',
            'delivery_method'
        ]]);
        $response->assertJsonFragment(['user_id' => $user->id,'id' => $order->id]);
    }

    // Админы могут смотреть конкретный заказ (чужой)
    public function testShowForAdminUserWithStrangeOrder(){
        $user1 = User::factory()->create();
        $user1->assignRole('Client');
        $user2 = User::factory()->create();
        $user2->assignRole('Admin');
        $order = Order::factory()->create(['user_id' => $user1->id]);
        $response = $this->actingAs($user2, 'sanctum')->get('/api/orders/' . $order->id);
        $response->assertStatus(200)->assertJsonStructure(["order" => [
            'id',
            'status',
            'total_amount',
            'created_at',
            'updated_at',
            'user_id',
            'user',
            'address',
            'products',
            'payment_method',
            'delivery_method'
        ]]);
        $response->assertJsonFragment(['user_id' => $user1->id,'id' => $order->id]);
    }    

    // Клиенты могут смотреть конкретный заказ (свой)
    public function testShowForClientUserWithOwnOrder(){
        $user = User::factory()->create();
        $user->assignRole('Client');
        $order = Order::factory()->create(['user_id' => $user->id]);
        $response = $this->actingAs($user, 'sanctum')->get('/api/orders/' . $order->id);
        $response->assertStatus(200)->assertJsonStructure(["order" => [
            'id',
            'status',
            'total_amount',
            'created_at',
            'updated_at',
            'user_id',
            'user',
            'address',
            'products',
            'payment_method',
            'delivery_method'
        ]]);
        $response->assertJsonFragment(['user_id' => $user->id,'id' => $order->id]);
    }    

    // Клиенты не могут смотреть конкретный заказ (чужой)
    public function testShowForClientUserWithStrangeOrder(){
        $user1 = User::factory()->create();
        $user1->assignRole('Client');
        $user2 = User::factory()->create();
        $user2->assignRole('Client');
        $order = Order::factory()->create(['user_id' => $user1->id]);
        $response = $this->actingAs($user2, 'sanctum')->get('/api/orders/' . $order->id);
        $response->assertStatus(403)->assertJsonFragment(["message" => "Отказано в доступе"]);
    }      

    // Гости не могут смотреть конкретный адрес
    public function testShowForGuestUser(){
        $user1 = User::factory()->create();
        $user1->assignRole('Client');
        $user2 = User::factory()->create();
        $user2->assignRole('Guest');
        $order = Order::factory()->create(['user_id' => $user1->id]);
        $response = $this->actingAs($user2, 'sanctum')->get('/api/orders/' . $order->id);
        $response->assertStatus(403)->assertJsonFragment(["message" => "Отказано в доступе"]);
    }

    // Админы могут создавать новый заказ
    public function testStoreForAdminUser(){
        $faker = Faker::create();
        $user = User::factory()->create();
        $user->assignRole('Admin');
        $address = Address::factory()->create(['user_id' => $user->id]);
        $product = Product::factory()->create();
        $payment = PaymentMethod::factory()->create();
        $delivery = DeliveryMethod::factory()->create();
        $count = $faker->numberBetween(1,10);
        
        $orderData = [
            'status' => $faker->text,
            'total_amount' => $product->price * $count,
            'user_id' => $user->id,
            'address_id' => $address->id,
            'products' => [
                [
                'product_id' => $product->id,
                'quantity' => $count
                ]
            ],
            'payment_method_id' => $payment->id,
            'delivery_method_id' => $delivery->id
        ];

        $response = $this->actingAs($user, 'sanctum')->post('/api/orders', $orderData);
        $response->assertStatus(201)->assertJsonFragment(['message' => 'Заказ успешно создан']);
    }

    // Клиенты могут создавать новый заказ
    public function testStoreForClientUser(){
        $faker = Faker::create();
        $user = User::factory()->create();
        $user->assignRole('Client');
        $address = Address::factory()->create(['user_id' => $user->id]);
        $product = Product::factory()->create();
        $payment = PaymentMethod::factory()->create();
        $delivery = DeliveryMethod::factory()->create();
        $count = $faker->numberBetween(1,10);
        
        $orderData = [
            'status' => $faker->text,
            'total_amount' => $product->price * $count,
            'user_id' => $user->id,
            'address_id' => $address->id,
            'products' => [
                [
                'product_id' => $product->id,
                'quantity' => $count
                ]
            ],
            'payment_method_id' => $payment->id,
            'delivery_method_id' => $delivery->id
        ];

        $response = $this->actingAs($user, 'sanctum')->post('/api/orders', $orderData);
        $response->assertStatus(201)->assertJsonFragment(['message' => 'Заказ успешно создан']);
    }  
    
    // Гости не могут создавать новый заказ
    public function testStoreForGuestUser(){
        $faker = Faker::create();
        $user = User::factory()->create();
        $user->assignRole('Guest');
        $address = Address::factory()->create(['user_id' => $user->id]);
        $product = Product::factory()->create();
        $payment = PaymentMethod::factory()->create();
        $delivery = DeliveryMethod::factory()->create();
        $count = $faker->numberBetween(1,10);
        
        $orderData = [
            'status' => $faker->text,
            'total_amount' => $product->price * $count,
            'user_id' => $user->id,
            'address_id' => $address->id,
            'products' => [
                [
                'product_id' => $product->id,
                'quantity' => $count
                ]
            ],
            'payment_method_id' => $payment->id,
            'delivery_method_id' => $delivery->id
        ];

        $response = $this->actingAs($user, 'sanctum')->post('/api/orders', $orderData);
        $response->assertStatus(403)->assertJsonFragment(['message' => 'Отказано в доступе']);
    }    
    
    // Админы могут обновлять конкретный адрес (свой)
    public function testUpdateForAdminUserWithOwnOrder(){
        $faker = Faker::create();
        $user = User::factory()->create();
        $user->assignRole('Admin');
        $count = $faker->numberBetween(1,10);
        $address = Address::factory()->create(['user_id' => $user->id]);
        $product = Product::factory()->create();
        $payment = PaymentMethod::factory()->create();
        $delivery = DeliveryMethod::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $orderData = [
            'status' => $faker->text,
            'total_amount' => $product->price * $count,
            'user_id' => $user->id,
            'address_id' => $address->id,
            'products' => [
                [
                'product_id' => $product->id,
                'quantity' => $count
                ]
            ],
            'payment_method_id' => $payment->id,
            'delivery_method_id' => $delivery->id
        ];

        $response = $this->actingAs($user, 'sanctum')->patch('/api/orders/' . $order->id . '/edit' , $orderData);
        $response->assertStatus(200)->assertJsonFragment(['message' => 'Адрес успешно обновлен']);
    }

    // Админы могут обновить конкретный адрес (чужой)
    public function testUpdateForAdminUserWithStrangeOrder(){
        $faker = Faker::create();
        $user1 = User::factory()->create();
        $user1->assignRole('Admin');
        $user2 = User::factory()->create();
        $user2->assignRole('Client');
        $count = $faker->numberBetween(1,10);
        $address = Address::factory()->create(['user_id' => $user1->id]);
        $product = Product::factory()->create();
        $payment = PaymentMethod::factory()->create();
        $delivery = DeliveryMethod::factory()->create();
        $order = Order::factory()->create(['user_id' => $user2->id]);

        $orderData = [
            'status' => $faker->text,
            'total_amount' => $product->price * $count,
            'user_id' => $user1->id,
            'address_id' => $address->id,
            'products' => [
                [
                'product_id' => $product->id,
                'quantity' => $count
                ]
            ],
            'payment_method_id' => $payment->id,
            'delivery_method_id' => $delivery->id
        ];

        $response = $this->actingAs($user1, 'sanctum')->patch('/api/orders/' . $order->id . '/edit' , $orderData);
        $response->assertStatus(200)->assertJsonFragment(['message' => 'Адрес успешно обновлен']);
    }

    // Клиенты могут обновлять конкретный адрес (свой)
    public function testUpdateForClientUserWithOwnOrder(){
        $faker = Faker::create();
        $user = User::factory()->create();
        $user->assignRole('Client');
        $count = $faker->numberBetween(1,10);
        $address = Address::factory()->create(['user_id' => $user->id]);
        $product = Product::factory()->create();
        $payment = PaymentMethod::factory()->create();
        $delivery = DeliveryMethod::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $orderData = [
            'status' => $faker->text,
            'total_amount' => $product->price * $count,
            'user_id' => $user->id,
            'address_id' => $address->id,
            'products' => [
                [
                'product_id' => $product->id,
                'quantity' => $count
                ]
            ],
            'payment_method_id' => $payment->id,
            'delivery_method_id' => $delivery->id
        ];

        $response = $this->actingAs($user, 'sanctum')->patch('/api/orders/' . $order->id . '/edit' , $orderData);
        $response->assertStatus(200)->assertJsonFragment(['message' => 'Адрес успешно обновлен']);        
    }

    // Клиенты не могут обновлять конкретный адрес (чужой)
    public function testUpdateForClientUserWithStrangeOrder(){
        $faker = Faker::create();
        $user1 = User::factory()->create();
        $user1->assignRole('Client');
        $user2 = User::factory()->create();
        $user2->assignRole('Client');
        $count = $faker->numberBetween(1,10);
        $address = Address::factory()->create(['user_id' => $user1->id]);
        $product = Product::factory()->create();
        $payment = PaymentMethod::factory()->create();
        $delivery = DeliveryMethod::factory()->create();
        $order = Order::factory()->create(["user_id" => $user2->id]);

        $orderData = [
            'status' => $faker->text,
            'total_amount' => $product->price * $count,
            'user_id' => $user1->id,
            'address_id' => $address->id,
            'products' => [
                [
                'product_id' => $product->id,
                'quantity' => $count
                ]
            ],
            'payment_method_id' => $payment->id,
            'delivery_method_id' => $delivery->id
        ];

        $response = $this->actingAs($user1, 'sanctum')->patch('/api/orders/' . $order->id . '/edit' , $orderData);
        $response->assertStatus(403)->assertJsonFragment(['message' => 'Отказано в доступе']);        
    }

    public function testUpdateForGuestUser(){
        $faker = Faker::create();
        $user1 = User::factory()->create();
        $user1->assignRole('Guest');
        $user2 = User::factory()->create();
        $user2->assignRole('Client');
        $count = $faker->numberBetween(1,10);
        $address = Address::factory()->create(['user_id' => $user1->id]);
        $product = Product::factory()->create();
        $payment = PaymentMethod::factory()->create();
        $delivery = DeliveryMethod::factory()->create();
        $order = Order::factory()->create(["user_id" => $user2->id]);

        $orderData = [
            'status' => $faker->text,
            'total_amount' => $product->price * $count,
            'user_id' => $user1->id,
            'address_id' => $address->id,
            'products' => [
                [
                'product_id' => $product->id,
                'quantity' => $count
                ]
            ],
            'payment_method_id' => $payment->id,
            'delivery_method_id' => $delivery->id
        ];

        $response = $this->actingAs($user1, 'sanctum')->patch('/api/orders/' . $order->id . '/edit' , $orderData);
        $response->assertStatus(403)->assertJsonFragment(['message' => 'Отказано в доступе']);     
    }

    // Админы могут удалять конкретный адрес (свой)
    public function testDestroyForAdminUserWithOwnOrder(){
        $faker = Faker::create();
        $user = User::factory()->create();
        $user->assignRole('Admin');
        $order = Order::factory()->create(['user_id' => $user->id]);
        $response = $this->actingAs($user, 'sanctum')->delete('/api/orders/' . $order->id);
        $response->assertStatus(200)->assertJsonFragment(['message' => 'Заказ был успешно удален']);
    }

    // Админы могут удалять конкретный адрес (чужой)
    public function testDestroyForAdminUserWithStrangeOrder(){
        $faker = Faker::create();
        $user1 = User::factory()->create();
        $user1->assignRole('Admin');
        $user2 = User::factory()->create();
        $user2->assignRole('Client');
        $order = Order::factory()->create(['user_id' => $user2->id]);
        $response = $this->actingAs($user1, 'sanctum')->delete('/api/orders/' . $order->id);
        $response->assertStatus(200)->assertJsonFragment(['message' => 'Заказ был успешно удален']);
    }  
    
    // Клиенты могут удалять конкретный адрес (свой)
    public function testDestroyForClientUserWithOwnOrder(){
        $faker = Faker::create();
        $user = User::factory()->create();
        $user->assignRole('Client');
        $order = Order::factory()->create(['user_id' => $user->id]);
        $response = $this->actingAs($user, 'sanctum')->delete('/api/orders/' . $order->id);
        $response->assertStatus(200)->assertJsonFragment(['message' => 'Заказ был успешно удален']);
    }   
    
    // Клиенты не могут удалять конкретный адрес (чужой)
    public function testDestroyForClientUserWithStrangeOrder(){
        $faker = Faker::create();
        $user1 = User::factory()->create();
        $user1->assignRole('Client');
        $user2 = User::factory()->create();
        $user2->assignRole('Client');
        $order = Order::factory()->create(['user_id' => $user2->id]);
        $response = $this->actingAs($user1, 'sanctum')->delete('/api/orders/' . $order->id);
        $response->assertStatus(403)->assertJsonFragment(['message' => 'Отказано в доступе']);
    }     
    
    // Гости не могут удалять конкретный адрес
    public function testDestroyForGuestUser(){
        $faker = Faker::create();
        $user1 = User::factory()->create();
        $user1->assignRole('Guest');
        $user2 = User::factory()->create();
        $user2->assignRole('Client');
        $order = Order::factory()->create(['user_id' => $user2->id]);
        $response = $this->actingAs($user1, 'sanctum')->delete('/api/orders/' . $order->id);
        $response->assertStatus(403)->assertJsonFragment(['message' => 'Отказано в доступе']);
    }  
    
    // Нельзя создать заказ с невалидными данными
    public function testCreateOrderWithInvalidData(){
        $faker = Faker::create();
        $user = User::factory()->create();
        $user->assignRole('Admin');
        $orderData = [
            'status' => $faker->numberBetween(1,20),
            'total_amount' => $faker->text,
            'user_id' => $faker->text,
            'address_id' => $faker->text,
            'products' => [
                [
                'product_id' => $faker->text,
                'quantity' => $faker->text
                ]
            ],
            'payment_method_id' => $faker->text,
            'delivery_method_id' => $faker->text
        ];

        $response = $this->actingAs($user, 'sanctum')->post('/api/orders', $orderData);
        $response->assertStatus(422)->assertJsonStructure(['errors' => ['delivery_method_id', 'payment_method_id', 'address_id', 'status']]);
    }
}
