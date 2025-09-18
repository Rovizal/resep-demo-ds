<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Asesmen extends Model
{
    use HasFactory;

    protected $table = 'tbl_asesmen';

    protected $fillable = [
        'uuid',
        'pasien_id',
        'rawat_pasien_id',
        'file_asesmen',
        'keluhan_utama',
        'riwayat_penyakit_sekarang',
        'riwayat_penyakit_dahulu',
        'riwayat_penyakit_keluarga',
        'suhu',
        'tinggi_badan',
        'berat_badan',
        'tanda_vital_nadi',
        'tanda_vital_td',
        'tanda_vital_rr',
        'tanda_vital_spo2',
        'penurunan_berat_badan',
        'kurang_nafsu_makan',
        'kondisi_gizi',
        'level_nyeri',
        'susah_jalan',
        'alat_bantu_jalan',
        'menopang_duduk',
        'alergi_makanan',
        'alergi_obat',
        'dokter_id',
        'perawat_id',
        'created_by',
    ];

    protected $casts = [
        'suhu' => 'integer',
        'tinggi_badan' => 'integer',
        'berat_badan' => 'integer',
        'tanda_vital_nadi' => 'integer',
        'tanda_vital_rr' => 'integer',
        'tanda_vital_spo2' => 'integer',
        'level_nyeri' => 'integer',
        'susah_jalan' => 'boolean',
        'alat_bantu_jalan' => 'boolean',
        'menopang_duduk' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Auto-UUID
    protected static function booted()
    {
        static::creating(function (self $m) {
            if (empty($m->uuid)) $m->uuid = (string) Str::uuid();
        });
    }

    // Helpers
    protected function bmi(): Attribute
    {
        return Attribute::get(function () {
            if (!$this->tinggi_badan || !$this->berat_badan) return null;
            $m = $this->tinggi_badan / 100;
            if ($m <= 0) return null;
            return round($this->berat_badan / ($m * $m), 1);
        });
    }

    protected function tdSystole(): Attribute
    {
        return Attribute::get(function () {
            if (!$this->tanda_vital_td) return null;
            $p = explode('/', $this->tanda_vital_td);
            return isset($p[0]) ? (int) trim($p[0]) : null;
        });
    }

    protected function tdDiastole(): Attribute
    {
        return Attribute::get(function () {
            if (!$this->tanda_vital_td) return null;
            $p = explode('/', $this->tanda_vital_td);
            return isset($p[1]) ? (int) trim($p[1]) : null;
        });
    }

    public function getFileUrlAttribute(): ?string
    {
        return $this->file_asesmen
            ? url(Storage::url($this->file_asesmen))
            : null;
    }
}
