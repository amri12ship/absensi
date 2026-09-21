<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class AttendanceLocation extends Model
{
    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'name',
        'address',
        'latitude',
        'longitude',
        'radius',
        'public_token',
        'status',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'radius' => 'integer',
    ];

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function distanceTo(float $latitude, float $longitude): float
    {
        $earthRadius = 6371000;

        $latFrom = deg2rad((float) $this->latitude);
        $lngFrom = deg2rad((float) $this->longitude);
        $latTo = deg2rad($latitude);
        $lngTo = deg2rad($longitude);

        $latDelta = $latTo - $latFrom;
        $lngDelta = $lngTo - $lngFrom;

        $a = sin($latDelta / 2) ** 2
            + cos($latFrom) * cos($latTo) * sin($lngDelta / 2) ** 2;

        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    public function isWithinRadius(float $latitude, float $longitude): bool
    {
        return $this->distanceTo($latitude, $longitude) <= $this->radius;
    }

    public function getScanUrlAttribute(): string
    {
        return route('employee.absensi.scan', ['token' => $this->public_token]);
    }

    public static function generateToken(): string
    {
        return Str::random(32);
    }
}
