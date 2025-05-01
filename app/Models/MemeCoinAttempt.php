<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemeCoinAttempt extends Model
{
    protected $fillable = [
        'user_id', 'full_name', 'attempted_name', 'attempt_number', 'status', 'error_message'
    ];
}
