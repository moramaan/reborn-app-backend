<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'email',
        // 'password',
        'profile_description',
        'city',
        'state',
        'country',
        'address',
        'zip_code',
        'admin',
    ];

    // protected $hidden = [
    //     'password',
    //     'remember_token',
    // ];

    public function items()
    {
        return $this->hasMany(Item::class);
    }

    public function transactionsAsBuyer()
    {
        return $this->hasMany(Transaction::class, 'buyer_id');
    }

    public function transactionsAsSeller()
    {
        return $this->hasMany(Transaction::class, 'seller_id');
    }

    public function allTransactions()
    {
        return $this->transactionsAsBuyer->merge($this->transactionsAsSeller);
    }

    public function isAdmin()
    {
        return $this->admin;
    }
}
