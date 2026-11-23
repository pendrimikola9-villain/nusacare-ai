<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WellnessProgram extends Model
{
    protected $guarded = [];
    protected $casts = ['ai_timeline' => 'array'];
}
