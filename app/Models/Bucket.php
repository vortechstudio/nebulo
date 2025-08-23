<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bucket extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'user_id',
        'description',
        'limit_size',
    ];

    protected $casts = [
        'limit_size' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function objects(): HasMany
    {
        return $this->hasMany(Object::class);
    }
}
