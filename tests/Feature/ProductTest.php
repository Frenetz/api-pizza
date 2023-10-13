<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\ProductCategory;
use Faker\Factory as Faker;

class ProductTest extends TestCase
{
    use DatabaseTransactions;

    // Админы могут просматривать товары
    public function testIndexForAdminUser(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Admin');
        $response = $this->actingAs($user, 'sanctum')->get('/api/products');
        $response->assertStatus(200)->assertJsonStructure([
            'products' => [
                '*' => [
                    'id',
                    'name',
                    'composition',
                    'calories',
                    'price',
                    'category_id',
                    'created_at',
                    'updated_at',
                    'category'
                ],
            ],
        ]);
    }

    // Клиенты могут просматривать товары
    public function testIndexForClientUser(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Client');
        $response = $this->actingAs($user, 'sanctum')->get('/api/products');
        $response->assertStatus(200)->assertJsonStructure([
            'products' => [
                '*' => [
                    'id',
                    'name',
                    'composition',
                    'calories',
                    'price',
                    'category_id',
                    'created_at',
                    'updated_at',
                    'category'
                ],
            ],
        ]);
    }    

    // Гости могут просматривать товары
    public function testIndexForGuestUser(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Guest');
        $response = $this->actingAs($user, 'sanctum')->get('/api/products');
        $response->assertStatus(200)->assertJsonStructure([
            'products' => [
                '*' => [
                    'id',
                    'name',
                    'composition',
                    'calories',
                    'price',
                    'category_id',
                    'created_at',
                    'updated_at',
                    'category'
                ],
            ],
        ]);
    } 
    
    // Админы могут смотреть конкретный товар
    public function testShowForAdminUser(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Admin');
        $product = Product::factory()->create();
        $response = $this->actingAs($user, 'sanctum')->get('/api/products/' . $product->id);
        $response->assertStatus(200)->assertJsonStructure([
            'product' => [
                'id',
                'name',
                'composition',
                'calories',
                'price',
                'category_id',
                'created_at',
                'updated_at',
                'category'
            ]
        ]);
    } 

    // Клиенты могут смотреть конкретный товар
    public function testShowForClientUser(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Client');
        $product = Product::factory()->create();
        $response = $this->actingAs($user, 'sanctum')->get('/api/products/' . $product->id);
        $response->assertStatus(200)->assertJsonStructure([
            'product' => [
                'id',
                'name',
                'composition',
                'calories',
                'price',
                'category_id',
                'created_at',
                'updated_at',
                'category'
            ]
        ]);
    } 

    // Гости могут смотреть конкретный товар
    public function testShowForGuestUser(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Guest');
        $product = Product::factory()->create();
        $response = $this->actingAs($user, 'sanctum')->get('/api/products/' . $product->id);
        $response->assertStatus(200)->assertJsonStructure([
            'product' => [
                'id',
                'name',
                'composition',
                'calories',
                'price',
                'category_id',
                'created_at',
                'updated_at',
                'category'
            ]
        ]);
    } 

    // Админы могут создать новый товар
    public function testStoreForAdminUser(){
        $faker = Faker::create();
        $user = User::factory()->create();
        $user->assignRole("Admin");
        $productCategory = ProductCategory::factory()->create();

        $productData = [
            "name" => $faker->text,
            "composition" => $faker->text,
            "calories" => $faker->numberBetween(100,1000),
            "category_id" => $productCategory->id,
            "price" => $faker->numberBetween(100, 1000)
        ];

        $response = $this->actingAs($user, 'sanctum')->post('/api/products', $productData);
        $response->assertStatus(201)->assertJsonFragment(["message" => "Продукт был успешно создан"]);
    }

    // Клиенты не могут создать новый товар
    public function testStoreForClientUser(){
        $faker = Faker::create();
        $user = User::factory()->create();
        $user->assignRole("Client");
        $productCategory = ProductCategory::factory()->create();

        $productData = [
            "name" => $faker->text,
            "composition" => $faker->text,
            "calories" => $faker->numberBetween(100,1000),
            "category_id" => $productCategory->id,
            "price" => $faker->numberBetween(100, 1000)
        ];

        $response = $this->actingAs($user, 'sanctum')->post('/api/products', $productData);
        $response->assertStatus(403)->assertJsonFragment(["message" => "Отказано в доступе"]);
    }

    // Гости не могут создать новый товар
    public function testStoreForGuestUser(){
        $faker = Faker::create();
        $user = User::factory()->create();
        $user->assignRole("Guest");
        $productCategory = ProductCategory::factory()->create();

        $productData = [
            "name" => $faker->text,
            "composition" => $faker->text,
            "calories" => $faker->numberBetween(100,1000),
            "category_id" => $productCategory->id,
            "price" => $faker->numberBetween(100, 1000)
        ];

        $response = $this->actingAs($user, 'sanctum')->post('/api/products', $productData);
        $response->assertStatus(403)->assertJsonFragment(["message" => "Отказано в доступе"]);
    }

    // Админы могут обновлять товар
    public function testUpdateForAdminUser(){
        $faker = Faker::create();
        $user = User::factory()->create();
        $user->assignRole("Admin");
        $product = Product::factory()->create();
        $productCategory = ProductCategory::factory()->create();

        $productData = [
            "name" => $faker->text,
            "composition" => $faker->text,
            "calories" => $faker->numberBetween(100,1000),
            "category_id" => $productCategory->id,
            "price" => $faker->numberBetween(100, 1000)
        ];

        $response = $this->actingAs($user, 'sanctum')->patch('/api/products/' . $product->id . '/edit', $productData);
        $response->assertStatus(200)->assertJsonFragment(["message" => "Продукт был успешно обновлен"]);
    }

    // Клиенты не могут обновлять товар
    public function testUpdateForClientUser(){
        $faker = Faker::create();
        $user = User::factory()->create();
        $user->assignRole("Client");
        $product = Product::factory()->create();
        $productCategory = ProductCategory::factory()->create();

        $productData = [
            "name" => $faker->text,
            "composition" => $faker->text,
            "calories" => $faker->numberBetween(100,1000),
            "category_id" => $productCategory->id,
            "price" => $faker->numberBetween(100, 1000)
        ];

        $response = $this->actingAs($user, 'sanctum')->patch('/api/products/' . $product->id . '/edit', $productData);
        $response->assertStatus(403)->assertJsonFragment(["message" => "Отказано в доступе"]);
    }

    // Клиенты не могут обновлять товар
    public function testUpdateForGuestUser(){
        $faker = Faker::create();
        $user = User::factory()->create();
        $user->assignRole("Guest");
        $product = Product::factory()->create();
        $productCategory = ProductCategory::factory()->create();

        $productData = [
            "name" => $faker->text,
            "composition" => $faker->text,
            "calories" => $faker->numberBetween(100,1000),
            "category_id" => $productCategory->id,
            "price" => $faker->numberBetween(100, 1000)
        ];

        $response = $this->actingAs($user, 'sanctum')->patch('/api/products/' . $product->id . '/edit', $productData);
        $response->assertStatus(403)->assertJsonFragment(["message" => "Отказано в доступе"]);
    }

    // Админы могут удалять товар
    public function testDestroyForAdminUser(){
        $faker = Faker::create();
        $user = User::factory()->create();
        $user->assignRole("Admin");
        $product = Product::factory()->create();
        $response = $this->actingAs($user, 'sanctum')->delete('/api/products/' . $product->id);
        $response->assertStatus(200)->assertJsonFragment(['message' => "Продукт был успешно удален"]);
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    // Клиенты могут удалять товар
    public function testDestroyForClientUser(){
        $faker = Faker::create();
        $user = User::factory()->create();
        $user->assignRole("Client");
        $product = Product::factory()->create();
        $response = $this->actingAs($user, 'sanctum')->delete('/api/products/' . $product->id);
        $response->assertStatus(403)->assertJsonFragment(['message' => "Отказано в доступе"]);
        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }

    // Гости могут удалять товар
    public function testDestroyForGuestUser(){
        $faker = Faker::create();
        $user = User::factory()->create();
        $user->assignRole("Guest");
        $product = Product::factory()->create();
        $response = $this->actingAs($user, 'sanctum')->delete('/api/products/' . $product->id);
        $response->assertStatus(403)->assertJsonFragment(['message' => "Отказано в доступе"]);
        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }    

    // Нельзя создать товар с невалидными данными
    public function testStoreForAdminUserWtihInvalidData(){
        $faker = Faker::create();
        $user = User::factory()->create();
        $user->assignRole("Admin");
        $productCategory = ProductCategory::factory()->create();

        $productData = [
            "name" => $faker->numberBetween(1,100),
            "composition" => $faker->numberBetween(1,100),
            "calories" => $faker->text,
            "category_id" => $productCategory->id,
            "price" => $faker->text
        ];

        $response = $this->actingAs($user, 'sanctum')->post('/api/products', $productData);
        $response->assertStatus(422)->assertJsonStructure(["errors" => ['name', 'composition', 'calories', 'price']]);
    }

    // Нельзя обновить данные тотвара невалидными данными
    public function testUpdateForAdminUserWtihInvalidData(){
        $faker = Faker::create();
        $user = User::factory()->create();
        $user->assignRole("Admin");
        $product = Product::factory()->create();
        $productCategory = ProductCategory::factory()->create();

        $productData = [
            "name" => $faker->numberBetween(1,100),
            "composition" => $faker->numberBetween(1,100),
            "calories" => $faker->text,
            "category_id" => $productCategory->id,
            "price" => $faker->text
        ];

        $response = $this->actingAs($user, 'sanctum')->patch('/api/products/' . $product->id . '/edit', $productData);
        $response->assertStatus(422)->assertJsonStructure(["errors" => ['name', 'composition', 'calories', 'price']]);
    }
}
