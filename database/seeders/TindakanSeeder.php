<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class TindakanSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $make = function (string $kode, string $nama, string $jenis, int $fd, int $fp, int $fk, int $bhp) use ($now) {
            $tarif = $fd + $fp + $fk + $bhp;
            return [
                'uuid'          => (string) Str::uuid(),
                'kode_internal' => $kode,
                'nama_tindakan' => $nama,
                'tarif'         => $tarif,
                'fee_dokter'    => $fd,
                'fee_perawat'   => $fp,
                'fee_klinik'    => $fk,
                'bhp'           => $bhp,
                'jenis'         => $jenis,
                'is_aktif'      => true,
                'created_at'    => $now,
                'updated_at'    => $now,
            ];
        };

        $rows = [
            $make('TND-001', 'Konsultasi Dokter Umum',               'rawat_jalan', 50000, 10000, 20000,  0),
            $make('TND-002', 'Konsultasi Dokter Spesialis',          'rawat_jalan', 80000, 15000, 30000,  0),
            $make('TND-003', 'Tindakan Luka Ringan (Debridement)',   'rawat_jalan', 60000, 20000, 30000, 10000),
            $make('TND-004', 'Infus Cairan',                         'rawat_jalan', 30000, 15000, 15000, 20000),
            $make('TND-005', 'Nebulizer',                            'rawat_jalan', 20000, 10000, 20000, 15000),

            $make('TND-006', 'Visite Rawat Inap (Dokter Umum)',      'rawat_inap',  60000,  0,    20000,  0),
            $make('TND-007', 'Visite Rawat Inap (Spesialis)',        'rawat_inap',  90000,  0,    30000,  0),
            $make('TND-008', 'Pemasangan Kateter Urine',             'rawat_inap',  50000, 20000, 30000, 15000),
            $make('TND-009', 'Pemasangan NGT',                       'rawat_inap',  50000, 20000, 30000, 10000),
            $make('TND-010', 'Penggantian Balutan Luka Sedang',      'rawat_inap',  40000, 15000, 20000, 10000),

            $make('TND-011', 'Scaling Gigi',                         'gigi',        0,     0,     90000,  0),
            $make('TND-012', 'Tambal Gigi Komposit',                 'gigi',        0,     0,    120000,  0),
            $make('TND-013', 'Pencabutan Gigi',                      'gigi',       70000, 10000, 30000,  0),
            $make('TND-014', 'Perawatan Saluran Akar (1 Kanal)',     'gigi',      120000, 15000, 45000,  0),

            $make('TND-015', 'Laboratorium: Hematologi Lengkap',     'laboratorium', 0,     0,    60000, 10000),
            $make('TND-016', 'Laboratorium: Kimia Darah (SGOT/SGPT)', 'laboratorium', 0,     0,    70000, 10000),
            $make('TND-017', 'Laboratorium: Gula Darah Sewaktu',     'laboratorium', 0,     0,    30000,  5000),
            $make('TND-018', 'Laboratorium: Urinalisis Lengkap',     'laboratorium', 0,     0,    50000, 10000),

            $make('TND-019', 'Injeksi IM/SC',                        'umum',       10000, 10000, 10000,  5000),
            $make('TND-020', 'Pemasangan Oksigen Nasal Kanul',       'umum',       20000, 10000, 20000, 15000),
        ];

        DB::table('tindakan')->upsert(
            $rows,
            ['kode_internal'],
            ['nama_tindakan', 'tarif', 'fee_dokter', 'fee_perawat', 'fee_klinik', 'bhp', 'jenis', 'is_aktif', 'updated_at']
        );
    }
}
