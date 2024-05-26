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
        'lastName',
        'email',
        'phone',
        'showPhone',
        'profileDescription',
        'city',
        'state',
        'country',
        'address',
        'zipCode',
    ];

    protected $guarded = [
        'id',
        'isAdmin',
        'isDeleted',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'showPhone' => 'boolean',
        'isAdmin' => 'boolean',
        'isDeleted' => 'boolean',
    ];

    // this way this fields are not returned in the response
    protected $hidden = ['created_at', 'updated_at', 'isDeleted', 'country', 'address', 'zipCode'];

    public function items()
    {
        return $this->hasMany(Item::class, 'userId', 'id');
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

    //list only users that are not flagged as deleted
    public function scopeActive($query)
    {
        return $query->where('isDeleted', false);
    }
}
