<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user',
        'description',
        'amount',
        'type',
        'category',
        'date',
    ];

    protected $casts = [
        'date' => 'date',
    ];
}
