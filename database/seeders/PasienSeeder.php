<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Faker\Factory as Faker;
use Carbon\Carbon;

class PasienSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');
        $now   = Carbon::now();

        $genderEnum  = ['male', 'female'];
        $bloodEnum   = ['unknown', 'A', 'B', 'AB', 'O'];
        $maritalEnum = ['belum_menikah', 'menikah', 'cerai'];
        $payEnum     = ['bpjs', 'umum', 'asuransi_lain'];
        $religEnum   = ['islam', 'kristen', 'protestan', 'hindu', 'budha', 'konghucu'];

        $rows = [];
        for ($i = 1; $i <= 20; $i++) {
            $noRm = 'RM' . str_pad((string)$i, 6, '0', STR_PAD_LEFT);

            $email   = $faker->boolean(70) ? $faker->unique()->safeEmail() : null;
            $telepon = $faker->boolean(40) ? '021' . $faker->numerify('########') : null;

            $jenisBayar   = $payEnum[array_rand($payEnum)];
            $namaAsuransi = $jenisBayar === 'asuransi_lain'
                ? $faker->randomElement(['Allianz', 'Prudential', 'Axa', 'Manulife'])
                : null;

            $statusHidup  = $faker->boolean(95) ? 'hidup' : 'meninggal';
            $tglMeninggal = $statusHidup === 'meninggal'
                ? $faker->dateTimeBetween('-2 years', '-1 months')
                : null;

            $gender    = $faker->randomElement($genderEnum);
            $panggilan = $gender === 'male' ? 'Tn.' : 'Ny.';
            $nama      = $faker->name($gender);

            $rows[] = [
                'uuid'                => (string) Str::uuid(),
                'no_rm'               => $noRm,
                'panggilan'           => $panggilan,
                'nama'                => $nama,
                'satusehat_id'        => $faker->boolean(20) ? 'PASIEN-' . Str::upper(Str::random(10)) : null,
                'nik'                 => $faker->numerify(str_repeat('#', 16)),
                'tmp_lahir'           => $faker->city(),
                'tanggal_lahir'       => $faker->dateTimeBetween('-80 years', '-18 years')->format('Y-m-d'),
                'jenis_kelamin'       => $gender,
                'golongan_darah'      => $bloodEnum[array_rand($bloodEnum)],
                'alamat'              => $faker->address(),
                'nohp'                => '08' . $faker->numerify('##########'),
                'telepon'             => $telepon,
                'status_satusehat'    => $faker->boolean(30) ? $faker->randomElement(['synced', 'pending', 'error']) : null,
                'id_wilayah'          => $faker->boolean(60) ? $faker->numerify('3173####') : null,

                'email'               => $email,
                'status_hidup'        => $statusHidup,
                'tanggal_meninggal'   => $tglMeninggal,

                'nama_kontak_darurat' => $faker->boolean(85) ? $faker->name() : null,
                'hubungan_kontak'     => $faker->boolean(85) ? $faker->randomElement(['Suami', 'Istri', 'Ayah', 'Ibu', 'Anak', 'Saudara']) : null,
                'telepon_kontak'      => $faker->boolean(85) ? '08' . $faker->numerify('##########') : null,

                'status_menikah'      => $maritalEnum[array_rand($maritalEnum)],
                'pendidikan'          => $faker->randomElement(['SD', 'SMP', 'SMA', 'D3', 'S1', 'S2']),
                'pekerjaan'           => $faker->randomElement(['Karyawan', 'Wiraswasta', 'PNS', 'Ibu Rumah Tangga', 'Pelajar/Mahasiswa', 'Petani']),
                'no_asuransi'         => $jenisBayar === 'bpjs' ? $faker->numerify('000#########') : ($jenisBayar === 'asuransi_lain' ? $faker->numerify('INS########') : null),
                'tanggal_daftar'      => $faker->dateTimeBetween('-1 years', 'now')->format('Y-m-d'),
                'jenis_bayar'         => $jenisBayar,
                'agama'               => $religEnum[array_rand($religEnum)],
                'nama_asuransi'       => $namaAsuransi,

                'sync_status'         => 'pending',
                'last_sync_attempt_at' => null,
                'last_sync_response'  => null,

                'created_at'          => $now,
                'updated_at'          => $now,
            ];
        }

        DB::table('pasien')->insert($rows);
    }
}
