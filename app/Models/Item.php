<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;
    use HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'userId',
        'title',
        'description',
        'price',
        'category',
        'location',
        'state',
        'condition',
        'publishDate',
        'images',
    ];

    protected $casts = [
        'images' => 'array', // Cast images column to array
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'userId', 'id');
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
