<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Attendance extends Model
{
    public const STATUS_PRESENT = 'present';

    public const STATUS_LATE = 'late';

    public const STATUS_LEFT = 'left';

    public const STATUS_IZIN = 'izin';

    public const STATUS_SICK = 'sick';

    public const STATUS_ALPHA = 'alpha';

    public const STATUS_LIBUR = 'libur';

    public const STATUS_LABELS = [
        self::STATUS_PRESENT => 'Hadir',
        self::STATUS_LATE => 'Terlambat',
        self::STATUS_LEFT => 'Selesai',
        self::STATUS_IZIN => 'Izin',
        self::STATUS_SICK => 'Sakit',
        self::STATUS_ALPHA => 'Alpha',
        self::STATUS_LIBUR => 'Libur',
    ];

    public const STATUS_BADGES = [
        self::STATUS_PRESENT => 'bg-success',
        self::STATUS_LATE => 'bg-warning text-dark',
        self::STATUS_LEFT => 'bg-success',
        self::STATUS_IZIN => 'bg-info text-dark',
        self::STATUS_SICK => 'bg-danger',
        self::STATUS_ALPHA => 'bg-dark',
        self::STATUS_LIBUR => 'bg-secondary',
    ];

    protected $fillable = [
        'employee_id',
        'attendance_location_id',
        'date',
        'check_in',
        'check_out',
        'status',
        'selfie_path',
        'check_in_lat',
        'check_in_lng',
        'check_out_lat',
        'check_out_lng',
        'check_in_distance',
        'check_out_distance',
    ];

    protected $casts = [
        'date' => 'date',
        'check_in_distance' => 'integer',
        'check_out_distance' => 'integer',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(AttendanceLocation::class, 'attendance_location_id');
    }

    public function hasCheckedIn(): bool
    {
        return $this->check_in !== null;
    }

    public function hasCheckedOut(): bool
    {
        return $this->check_out !== null;
    }

    public function getSelfieUrlAttribute(): ?string
    {
        if (! $this->selfie_path) {
            return null;
        }

        return Storage::disk('public')->url($this->selfie_path);
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? ucfirst($this->status);
    }

    public function statusBadge(): string
    {
        return self::STATUS_BADGES[$this->status] ?? 'bg-secondary';
    }
}