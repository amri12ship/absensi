<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'name',
        'date',
        'status',
        'description',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }
}