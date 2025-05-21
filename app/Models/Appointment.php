<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

class Appointment extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $casts = [
        'date' => 'datetime',
    ];

    public function doctor(): Relation
    {
        return $this->belongsTo(Doctor::class);
    }

    public function patient(): Relation
    {
        return $this->belongsTo(Patient::class);
    }
}
