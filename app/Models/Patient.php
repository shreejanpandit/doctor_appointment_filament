<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

class Patient extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function appointments(): Relation
    {
        return $this->hasMany(Appointment::class);
    }
    public function user(): Relation
    {
        return $this->belongsTo(User::class);
    }
}
