<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * @method updateOrCreate()
 */
class Doctor extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'department_id',
        'contact',
        'bio',
        'image',
    ];

    /**
     * @return BelongsTo<Department, Doctor>
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function appointments(): Relation
    {
        return $this->hasMany(Appointment::class);
    }

    public function schedules(): Relation
    {
        return $this->hasMany(Schedule::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
