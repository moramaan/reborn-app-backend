<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;
    use HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        // 'id',
        'item_id',
        'buyer_id',
        'seller_id',
        'price',
        'transaction_date',
    ];

    protected $guarded = [
        'id',
        'admin',
        'created_at',
        'updated_at',
    ];

    // protected $casts = [
    //     'transaction_date' => 'datetime:Y-m-d',
    // ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }
}
