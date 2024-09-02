<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

class Schedule extends Model
{
    use HasFactory;
    protected $fillable = ['doctor_id', 'week_day', 'start_time', 'end_time'];
    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];
    public function doctor(): Relation
    {
        return $this->belongsTo(Doctor::class);
    }
}
