<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'state',
        'condition',
        'publish_date',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transaction()
    {
        return $this->hasOne(Transaction::class);
    }

    // Get only the items that are available or reserved
    public static function listAvailableItems()
    {
        return Item::whereIn('state', ['available', 'reserved'])->get();
    }

    // Items can only be updated if they are not sold
    public function canBeUpdated()
    {
        return $this->state !== 'sold';
    }
}
