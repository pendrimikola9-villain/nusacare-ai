<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HealthScreening extends Model
{
    protected $guarded = [];
protected $casts = ['ai_result' => 'array'];
public function user() { return $this->belongsTo(User::class); }
}
