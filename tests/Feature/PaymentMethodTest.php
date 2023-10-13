<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\PaymentMethod;
use Faker\Factory as Faker;

class PaymentMethodTest extends TestCase
{
    use DatabaseTransactions;
    
    // Админы могут смотреть список способов оплаты
    public function testIndexForAdminUser(): void
    {
        $user = User::factory()->create();
        $user->assignRole("Admin");
        
        $response = $this->actingAs($user)->get("/api/payment-methods");
        $response->assertStatus(200)->assertJsonStructure(['payment-methods' => [
            '*' => [
                'id',
                'name',
                'created_at',
                'updated_at'
            ],],]);
    }

    // Клиенты могут смотреть список способов оплаты
    public function testIndexForClientUser(): void
    {
        $user = User::factory()->create();
        $user->assignRole("Client");
        
        $response = $this->actingAs($user)->get("/api/payment-methods");
        $response->assertStatus(200)->assertJsonStructure(['payment-methods' => [
            '*' => [
                'id',
                'name',
                'created_at',
                'updated_at'
            ],],]);
    }

    // Гости могут смотреть список способов оплаты
    public function testIndexForGuestUser(): void
    {
        $user = User::factory()->create();
        $user->assignRole("Guest");
        
        $response = $this->actingAs($user)->get("/api/payment-methods");
        $response->assertStatus(200)->assertJsonStructure(['payment-methods' => [
            '*' => [
                'id',
                'name',
                'created_at',
                'updated_at'
            ],],]);
    }    

    // Админы могут смотреть конкретный способ оплаты
    public function testShowForAdminUser(): void
    {
        $user = User::factory()->create();
        $user->assignRole("Admin");
        $paymentMethod = PaymentMethod::factory()->create();
        
        $response = $this->actingAs($user)->get("/api/payment-methods/" . $paymentMethod->id);
        $response->assertStatus(200)->assertJsonStructure(['payment-method' => [
            'id',
            'name',
            'created_at',
            'updated_at'
        ]]);
    }

    // Клиенты могут смотреть конкретный способ оплаты
    public function testShowForClientUser(): void
    {
        $user = User::factory()->create();
        $user->assignRole("Client");
        $paymentMethod = PaymentMethod::factory()->create();
        
        $response = $this->actingAs($user)->get("/api/payment-methods/" . $paymentMethod->id);
        $response->assertStatus(200)->assertJsonStructure(['payment-method' => [
            'id',
            'name',
            'created_at',
            'updated_at'
        ]]);
    }  
    
    // Гости могут смотреть конкретный способ оплаты
    public function testShowForGuestUser(): void
    {
        $user = User::factory()->create();
        $user->assignRole("Guest");
        $paymentMethod = PaymentMethod::factory()->create();
        
        $response = $this->actingAs($user)->get("/api/payment-methods/" . $paymentMethod->id);
        $response->assertStatus(200)->assertJsonStructure(['payment-method' => [
            'id',
            'name',
            'created_at',
            'updated_at'
        ]]);
    }     

    // Админы могут создавать новый способ оплаты
    public function testStoreForAdminUser(){
        $faker = Faker::create();
        $user = User::factory()->create();
        $user->assignRole('Admin');

        $paymentMethodData = [
            'name' => $faker->text
        ];

        $response = $this->actingAs($user, 'sanctum')->post('/api/payment-methods', $paymentMethodData);
        $response->assertStatus(201)->assertJsonFragment(["message" => "Способ оплаты был успешно добавлен"]);
    }

    // Клиенты не могут создавать новый способ оплаты
    public function testStoreForClientUser(){
        $faker = Faker::create();
        $user = User::factory()->create();
        $user->assignRole('Client');

        $paymentMethodData = [
            'name' => $faker->text
        ];

        $response = $this->actingAs($user, 'sanctum')->post('/api/payment-methods', $paymentMethodData);
        $response->assertStatus(403)->assertJsonFragment(['message' => 'Отказано в доступе']);
    }

    // Клиенты не могут создавать новый способ оплаты
    public function testStoreForGuestUser(){
        $faker = Faker::create();
        $user = User::factory()->create();
        $user->assignRole('Guest');

        $paymentMethodData = [
            'name' => $faker->text
        ];

        $response = $this->actingAs($user, 'sanctum')->post('/api/payment-methods', $paymentMethodData);
        $response->assertStatus(403)->assertJsonFragment(['message' => 'Отказано в доступе']);
    }

    // Админы могут обновлять способ оплаты
    public function testUpdateForAdminUser(){
        $faker = Faker::create();
        $user = User::factory()->create();
        $user->assignRole("Admin");
        $paymentMethod = PaymentMethod::factory()->create();

        $paymentMethodData = [
            'name' => $faker->text
        ];

        $response = $this->actingAs($user, 'sanctum')->patch('/api/payment-methods/' . $paymentMethod->id . '/edit', $paymentMethodData);
        $response->assertStatus(200)->assertJsonFragment(['message' => 'Способ оплаты был успешно обновлен']);
    }

    // Клиенты не могут обновлять способ оплаты
    public function testUpdateForClientUser(){
        $faker = Faker::create();
        $user = User::factory()->create();
        $user->assignRole("Client");
        $paymentMethod = PaymentMethod::factory()->create();

        $paymentMethodData = [
            'name' => $faker->text
        ];

        $response = $this->actingAs($user, 'sanctum')->patch('/api/payment-methods/' . $paymentMethod->id . '/edit', $paymentMethodData);
        $response->assertStatus(403)->assertJsonFragment(['message' => 'Отказано в доступе']);
    }

    // Гости не могут обновлять способ оплаты
    public function testUpdateForGuestUser(){
        $faker = Faker::create();
        $user = User::factory()->create();
        $user->assignRole("Guest");
        $paymentMethod = PaymentMethod::factory()->create();

        $paymentMethodData = [
            'name' => $faker->text
        ];

        $response = $this->actingAs($user, 'sanctum')->patch('/api/payment-methods/' . $paymentMethod->id . '/edit', $paymentMethodData);
        $response->assertStatus(403)->assertJsonFragment(['message' => 'Отказано в доступе']);
    }

    // Админы могут удалить способ оплаты
    public function testDestroyForAdminUser(){
        $user = User::factory()->create();
        $user->assignRole("Admin");
        $paymentMethod = PaymentMethod::factory()->create();
        $response = $this->actingAs($user, 'sanctum')->delete('/api/payment-methods/' . $paymentMethod->id);
        $response->assertStatus(200)->assertJsonFragment(['message' => 'Способ оплаты был успешно удален']);
    }

    // Клиенты не могут удалить способ оплаты
    public function testDestroyForClientUser(){
        $user = User::factory()->create();
        $user->assignRole("Client");
        $paymentMethod = PaymentMethod::factory()->create();
        $response = $this->actingAs($user, 'sanctum')->delete('/api/payment-methods/' . $paymentMethod->id);
        $response->assertStatus(403)->assertJsonFragment(['message' => 'Отказано в доступе']);
    }
    
    // Гости не могут удалить способ оплаты
    public function testDestroyForGuestUser(){
        $user = User::factory()->create();
        $user->assignRole("Guest");
        $paymentMethod = PaymentMethod::factory()->create();
        $response = $this->actingAs($user, 'sanctum')->delete('/api/payment-methods/' . $paymentMethod->id);
        $response->assertStatus(403)->assertJsonFragment(['message' => 'Отказано в доступе']);
    }    

    // Нельзя создать способ оплаты с невалидными данными
    public function testStoreForAdminUserWithInvalidData(){
        $faker = Faker::create();
        $user = User::factory()->create();
        $user->assignRole("Admin");
        
        $paymentMethodData = [
            'name' => $faker->numberBetween(1,20)
        ];

        $response = $this->actingAs($user, 'sanctum')->post('/api/payment-methods');
        $response->assertStatus(422)->assertJsonStructure(['errors' => ['name']]);
    }

    // Нельзя обновить способ оплаты невалидными данными
    public function testUpdateForAdminUserWithInvalidData(){
        $faker = Faker::create();
        $user = User::factory()->create();
        $user->assignRole("Admin");
        $paymentMethod = PaymentMethod::factory()->create();
        
        $paymentMethodData = [
            'name' => $faker->numberBetween(1,20)
        ];

        $response = $this->actingAs($user, 'sanctum')->patch('/api/payment-methods/' . $paymentMethod->id . '/edit', $paymentMethodData);
        $response->assertStatus(422)->assertJsonStructure(['errors' => ['name']]);
    }    
}
