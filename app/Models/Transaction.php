<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'description',
        'amount',
        'type',
        'category',
        'currency',
        'original_amount',
        'original_currency',
        'payment_type',
        'billing_day',
        'imported_transaction_date',
    ];


    /**
     * Connection to user table
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}