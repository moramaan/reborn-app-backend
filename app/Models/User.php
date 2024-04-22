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
        'profile_description',
        'city',
        'state',
        'country',
        'address',
        'zip_code',
    ];

    protected $guarded = [
        'id',
        'is_admin',
        'is_deleted',
        'created_at',
        'updated_at',
    ];

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

    //items of the user that are not sold, i.e. items that are not part of any transaction
    public function unsoldItems()
    {
        return $this->items()->whereNotIn('id', $this->transactionsAsSeller()->pluck('item_id'));
    }

}
