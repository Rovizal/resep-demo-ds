<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Pasien extends Model
{
    use HasFactory;

    protected $table = 'pasien';

    // Mass-assign (silakan sesuaikan bila perlu)
    protected $fillable = [
        'uuid',
        'no_rm',
        'panggilan',
        'nama',
        'satusehat_id',
        'nik',
        'tmp_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'golongan_darah',
        'alamat',
        'nohp',
        'telepon',
        'status_satusehat',
        'id_wilayah',
        'email',
        'status_hidup',
        'tanggal_meninggal',
        'nama_kontak_darurat',
        'hubungan_kontak',
        'telepon_kontak',
        'status_menikah',
        'pendidikan',
        'pekerjaan',
        'no_asuransi',
        'tanggal_daftar',
        'jenis_bayar',
        'agama',
        'nama_asuransi',
        'sync_status',
        'last_sync_attempt_at',
        'last_sync_response',
    ];

    // Cast tanggal/datetime biar otomatis jadi Carbon
    protected $casts = [
        'tanggal_lahir'         => 'date',
        'tanggal_meninggal'     => 'date',
        'tanggal_daftar'        => 'date',
        'last_sync_attempt_at'  => 'datetime',
    ];

    // ===== Relasi =====
    public function rawat(): HasMany
    {
        return $this->hasMany(RawatPasien::class, 'pasien_id');
    }

    public function asesmen(): HasMany
    {
        return $this->hasMany(Asesmen::class, 'pasien_id'); // tabel: tbl_asesmen
    }

    public function diagnosis(): HasMany
    {
        return $this->hasMany(Diagnosis::class, 'pasien_id'); // tabel: tbl_diagnosis
    }

    // Kunjungan aktif (kalau cuma satu yang aktif per pasien, bisa pakai hasOneThrough/hasOne with constraint)
    public function rawatAktif()
    {
        return $this->hasOne(RawatPasien::class, 'pasien_id')->where('status', 'aktif');
    }

    // ===== Accessors / Attributes =====
    protected function nameWithTitle(): Attribute
    {
        return Attribute::get(fn() => trim(($this->panggilan ? $this->panggilan . ' ' : '') . ($this->nama ?? '')));
    }

    protected function age(): Attribute
    {
        return Attribute::get(function () {
            if (!$this->tanggal_lahir) return null;
            return Carbon::parse($this->tanggal_lahir)->age; // integer (tahun)
        });
    }

    protected function jkLabel(): Attribute
    {
        return Attribute::get(function () {
            return match ($this->jenis_kelamin) {
                'male'   => 'Laki-laki',
                'female' => 'Perempuan',
                'other'  => 'Lainnya',
                default  => 'Tidak diketahui',
            };
        });
    }

    // ===== Scopes =====

    /** Cari by nama / RM / NIK / no HP */
    public function scopeSearch($q, ?string $term)
    {
        $term = trim((string) $term);
        if ($term === '') return $q;

        return $q->where(function ($w) use ($term) {
            $w->where('nama', 'like', "%{$term}%")
                ->orWhere('no_rm', 'like', "%{$term}%")
                ->orWhere('nik', 'like', "%{$term}%")
                ->orWhere('nohp', 'like', "%{$term}%");
        });
    }

    /** Hanya pasien yang belum punya rawat_pasien status 'aktif' */
    public function scopeWithoutActiveVisit($q)
    {
        return $q->whereDoesntHave('rawat', fn($w) => $w->where('status', 'aktif'));
    }

    // ===== Boot: auto-UUID (kalau belum diisi) =====
    protected static function booted()
    {
        static::creating(function (self $model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    // (Opsional) kalau mau route model pakai uuid:
    // public function getRouteKeyName(): string { return 'uuid'; }
}
