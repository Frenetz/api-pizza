<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'city',
        'street',
        'house_number',
        'apartment_number',
        'entrance',
        'floor',
        'intercom',
        'gate',
        'comment',
        'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class)->select(['users.id', 'users.name', 'users.surname', 'users.patronymic', 'users.date_of_birth', 'users.email', 'users.phone']);
    }
    

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

}
