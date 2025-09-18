<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class EResep extends Model
{
    use HasFactory;

    protected $table = 'e_resep';

    public const STATUS_DRAFT     = 'DRAFT';
    public const STATUS_SUBMITTED = 'SUBMITTED';
    public const STATUS_SERVED    = 'SERVED';
    public const STATUS_PAID      = 'PAID';

    public const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_SUBMITTED,
        self::STATUS_SERVED,
        self::STATUS_PAID,
    ];

    protected $fillable = [
        'uuid',
        'rawat_pasien_id',
        'dokter_id',
        'apoteker_id',
        'status',
        'total_amount',
    ];

    protected $casts = [
        'total_amount' => 'integer',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
    ];

    // ========= Relasi =========
    public function rawat(): BelongsTo
    {
        return $this->belongsTo(RawatPasien::class, 'rawat_pasien_id');
    }

    public function dokter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dokter_id');
    }

    public function apoteker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'apoteker_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ResepObat::class, 'e_resep_id');
    }

    // ========= Scopes =========
    public function scopeDraft($q)
    {
        return $q->where('status', self::STATUS_DRAFT);
    }
    public function scopeSubmitted($q)
    {
        return $q->where('status', self::STATUS_SUBMITTED);
    }
    public function scopeServed($q)
    {
        return $q->where('status', self::STATUS_SERVED);
    }
    public function scopePaid($q)
    {
        return $q->where('status', self::STATUS_PAID);
    }

    // ========= Helpers =========
    public function isEditableByDoctor(?User $user): bool
    {
        return $user
            && $user->id === $this->dokter_id
            && $user->role === 'doctor'
            && $this->status === self::STATUS_DRAFT;
    }

    public function isEditableByPharmacist(?User $user): bool
    {
        return $user
            && $user->role === 'pharmacist'
            && in_array($this->status, [self::STATUS_SUBMITTED, self::STATUS_SERVED], true);
    }

    public function isLocked(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    public function recalcTotal(): int
    {
        $total = (int) $this->items()->sum('subtotal');
        $this->forceFill(['total_amount' => $total])->save();
        return $total;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT     => 'DRAFT',
            self::STATUS_SUBMITTED => 'Dikirim ke Apoteker',
            self::STATUS_SERVED    => 'Dilayani',
            self::STATUS_PAID      => 'Lunas',
            default                => $this->status ?? '-',
        };
    }

    protected static function booted()
    {
        static::creating(function (self $m) {
            if (empty($m->uuid)) {
                $m->uuid = (string) Str::uuid();
            }
            // default status jika belum diisi
            if (empty($m->status)) {
                $m->status = self::STATUS_DRAFT;
            }
        });
    }
}
