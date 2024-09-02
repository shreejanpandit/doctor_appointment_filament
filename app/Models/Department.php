<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Relation;

class Department extends Model
{
    use HasFactory;

    /**
     * @return HasMany<Doctor>
     */
    public function doctors(): HasMany
    {
        return $this->hasMany(Doctor::class);
    }
}
