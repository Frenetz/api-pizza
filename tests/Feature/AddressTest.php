<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Address;
use Faker\Factory as Faker;


class AddressTest extends TestCase
{
    use DatabaseTransactions;

    // Администраторы могут смотреть список всех адресов
    public function testIndexForAdminUser()
    {
        $user = User::factory()->create();
        $user->assignRole('Admin');
        $response = $this->actingAs($user, 'sanctum')->get('/api/addresses');
        $response->assertStatus(200)->assertJsonStructure([
            'addresses' => [
                '*' => [ 
                    'id',
                    'city',
                    'street',
                    'house_number',
                    'apartment_number',
                    'entrance',
                    'floor',
                    'intercom',
                    'gate',
                    'comment',
                    'user_id',
                    'created_at',
                    'updated_at',
                    'user' => [
                        'id',
                        'name',
                        'surname',
                        'patronymic',
                        'date_of_birth',
                        'email',
                        'phone',
                    ],
                ],
            ],
        ]);
    }

    // Клиенты могут смотреть список своих адресов
    public function testIndexForClientUser()
    {
        $user = User::factory()->create();
        $address = Address::factory()->create(['user_id' => $user->id]);
        $user->assignRole('Client');
        $response = $this->actingAs($user, 'sanctum')->get('/api/addresses');
        $response->assertStatus(200)->assertJsonStructure([
            'addresses' => [
                '*' => [ 
                    'id',
                    'city',
                    'street',
                    'house_number',
                    'apartment_number',
                    'entrance',
                    'floor',
                    'intercom',
                    'gate',
                    'comment',
                    'user_id',
                    'created_at',
                    'updated_at',
                    'user' => [
                        'id',
                        'name',
                        'surname',
                        'patronymic',
                        'date_of_birth',
                        'email',
                        'phone',
                    ],
                ],
            ],
        ]);
        $response->assertJsonFragment(['user_id' => $user->id]);
    }

    // Гости не могут посмотреть список адресов
    public function testIndexForGuestUser(){
        $user = User::factory()->create();
        $user->assignRole("Guest");
        $response = $this->actingAs($user, 'sanctum')->get('/api/addresses');
        $response->assertStatus(403);
        $response->assertJsonFragment(['message' => 'Отказано в доступе']);
    }

    // Администратор может смотреть чужие адреса
    public function testShowForAdminUserWithStrangeAddress(){
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user1->assignRole('Admin');
        $user2->assignRole('Client');
        $address = Address::factory()->create(['user_id' => $user2->id]);
        $response = $this->actingAs($user1, 'sanctum')->get('/api/addresses/' . $address->id);
        $response->assertStatus(200)
        ->assertJsonStructure([
            'address' => [
                'id',
                'city',
                'street',
                'house_number',
                'apartment_number',
                'entrance',
                'floor',
                'intercom',
                'gate',
                'comment',
                'user_id',
                'created_at',
                'updated_at',
                'user' => [
                    'id',
                    'name',
                    'surname',
                    'patronymic',
                    'date_of_birth',
                    'email',
                    'phone',
                ],
            ],
        ]);

        $response->assertJsonFragment(['user_id' => $user2->id]);
    }
    
    // Клиенты могут смотреть свои адреса
    public function testShowForClientUserWithOwnAddress(){
        $user = User::factory()->create();
        $user->assignRole('Client');
        $address = Address::factory()->create(['user_id' => $user->id]);
        $response = $this->actingAs($user, 'sanctum')->get('/api/addresses/' . $address->id);
        $response->assertStatus(200)
        ->assertJsonStructure([
            'address' => [
                'id',
                'city',
                'street',
                'house_number',
                'apartment_number',
                'entrance',
                'floor',
                'intercom',
                'gate',
                'comment',
                'user_id',
                'created_at',
                'updated_at',
                'user' => [
                    'id',
                    'name',
                    'surname',
                    'patronymic',
                    'date_of_birth',
                    'email',
                    'phone',
                ],
            ],
        ]);
        $response->assertJsonFragment(['user_id' => $user->id]);
    }

    // Клиенты не могут смотреть чужие адреса
    public function testShowForClientUserWithStrangeAddress(){
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user1->assignRole('Client');
        $user2->assignRole('Client');
        $address = Address::factory()->create(['user_id' => $user2->id]);
        $response = $this->actingAs($user1, 'sanctum')->get('/api/addresses/' . $address->id);
        $response->assertStatus(403);
        $response->assertJsonFragment(['message' => "Отказано в доступе"]);
    }

    // Гости не могут смотреть конкретный адрес
    public function testShowForGuestUser(){
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user1->assignRole('Client');
        $user2->assignRole("Guest");
        $address = Address::factory()->create(['user_id' => $user1->id]);
        $response = $this->actingAs($user2,'sanctum')->get('/api/addresses/' . $address->id);
        $response->assertStatus(403);
        $response->assertJsonFragment(['message' => 'Отказано в доступе']);
    }

    // Админ создает новый адрес
    public function testStoreForAdminUser()
    {
        $faker = Faker::create();
        $user = User::factory()->create();
        $user->assignRole('Admin'); 

        $addressData = [
            'city' => $faker->city,
            'street' => $faker->streetName,
            'house_number' => $faker->buildingNumber,
            'apartment_number' => $faker->numberBetween(1, 100),
            'entrance' => $faker->randomElement(['A', 'B', 'C', 'D']),
            'floor' => $faker->numberBetween(1, 20),
            'intercom' => $faker->randomNumber(4),
            'gate' => $faker->boolean,
            'comment' => $faker->text,
            'user_id' => $user->id,
        ];

        $response = $this->actingAs($user, 'sanctum')->post('/api/addresses', $addressData);
        $response->assertStatus(201);
        $response->assertJsonFragment(['message' => "Адрес успешно добавлен"]);
        $this->assertDatabaseHas('addresses', $addressData);
    }

    // Клиент создает новый адрес
    public function testStoreForClientUser()
    {
        $faker = Faker::create();
        $user = User::factory()->create();
        $user->assignRole('Client'); 

        $addressData = [
            'city' => $faker->city,
            'street' => $faker->streetName,
            'house_number' => $faker->buildingNumber,
            'apartment_number' => $faker->numberBetween(1, 100),
            'entrance' => $faker->randomElement(['A', 'B', 'C', 'D']),
            'floor' => $faker->numberBetween(1, 20),
            'intercom' => $faker->randomNumber(4),
            'gate' => $faker->boolean,
            'comment' => $faker->text,
            'user_id' => $user->id,
        ];

        $response = $this->actingAs($user, 'sanctum')->post('/api/addresses', $addressData);
        $response->assertStatus(201);
        $response->assertJsonFragment(['message' => "Адрес успешно добавлен"]);
        $this->assertDatabaseHas('addresses', $addressData);
    }

    // Гость не может создать адрес
    public function testStoreForGuestUser(){
        $faker = Faker::create();
        $user = User::factory()->create();
        $user->assignRole('Guest'); 

        $addressData = [
            'city' => $faker->city,
            'street' => $faker->streetName,
            'house_number' => $faker->buildingNumber,
            'apartment_number' => $faker->numberBetween(1, 100),
            'entrance' => $faker->randomElement(['A', 'B', 'C', 'D']),
            'floor' => $faker->numberBetween(1, 20),
            'intercom' => $faker->randomNumber(4),
            'gate' => $faker->boolean,
            'comment' => $faker->text,
            'user_id' => $user->id,
        ];

        $response = $this->actingAs($user, 'sanctum')->post('/api/addresses', $addressData);
        $response->assertStatus(403);
        $response->assertJsonFragment(['message' => "Отказано в доступе"]);
        $this->assertDatabaseMissing('addresses', $addressData);
    }

    // Админ может изменить свой адрес
    public function testEditForAdminUserWithOwnAddress(){
        $faker = Faker::create();
        $user = User::factory()->create();
        $user->assignRole('Admin');
        $address = Address::factory()->create(['user_id' => $user->id]);

        $addressData = [
            'city' => $faker->city,
            'street' => $faker->streetName,
            'house_number' => $faker->buildingNumber,
            'apartment_number' => $faker->numberBetween(1, 100),
            'entrance' => $faker->randomElement(['A', 'B', 'C', 'D']),
            'floor' => $faker->numberBetween(1, 20),
            'intercom' => $faker->randomNumber(4),
            'gate' => $faker->boolean,
            'comment' => $faker->text,
        ];

        $response = $this->actingAs($user, 'sanctum')->patch('/api/addresses/' . $address->id . '/edit', $addressData);
        $response->assertStatus(200)->assertJsonFragment(['message' => 'Адрес успешно обновлен']);
        $this->assertDatabaseHas('addresses', $addressData);
    }

    // Админ может изменить чужой адрес
    public function testEditForAdminUserWithStrangeAddress(){
        $faker = Faker::create();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user1->assignRole('Admin');
        $user2->assignRole('Client');
        $address = Address::factory()->create(['user_id' => $user2->id]);

        $addressData = [
            'city' => $faker->city,
            'street' => $faker->streetName,
            'house_number' => $faker->buildingNumber,
            'apartment_number' => $faker->numberBetween(1, 100),
            'entrance' => $faker->randomElement(['A', 'B', 'C', 'D']),
            'floor' => $faker->numberBetween(1, 20),
            'intercom' => $faker->randomNumber(4),
            'gate' => $faker->boolean,
            'comment' => $faker->text,
        ];

        $response = $this->actingAs($user1, 'sanctum')->patch('/api/addresses/' . $address->id . '/edit', $addressData);
        $response->assertStatus(200)->assertJsonFragment(['message' => 'Адрес успешно обновлен']);
        $this->assertDatabaseHas('addresses', $addressData);
    }

    // Клиент может изменить свой адрес
    public function testEditForClientUserWithOwnAddress(){
        $faker = Faker::create();
        $user = User::factory()->create();
        $user->assignRole('Client');
        $address = Address::factory()->create(['user_id' => $user->id]);

        $addressData = [
            'city' => $faker->city,
            'street' => $faker->streetName,
            'house_number' => $faker->buildingNumber,
            'apartment_number' => $faker->numberBetween(1, 100),
            'entrance' => $faker->randomElement(['A', 'B', 'C', 'D']),
            'floor' => $faker->numberBetween(1, 20),
            'intercom' => $faker->randomNumber(4),
            'gate' => $faker->boolean,
            'comment' => $faker->text,
        ];

        $response = $this->actingAs($user, 'sanctum')->patch('/api/addresses/' . $address->id . '/edit', $addressData);
        $response->assertStatus(200)->assertJsonFragment(['message' => 'Адрес успешно обновлен']);
        $this->assertDatabaseHas('addresses', $addressData);
    }

    // Клиент не может изменить чужой адрес
    public function testEditForClientUserWithStrangeAddress(){
        $faker = Faker::create();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user1->assignRole('Client');
        $user2->assignRole('Client');
        $address = Address::factory()->create(['user_id' => $user2->id]);

        $addressData = [
            'city' => $faker->city,
            'street' => $faker->streetName,
            'house_number' => $faker->buildingNumber,
            'apartment_number' => $faker->numberBetween(1, 100),
            'entrance' => $faker->randomElement(['A', 'B', 'C', 'D']),
            'floor' => $faker->numberBetween(1, 20),
            'intercom' => $faker->randomNumber(4),
            'gate' => $faker->boolean,
            'comment' => $faker->text,
        ];

        $response = $this->actingAs($user1, 'sanctum')->patch('/api/addresses/' . $address->id . '/edit', $addressData);
        $response->assertStatus(403)->assertJsonFragment(["message" => "Отказано в доступе"]);
        $this->assertDatabaseMissing('addresses', $addressData);
    }

    // Гость не может изменить адрес
    public function testEditForGuestUser(){
        $faker = Faker::create();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user1->assignRole('Guest');
        $user2->assignRole('Client');
        $address = Address::factory()->create(['user_id' => $user2->id]);

        $addressData = [
            'city' => $faker->city,
            'street' => $faker->streetName,
            'house_number' => $faker->buildingNumber,
            'apartment_number' => $faker->numberBetween(1, 100),
            'entrance' => $faker->randomElement(['A', 'B', 'C', 'D']),
            'floor' => $faker->numberBetween(1, 20),
            'intercom' => $faker->randomNumber(4),
            'gate' => $faker->boolean,
            'comment' => $faker->text,
        ];
        $response = $this->actingAs($user1, 'sanctum')->patch('/api/addresses/' . $address->id . '/edit', $addressData);
        $response->assertStatus(403)->assertJsonFragment(["message" => "Отказано в доступе"]);
        $this->assertDatabaseMissing('addresses', $addressData);
    }

    // Админ может удалить чужой адрес
    public function testDestroyForAdminUserWithStrangeAddress(){
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user1->assignRole('Admin');
        $user2->assignRole('Client');
        $address = Address::factory()->create(['user_id' => $user2->id]);
        $response = $this->actingAs($user1, 'sanctum')->delete('/api/addresses/' . $address->id);
        $response->assertStatus(200)->assertJsonFragment(["message" => "Адрес успешно удален"]);
        $this->assertDatabaseMissing('addresses', ['id' => $address->id]);
    }

    // Админ может удалить свой адрес
    public function testDestroyForAdminUserWithOwnAddress(){
        $user = User::factory()->create();
        $user->assignRole('Admin');
        $address = Address::factory()->create(['user_id' => $user->id]);
        $response = $this->actingAs($user, 'sanctum')->delete('/api/addresses/' . $address->id);
        $response->assertStatus(200)->assertJsonFragment(["message" => "Адрес успешно удален"]);
        $this->assertDatabaseMissing('addresses', ['id' => $address->id]);
    }
    
    // Клиент не может удалить чужой адрес
    public function testDestroyForClientUserWithStrangeAddress(){
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user1->assignRole('Client');
        $user2->assignRole('Client');
        $address = Address::factory()->create(['user_id' => $user2->id]);
        $response = $this->actingAs($user1, 'sanctum')->delete('/api/addresses/' . $address->id);
        $response->assertStatus(403)->assertJsonFragment(["message" => "Отказано в доступе"]);
        $this->assertDatabaseHas('addresses', ['id' => $address->id]);
    }

    // Клиент может удалить свой адрес 
    public function testDestroyForClientUserWithOwnAddress(){
        $user = User::factory()->create();
        $user->assignRole('Client');
        $address = Address::factory()->create(['user_id' => $user->id]);
        $response = $this->actingAs($user, 'sanctum')->delete('/api/addresses/' . $address->id);
        $response->assertStatus(200)->assertJsonFragment(["message" => "Адрес успешно удален"]);
        $this->assertDatabaseMissing('addresses', ['id' => $address->id]);
    }

    // Гость не может удалить адрес
    public function testDestroyForGuest(){
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user1->assignRole('Guest');
        $user2->assignRole('Client');
        $address = Address::factory()->create(['user_id' => $user2->id]);
        $response = $this->actingAs($user1, 'sanctum')->delete('/api/addresses/' . $address->id);
        $response->assertStatus(403)->assertJsonFragment(["message" => "Отказано в доступе"]);
        $this->assertDatabaseHas('addresses', ['id' => $address->id]);
    }

    // Нельзя создать адрес с невалидными данными
    public function testCantCreateAddressWithInvalidData()
    {
        $faker = Faker::create();
        $user = User::factory()->create();
        $user->assignRole('Admin'); 

        $addressData = [
            'city' => $faker->numberBetween(1,20),
            'street' => $faker->numberBetween(1,20),
            'house_number' => $faker->text,
            'apartment_number' => $faker->text,
            'entrance' => $faker->numberBetween(1,20),
            'floor' => $faker->text,
            'intercom' => $faker->text,
            'gate' => $faker->text,
            'comment' => $faker->numberBetween(1,20),
            'user_id' => $user->id,
        ];

        $response = $this->actingAs($user, 'sanctum')->post('/api/addresses', $addressData);
        $response->assertStatus(422);
        $response->assertJson([
            'errors' => [
                'city' => ['The city field must be a string.'],
                'street' => ['The street field must be a string.'],
                'house_number' => ['The house number field must be an integer.'],
                'apartment_number' => ['The apartment number field must be an integer.'],
                'entrance' => ['The entrance field must be a string.'],
                'floor' => ['The floor field must be an integer.'],
                'intercom' => ['The intercom field must be an integer.'],
                'gate' => ['The gate field must be true or false.'],
                'comment' => ['The comment field must be a string.'],
            ],
        ]);
        $this->assertDatabaseMissing('addresses', $addressData);
    }

    // Нельзя изменить данные адреса на невалидные
    public function testCantUpdateAddressWithInvalidData()
    {
        $faker = Faker::create();
        $user = User::factory()->create();
        $user->assignRole('Admin'); 
        $address = Address::factory()->create(['user_id' => $user->id]);

        $addressData = [
            'city' => $faker->numberBetween(1,20),
            'street' => $faker->numberBetween(1,20),
            'house_number' => $faker->text,
            'apartment_number' => $faker->text,
            'entrance' => $faker->numberBetween(1,20),
            'floor' => $faker->text,
            'intercom' => $faker->text,
            'gate' => $faker->text,
            'comment' => $faker->numberBetween(1,20),
            'user_id' => $user->id,
        ];

        $response = $this->actingAs($user, 'sanctum')->patch('/api/addresses/' . $address->id . '/edit', $addressData);
        $response->assertStatus(422);
        $response->assertJson([
            'errors' => [
                'city' => ['The city field must be a string.'],
                'street' => ['The street field must be a string.'],
                'house_number' => ['The house number field must be an integer.'],
                'apartment_number' => ['The apartment number field must be an integer.'],
                'entrance' => ['The entrance field must be a string.'],
                'floor' => ['The floor field must be an integer.'],
                'intercom' => ['The intercom field must be an integer.'],
                'gate' => ['The gate field must be true or false.'],
                'comment' => ['The comment field must be a string.'],
            ],
        ]);
        $this->assertDatabaseMissing('addresses', $addressData);
    }
}
