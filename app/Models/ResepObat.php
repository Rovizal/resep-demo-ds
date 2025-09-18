<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ResepObat extends Model
{
    use HasFactory;

    protected $table = 'resep_obat';

    protected $fillable = [
        'uuid',
        'e_resep_id',
        'is_racikan',
        'medicine_id',
        'medicine_name',
        'qty',
        'dosis',
        'aturan_pakai',
        'harga_satuan',
        'subtotal',
        'urutan'
    ];

    protected $casts = [
        'is_racikan'    => 'boolean',
        'qty'           => 'integer',
        'harga_satuan'  => 'integer',
        'subtotal'      => 'integer',
        'urutan'        => 'integer',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
        'medicine_id' => 'string',
    ];

    public function header(): BelongsTo
    {
        return $this->belongsTo(EResep::class, 'e_resep_id');
    }

    protected static function booted()
    {
        static::creating(function (self $m) {
            if (empty($m->uuid)) $m->uuid = (string) Str::uuid();
            if (is_null($m->subtotal) && $m->harga_satuan && $m->qty) {
                $m->subtotal = (int) $m->harga_satuan * (int) $m->qty;
            }
        });

        static::saving(function (self $m) {
            if ($m->isDirty(['harga_satuan', 'qty']) || is_null($m->subtotal)) {
                $m->subtotal = ((int) $m->harga_satuan) * ((int) $m->qty);
            }
        });

        static::saved(function (self $m) {
            optional($m->header)->recalcTotal();
        });
        static::deleted(function (self $m) {
            optional($m->header)->recalcTotal();
        });
    }
}
