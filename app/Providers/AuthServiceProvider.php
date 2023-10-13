<?php

namespace App\Providers;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;


// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // if (!Role::where('name', 'Admin')->exists()) {
        //     Role::create(['name' => 'Admin']);
        // }
        // if (!Role::where('name', 'Client')->exists()) {
        //     Role::create(['name' => 'Client']);
        // }
        // if (!Role::where('name', 'Guest')->exists()) {
        //     Role::create(['name' => 'Guest']);
        // }
        // $adminRole = Role::create(['name' => 'Admin']);
        // $clientRole = Role::create(['name' => 'Client']);
        // $guestRole = Role::create(['name' => 'Guest']);
    }
}
