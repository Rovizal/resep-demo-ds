<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Diagnosis extends Model
{
    use HasFactory;

    protected $table = 'tbl_diagnosis';

    protected $fillable = [
        'uuid',
        'pasien_id',
        'rawat_pasien_id',
        'kode',
        'diagnosis',
        'kode_penyerta',
        'diagnosis_penyerta',
        'keterangan',
        'dokter',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relasi
    public function pasien()
    {
        return $this->belongsTo(Pasien::class, 'pasien_id');
    }
    public function rawat()
    {
        return $this->belongsTo(RawatPasien::class, 'rawat_pasien_id');
    }

    // Scopes
    public function scopeDenganKode($q)
    {
        return $q->whereNotNull('kode');
    }

    // Auto-UUID
    protected static function booted()
    {
        static::creating(function (self $m) {
            if (empty($m->uuid)) $m->uuid = (string) Str::uuid();
        });
    }
}
