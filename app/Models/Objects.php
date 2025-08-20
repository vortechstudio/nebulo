<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Objects extends Model
{
    use HasFactory;

    protected $fillable = [
        'bucket_id',
        'name',
        'path',
        'size',
        'mime_type',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function bucket(): BelongsTo
    {
        return $this->belongsTo(Bucket::class);
    }
}
