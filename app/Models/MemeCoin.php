<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemeCoin extends Model
{
    protected $fillable = ['user_id', 'full_name', 'coin_name', 'attempts'];
}
