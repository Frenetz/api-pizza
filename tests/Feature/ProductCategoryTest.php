<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\ProductCategory;
use Faker\Factory as Faker;

class ProductCategoryTest extends TestCase
{
    use DatabaseTransactions;

    // Админы могут просматривать категории товаров
    public function testIndexForAdminUser(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Admin');
        $response = $this->actingAs($user, 'sanctum')->get('/api/product-categories');
        $response->assertStatus(200)->assertJsonStructure([
            'product-categories' => [
                '*' => [
                    'id',
                    'name',
                    'created_at',
                    'updated_at',
                ],
            ],
        ]);
    }

    // Клиенты могут просматривать категории товаров
    public function testIndexForClientUser(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Client');
        $response = $this->actingAs($user, 'sanctum')->get('/api/product-categories');
        $response->assertStatus(200)->assertJsonStructure([
            'product-categories' => [
                '*' => [
                    'id',
                    'name',
                    'created_at',
                    'updated_at',
                ],
            ],
        ]);
    }

    // Гости могут просматривать категории товаров
    public function testIndexForGuestUser(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Guest');
        $response = $this->actingAs($user, 'sanctum')->get('/api/product-categories');
        $response->assertStatus(200)->assertJsonStructure([
            'product-categories' => [
                '*' => [
                    'id',
                    'name',
                    'created_at',
                    'updated_at',
                ],
            ],
        ]);
    }

    // Админы могут просматривать конкретную категорию товаров
    public function testShowForAdminUser(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Admin');
        $productCategory = ProductCategory::factory()->create();
        $response = $this->actingAs($user, 'sanctum')->get('/api/product-categories/' . $productCategory->id);
        $response->assertStatus(200)
        ->assertJsonStructure([
            'product-category' => [
                'id',
                'name',
                'created_at',
                'updated_at',
            ],
        ]);
    }

    // Клиенты могут просматривать конкретную категорию товаров
    public function testShowForClientUser(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Client');
        $productCategory = ProductCategory::factory()->create();
        $response = $this->actingAs($user, 'sanctum')->get('/api/product-categories/' . $productCategory->id);
        $response->assertStatus(200)
        ->assertJsonStructure([
            'product-category' => [
                'id',
                'name',
                'created_at',
                'updated_at',
            ],
        ]);
    }

    // Гости могут просматривать конкретную категорию товаров
    public function testShowForGuestUser(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Guest');
        $productCategory = ProductCategory::factory()->create();
        $response = $this->actingAs($user, 'sanctum')->get('/api/product-categories/' . $productCategory->id);
        $response->assertStatus(200)
        ->assertJsonStructure([
            'product-category' => [
                'id',
                'name',
                'created_at',
                'updated_at',
            ],
        ]);
    }

    // Админы могут добавлять категорию товаров
    public function testStoreForAdminUser(){
        $faker = Faker::create();
        $user = User::factory()->create();
        $user->assignRole('Admin');
        $productCategoryData = [
            'name' => $faker->text
        ];
        $response = $this->actingAs($user, 'sanctum')->post('/api/product-categories', $productCategoryData);
        $response->assertStatus(201)->assertJsonFragment([
            "message" => "Категория товаров была успешно добавлена"
        ]);
    }

    // Клиенты не могут добавлять категорию товаров
    public function testStoreForClientUser(){
        $faker = Faker::create();
        $user = User::factory()->create();
        $user->assignRole('Client');
        $productCategoryData = [
            'name' => $faker->text
        ];
        $response = $this->actingAs($user, 'sanctum')->post('/api/product-categories', $productCategoryData);
        $response->assertStatus(403)->assertJsonFragment([
            "message" => "Отказано в доступе"
        ]);
    }

    // Гости не могут добавлять категорию товаров
    public function testStoreForGuestUser(){
        $faker = Faker::create();
        $user = User::factory()->create();
        $user->assignRole('Guest');
        $productCategoryData = [
            'name' => $faker->text
        ];
        $response = $this->actingAs($user, 'sanctum')->post('/api/product-categories', $productCategoryData);
        $response->assertStatus(403)->assertJsonFragment([
            "message" => "Отказано в доступе"
        ]);
    }

    // Админы могут обновлять категорию товаров
    public function testEditForAdminUser(){
        $faker = Faker::create();
        $user = User::factory()->create();
        $user->assignRole('Admin');
        $productCategory = ProductCategory::factory()->create();

        $productCategoryData = [
            'name' => $faker->text
        ];
        $response = $this->actingAs($user, 'sanctum')->patch('/api/product-categories/' . $productCategory->id . '/edit' , $productCategoryData);
        $response->assertStatus(200)->assertJsonFragment([
            "message" => "Категория товаров была успешно обновлена"
        ]);
    }

    // Клиенты не могут обновлять категорию товаров
    public function testEditForClientUser(){
        $faker = Faker::create();
        $user = User::factory()->create();
        $user->assignRole('Client');
        $productCategory = ProductCategory::factory()->create();

        $productCategoryData = [
            'name' => $faker->text
        ];
        $response = $this->actingAs($user, 'sanctum')->patch('/api/product-categories/' . $productCategory->id . '/edit' , $productCategoryData);
        $response->assertStatus(403)->assertJsonFragment([
            "message" => "Отказано в доступе"
        ]);
    }

    // Гости не могут обновлять категорию товаров
    public function testEditForGuestUser(){
        $faker = Faker::create();
        $user = User::factory()->create();
        $user->assignRole('Guest');
        $productCategory = ProductCategory::factory()->create();

        $productCategoryData = [
            'name' => $faker->text
        ];
        $response = $this->actingAs($user, 'sanctum')->patch('/api/product-categories/' . $productCategory->id . '/edit' , $productCategoryData);
        $response->assertStatus(403)->assertJsonFragment([
            "message" => "Отказано в доступе"
        ]);
    }

    // Админы могут удалять категорию товаров
    public function testDestroyForAdminUser(){
        $user = User::factory()->create();
        $user->assignRole('Admin');
        $productCategory = ProductCategory::factory()->create();
        $response = $this->actingAs($user, 'sanctum')->delete('/api/product-categories/' . $productCategory->id);
        $response->assertStatus(200)->assertJsonFragment(["message" => "Категория товаров была успешно удалена"]);
        $this->assertDatabaseMissing('product_categories', ['id' => $productCategory->id]);
    }

    // Клиенты не могут удалять категорию товаров
    public function testDestroyForClientUser(){
        $user = User::factory()->create();
        $user->assignRole('Client');
        $productCategory = ProductCategory::factory()->create();
        $response = $this->actingAs($user, 'sanctum')->delete('/api/product-categories/' . $productCategory->id);
        $response->assertStatus(403)->assertJsonFragment(["message" => "Отказано в доступе"]);
        $this->assertDatabaseHas('product_categories', ['id' => $productCategory->id]);
    }

    // Гости не могут удалять категорию товаров
    public function testDestroyForGuestUser(){
        $user = User::factory()->create();
        $user->assignRole('Guest');
        $productCategory = ProductCategory::factory()->create();
        $response = $this->actingAs($user, 'sanctum')->delete('/api/product-categories/' . $productCategory->id);
        $response->assertStatus(403)->assertJsonFragment(["message" => "Отказано в доступе"]);
        $this->assertDatabaseHas('product_categories', ['id' => $productCategory->id]);
    }

    // Админы не могут создавать категорию товаров с невалидными данными
    public function testStoreForAdminUserWithInvalidData(){
        $faker = Faker::create();
        $user = User::factory()->create();
        $user->assignRole('Admin');
        $productCategoryData = [
            "name" => $faker->numberBetween(1,20)
        ];
        $response = $this->actingAs($user, 'sanctum')->post('/api/product-categories', $productCategoryData);
        $response->assertStatus(422)->assertJsonStructure([
            'errors' => [
                'name' 
            ]
        ]);
    }

    // Админы не могут обновлять запись из категории товаров невалидными данными
    public function testUpdateForAdminUserWithInvalidData(){
        $faker = Faker::create();
        $user = User::factory()->create();
        $user->assignRole('Admin');
        $productCategory = ProductCategory::factory()->create();
        $productCategoryData = [
            "name" => $faker->numberBetween(1,20)
        ];
        $response = $this->actingAs($user, 'sanctum')->patch('/api/product-categories/' . $productCategory->id . '/edit'  , $productCategoryData);
        $response->assertStatus(422)->assertJsonStructure([
            'errors' => [
                'name' 
            ]
        ]);
    }
}
