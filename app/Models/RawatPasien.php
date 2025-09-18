<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class RawatPasien extends Model
{
    use HasFactory;

    protected $table = 'rawat_pasien';

    protected $fillable = [
        'uuid',
        'pasien_id',
        'dokter_id',
        'poli_id',
        'jenis_layanan',
        'cara_masuk',
        'referensi_masuk_dari',
        'jenis_bayar',
        'asuransi',
        'cara_keluar',
        'tanggal_masuk',
        'tanggal_keluar',
        'status',
        'satusehat_id',
    ];

    protected $casts = [
        'tanggal_masuk' => 'datetime',
        'tanggal_keluar' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relasi
    public function pasien()
    {
        return $this->belongsTo(Pasien::class, 'pasien_id');
    }
    public function dokter()
    {
        return $this->belongsTo(User::class, 'dokter_id');
    }
    public function asesmen()
    {
        return $this->hasMany(Asesmen::class, 'rawat_pasien_id');
    }
    public function diagnosis()
    {
        return $this->hasMany(Diagnosis::class, 'rawat_pasien_id');
    }
    public function resep()
    {
        return $this->hasOne(EResep::class, 'rawat_pasien_id');
    } // header e_resep

    // Scopes
    public function scopeAktif($q)
    {
        return $q->where('status', 'aktif');
    }
    public function scopeSelesai($q)
    {
        return $q->where('status', 'selesai');
    }

    // Auto-UUID
    protected static function booted()
    {
        static::creating(function (self $m) {
            if (empty($m->uuid)) $m->uuid = (string) Str::uuid();
        });
    }
}
