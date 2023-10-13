<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Faker\Factory as Faker;

class AuthTest extends TestCase
{
    use DatabaseTransactions;
    
    // Администраторы не могут зарегистрировать аккаунт
    public function testRegisterForAdminUser(): void
    {
        $faker = Faker::create();
        $user = User::factory()->create();
        $user->assignRole('Admin');

        $registerData = [
            "name" => $faker->name,
            "email" => $faker->email,
            "password" => $faker->text,
            "surname" => $faker->name,
            "patronymic" => $faker->name,
            "phone" => $faker->text,
            "date_of_birth" => $faker->date  
        ];

        $response = $this->actingAs($user,'sanctum')->post('/api/register', $registerData);
        $response->assertStatus(403)->assertJsonFragment(['message' => 'Отказано в доступе']);
    }

    // Клиенты не могут зарегистрировать аккаунт
    public function testRegisterForClientUser(): void
    {
        $faker = Faker::create();
        $user = User::factory()->create();
        $user->assignRole('Client');

        $registerData = [
            "name" => $faker->name,
            "email" => $faker->email,
            "password" => $faker->text,
            "surname" => $faker->name,
            "patronymic" => $faker->name,
            "phone" => $faker->text,
            "date_of_birth" => $faker->date  
        ];

        $response = $this->actingAs($user,'sanctum')->post('/api/register', $registerData);
        $response->assertStatus(403)->assertJsonFragment(['message' => 'Отказано в доступе']);
    }

    // Гости могут зарегистрировать аккаунт
    public function testRegisterForGuestUser(): void
    {
        $faker = Faker::create();
        $user = User::factory()->create();
        $user->assignRole('Guest');

        $registerData = [
            "name" => $faker->name,
            "email" => $faker->email,
            "password" => $faker->text,
            "surname" => $faker->name,
            "patronymic" => $faker->name,
            "phone" => $faker->text,
            "date_of_birth" => $faker->date  
        ];

        $response = $this->actingAs($user,'sanctum')->post('/api/register', $registerData);
        $response->assertStatus(201)->assertJsonFragment(['message' => 'Пользователь успешно зарегистрирован']);
    }

    // Администраторы не могут заходить в аккаунт
    public function testLoginForAdminUser(): void
    {
        $faker = Faker::create();
        $user = User::factory()->create();
        $user->assignRole('Admin');
        $user2 = User::factory()->create();

        $registerData = [
            "email" => $user2->email,
            "password" => $user2->password,
        ];

        $response = $this->actingAs($user,'sanctum')->post('/api/login', $registerData);
        $response->assertStatus(403)->assertJsonFragment(['message' => 'Отказано в доступе']);
    }

    // Клиенты не могут заходить в аккаунт
    public function testLoginForClientUser(): void
    {
        $faker = Faker::create();
        $user = User::factory()->create();
        $user->assignRole('Client');
        $user2 = User::factory()->create();

        $registerData = [
            "email" => $user2->email,
            "password" => $user2->password,
        ];

        $response = $this->actingAs($user,'sanctum')->post('/api/login', $registerData);
        $response->assertStatus(403)->assertJsonFragment(['message' => 'Отказано в доступе']);
    }   
    
    // Гости могут заходить в аккаунт 
    public function testLoginForGuestUser()
    {
        $faker = Faker::create();
        $password = $faker->password;
        $user = User::factory()->create(['password' => $password]);
        $loginData = ['email' => $user->email, 'password' => $password];
        $response = $this->post('/api/login', $loginData);
        $response->assertStatus(200);
        $response->assertJsonStructure(['token']);
    }

    // Админы могут выйти из аккаунта
    public function testLogoutForAdminUser(){
        $user = User::factory()->create();
        $user->assignRole("Admin");
        $response = $this->actingAs($user,'sanctum')->get('/api/logout');
        $response->assertStatus(200)->assertJsonFragment(['message' => 'Вы вышли из системы']);
    }

    // Клиенты могут выйти из аккаунта
    public function testLogoutForClientUser(){
        $user = User::factory()->create();
        $user->assignRole("Client");
        $response = $this->actingAs($user,'sanctum')->get('/api/logout');
        $response->assertStatus(200)->assertJsonFragment(['message' => 'Вы вышли из системы']);
    }

    // Гости не могут выйти из аккаунта
    public function testLogoutForGuestUser(){
        $user = User::factory()->create();
        $user->assignRole("Guest");
        $response = $this->actingAs($user,'sanctum')->get('/api/logout');
        $response->assertStatus(403)->assertJsonFragment(['message' => 'Отказано в доступе']);
    }

    // Админы могут смотреть информацию о своем аккаунте
    public function testUserForAdminUser(){
        $user = User::factory()->create();
        $user->assignRole("Admin");
        $response = $this->actingAs($user, 'sanctum')->get('/api/user');
        $response->assertStatus(200)->assertJsonStructure([
            "id",
            "name",
            "surname",
            "patronymic",
            "date_of_birth",
            "email",
            "email_verified_at",
            "phone",
            "created_at",
            "updated_at",
            "roles",
        ]);
    }

    // Клиенты могут смотреть информацию о своем аккаунте
    public function testUserForClientUser(){
        $user = User::factory()->create();
        $user->assignRole("Client");
        $response = $this->actingAs($user, 'sanctum')->get('/api/user');
        $response->assertStatus(200)->assertJsonStructure([
            "id",
            "name",
            "surname",
            "patronymic",
            "date_of_birth",
            "email",
            "email_verified_at",
            "phone",
            "created_at",
            "updated_at",
            "roles",
        ]);
    }

    // Гости не могут смотреть информацию о своем аккаунте
    public function testUserForGuestUser(){
        $user = User::factory()->create();
        $user->assignRole("Guest");
        $response = $this->actingAs($user, 'sanctum')->get('/api/user');
        $response->assertStatus(403)->assertJsonFragment(['message' => 'Отказано в доступе']);
    }  
    
    // Админы могут смотреть список всех зарегистированных пользователей
    public function testUsersForAdminUser(){
        $user = User::factory()->create();
        $user->assignRole("Admin");
        $response = $this->actingAs($user, 'sanctum')->get('/api/users');
        $response->assertStatus(200)->assertJsonStructure([
            'users' => [
                '*' => [
                    "id",
                    "name",
                    "surname",
                    "patronymic",
                    "date_of_birth",
                    "email",
                    "email_verified_at",
                    "phone",
                    "created_at",
                    "updated_at",
                    "roles"
                ]
            ]
        ]);
    }

    // Клиенты не могут смотреть список зарегистрированных пользователей
    public function testUsersForClientUser(){
        $user = User::factory()->create();
        $user->assignRole("Client");
        $response = $this->actingAs($user, 'sanctum')->get('/api/users');
        $response->assertStatus(403)->assertJsonFragment(['message' => 'Отказано в доступе']);
    }

    // Гости не могут смотреть список зарегистрированных пользователей
    public function testUsersForGuestUser(){
        $user = User::factory()->create();
        $user->assignRole("Guest");
        $response = $this->actingAs($user, 'sanctum')->get('/api/users');
        $response->assertStatus(403)->assertJsonFragment(['message' => 'Отказано в доступе']);
    }

    // Нельзя зарегистрировать пользователя с невалидными данными
    public function testRegisterForGuestUserWithInvalidData(){
        $faker = Faker::create();
        $user = User::factory()->create();
        $user->assignRole('Guest');

        $registerData = [
            "name" => $faker->numberBetween(1,10),
            "email" => $faker->numberBetween(1,10),
            "password" => $faker->numberBetween(1,10),
            "surname" => $faker->numberBetween(1,10),
            "patronymic" => $faker->numberBetween(1,10),
            "phone" => $faker->numberBetween(1,10),
            "date_of_birth" => $faker->numberBetween(1,10)  
        ];

        $response = $this->actingAs($user,'sanctum')->post('/api/register', $registerData);
        $response->assertStatus(422)->assertJsonStructure(['errors' => [
            'name',
            'email',
            'password',
            'patronymic',
            'phone',
            'date_of_birth'
        ]]);
    }

    // Нельзя войти в аккаунт с невалидными данными
    public function testLoginForGuestUserWithInvalidData()
    {
        $faker = Faker::create();
        $password = $faker->password;
        $user = User::factory()->create(['password' => $password]);
        $loginData = ['email' => $faker->numberBetween(1,10), 
        'password' => $faker->numberBetween(1,10)];
        $response = $this->post('/api/login', $loginData);
        $response->assertStatus(422);
        $response->assertJsonStructure(['errors' =>
        ['email', 'password']]);
    }

    // Нельзя войти в аккаунт с неправильными логином/паролем
    public function testLoginForGuestUserWithIncorrectData()
    {
        $faker = Faker::create();
        $email1 = $faker->email;
        $email2 = $faker->email;
        $password1 = $faker->password;
        $password2 = $faker->password;
        $user = User::factory()->create(['email' => $email1,'password' => $password1]);
        $loginData = ['email' => $email2, 
        'password' => $password2];
        $response = $this->post('/api/login', $loginData);
        $response->assertStatus(422);
        $response->assertJsonFragment(['message' => 'Неверные учетные данные']);
    }
}
