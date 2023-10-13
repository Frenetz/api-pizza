<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\DeliveryMethod;
use Faker\Factory as Faker;

class DeliveryMethodTest extends TestCase
{
    use DatabaseTransactions;
    
    // Админы могут смотреть список способов доставки
    public function testIndexForAdminUser(): void
    {
        $user = User::factory()->create();
        $user->assignRole("Admin");
        
        $response = $this->actingAs($user)->get("/api/delivery-methods");
        $response->assertStatus(200)->assertJsonStructure(['delivery-methods' => [
            '*' => [
                'id',
                'name',
                'created_at',
                'updated_at'
            ],],]);
    }

    // Клиенты могут смотреть список способов доставки
    public function testIndexForClientUser(): void
    {
        $user = User::factory()->create();
        $user->assignRole("Client");
        
        $response = $this->actingAs($user)->get("/api/delivery-methods");
        $response->assertStatus(200)->assertJsonStructure(['delivery-methods' => [
            '*' => [
                'id',
                'name',
                'created_at',
                'updated_at'
            ],],]);
    }

    // Гости могут смотреть список способов доставки
    public function testIndexForGuestUser(): void
    {
        $user = User::factory()->create();
        $user->assignRole("Guest");
        
        $response = $this->actingAs($user)->get("/api/delivery-methods");
        $response->assertStatus(200)->assertJsonStructure(['delivery-methods' => [
            '*' => [
                'id',
                'name',
                'created_at',
                'updated_at'
            ],],]);
    }    

    // Админы могут смотреть конкретный способ доставки
    public function testShowForAdminUser(): void
    {
        $user = User::factory()->create();
        $user->assignRole("Admin");
        $deliveryMethod = DeliveryMethod::factory()->create();
        
        $response = $this->actingAs($user)->get("/api/delivery-methods/" . $deliveryMethod->id);
        $response->assertStatus(200)->assertJsonStructure(['delivery-method' => [
            'id',
            'name',
            'created_at',
            'updated_at'
        ]]);
    }

    // Клиенты могут смотреть конкретный способ доставки
    public function testShowForClientUser(): void
    {
        $user = User::factory()->create();
        $user->assignRole("Client");
        $deliveryMethod = DeliveryMethod::factory()->create();
        
        $response = $this->actingAs($user)->get("/api/delivery-methods/" . $deliveryMethod->id);
        $response->assertStatus(200)->assertJsonStructure(['delivery-method' => [
            'id',
            'name',
            'created_at',
            'updated_at'
        ]]);
    }  
    
    // Гости могут смотреть конкретный способ доставки
    public function testShowForGuestUser(): void
    {
        $user = User::factory()->create();
        $user->assignRole("Guest");
        $deliveryMethod = DeliveryMethod::factory()->create();
        
        $response = $this->actingAs($user)->get("/api/delivery-methods/" . $deliveryMethod->id);
        $response->assertStatus(200)->assertJsonStructure(['delivery-method' => [
            'id',
            'name',
            'created_at',
            'updated_at'
        ]]);
    }     

    // Админы могут создавать новый способ доставки
    public function testStoreForAdminUser(){
        $faker = Faker::create();
        $user = User::factory()->create();
        $user->assignRole('Admin');

        $deliveryMethodData = [
            'name' => $faker->text
        ];

        $response = $this->actingAs($user, 'sanctum')->post('/api/delivery-methods', $deliveryMethodData);
        $response->assertStatus(201)->assertJsonFragment(["message" => "Способ доставки был успешно создан"]);
    }

    // Клиенты не могут создавать новый способ доставки
    public function testStoreForClientUser(){
        $faker = Faker::create();
        $user = User::factory()->create();
        $user->assignRole('Client');

        $deliveryMethodData = [
            'name' => $faker->text
        ];

        $response = $this->actingAs($user, 'sanctum')->post('/api/delivery-methods', $deliveryMethodData);
        $response->assertStatus(403)->assertJsonFragment(['message' => 'Отказано в доступе']);
    }

    // Клиенты не могут создавать новый способ доставки
    public function testStoreForGuestUser(){
        $faker = Faker::create();
        $user = User::factory()->create();
        $user->assignRole('Guest');

        $deliveryMethodData = [
            'name' => $faker->text
        ];

        $response = $this->actingAs($user, 'sanctum')->post('/api/delivery-methods', $deliveryMethodData);
        $response->assertStatus(403)->assertJsonFragment(['message' => 'Отказано в доступе']);
    }

    // Админы могут обновлять способ доставки
    public function testUpdateForAdminUser(){
        $faker = Faker::create();
        $user = User::factory()->create();
        $user->assignRole("Admin");
        $deliveryMethod = DeliveryMethod::factory()->create();

        $deliveryMethodData = [
            'name' => $faker->text
        ];

        $response = $this->actingAs($user, 'sanctum')->patch('/api/delivery-methods/' . $deliveryMethod->id . '/edit', $deliveryMethodData);
        $response->assertStatus(200)->assertJsonFragment(['message' => 'Способ доставки был успешно обновлен']);
    }

    // Клиенты не могут обновлять способ доставки
    public function testUpdateForClientUser(){
        $faker = Faker::create();
        $user = User::factory()->create();
        $user->assignRole("Client");
        $deliveryMethod = DeliveryMethod::factory()->create();

        $deliveryMethodData = [
            'name' => $faker->text
        ];

        $response = $this->actingAs($user, 'sanctum')->patch('/api/delivery-methods/' . $deliveryMethod->id . '/edit', $deliveryMethodData);
        $response->assertStatus(403)->assertJsonFragment(['message' => 'Отказано в доступе']);
    }

    // Гости не могут обновлять способ доставки
    public function testUpdateForGuestUser(){
        $faker = Faker::create();
        $user = User::factory()->create();
        $user->assignRole("Guest");
        $deliveryMethod = DeliveryMethod::factory()->create();

        $deliveryMethodData = [
            'name' => $faker->text
        ];

        $response = $this->actingAs($user, 'sanctum')->patch('/api/delivery-methods/' . $deliveryMethod->id . '/edit', $deliveryMethodData);
        $response->assertStatus(403)->assertJsonFragment(['message' => 'Отказано в доступе']);
    }

    // Админы могут удалить способ доставки
    public function testDestroyForAdminUser(){
        $user = User::factory()->create();
        $user->assignRole("Admin");
        $deliveryMethod = DeliveryMethod::factory()->create();
        $response = $this->actingAs($user, 'sanctum')->delete('/api/delivery-methods/' . $deliveryMethod->id);
        $response->assertStatus(200)->assertJsonFragment(['message' => 'Способ доставки был успешно удален']);
    }

    // Клиенты не могут удалить способ доставки
    public function testDestroyForClientUser(){
        $user = User::factory()->create();
        $user->assignRole("Client");
        $deliveryMethod = DeliveryMethod::factory()->create();
        $response = $this->actingAs($user, 'sanctum')->delete('/api/delivery-methods/' . $deliveryMethod->id);
        $response->assertStatus(403)->assertJsonFragment(['message' => 'Отказано в доступе']);
    }
    
    // Гости не могут удалить способ доставки
    public function testDestroyForGuestUser(){
        $user = User::factory()->create();
        $user->assignRole("Guest");
        $deliveryMethod = DeliveryMethod::factory()->create();
        $response = $this->actingAs($user, 'sanctum')->delete('/api/delivery-methods/' . $deliveryMethod->id);
        $response->assertStatus(403)->assertJsonFragment(['message' => 'Отказано в доступе']);
    }    

    // Нельзя создать способ доставки с невалидными данными
    public function testStoreForAdminUserWithInvalidData(){
        $faker = Faker::create();
        $user = User::factory()->create();
        $user->assignRole("Admin");
        
        $deliveryMethodData = [
            'name' => $faker->numberBetween(1,20)
        ];

        $response = $this->actingAs($user, 'sanctum')->post('/api/delivery-methods');
        $response->assertStatus(422)->assertJsonStructure(['errors' => ['name']]);
    }

    // Нельзя обновить способ доставки невалидными данными
    public function testUpdateForAdminUserWithInvalidData(){
        $faker = Faker::create();
        $user = User::factory()->create();
        $user->assignRole("Admin");
        $deliveryMethod = DeliveryMethod::factory()->create();
        
        $deliveryMethodData = [
            'name' => $faker->numberBetween(1,20)
        ];

        $response = $this->actingAs($user, 'sanctum')->patch('/api/delivery-methods/' . $deliveryMethod->id . '/edit', $deliveryMethodData);
        $response->assertStatus(422)->assertJsonStructure(['errors' => ['name']]);
    }    
}
