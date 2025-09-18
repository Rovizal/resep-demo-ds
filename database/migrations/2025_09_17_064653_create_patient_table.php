<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pasien', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('no_rm')->unique();
            $table->string('panggilan')->nullable();
            $table->string('nama');
            $table->string('satusehat_id')->nullable();
            $table->string('nik')->nullable();
            $table->string('tmp_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->enum('jenis_kelamin', ['male', 'female', 'other', 'unknown']);
            $table->enum('golongan_darah', ['unknown', 'A', 'B', 'AB', 'O']);
            $table->text('alamat')->nullable();
            $table->string('nohp')->nullable();
            $table->string('telepon')->nullable();
            $table->string('status_satusehat')->nullable();
            $table->string('id_wilayah')->nullable();

            $table->string('email')->nullable(); // telecom: email
            $table->enum('status_hidup', ['hidup', 'meninggal'])->default('hidup');
            $table->date('tanggal_meninggal')->nullable();

            // Untuk contact person
            $table->string('nama_kontak_darurat')->nullable();
            $table->string('hubungan_kontak')->nullable();
            $table->string('telepon_kontak')->nullable();

            $table->enum('status_menikah', ['belum_menikah', 'menikah', 'cerai'])->nullable();
            $table->string('pendidikan')->nullable();
            $table->string('pekerjaan')->nullable();
            $table->string('no_asuransi')->nullable();
            $table->date('tanggal_daftar')->nullable();
            $table->enum('jenis_bayar', ['bpjs', 'umum', 'asuransi_lain'])->nullable();
            $table->enum('agama', ['islam', 'kristen', 'protestan', 'hindu', 'budha', 'konghucu'])->nullable();
            $table->string('nama_asuransi')->nullable();
            $table->timestamps();

            $table->enum('sync_status', ['pending', 'processing', 'success', 'failed'])->default('pending');
            $table->timestamp('last_sync_attempt_at')->nullable();
            $table->text('last_sync_response')->nullable();
        });

        Schema::create('rawat_pasien', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->unsignedBigInteger('pasien_id');
            $table->unsignedBigInteger('dokter_id');
            $table->unsignedBigInteger('poli_id')->nullable();

            $table->enum('jenis_layanan', ['ranap', 'rajal', 'observasi']);

            $table->enum('cara_masuk', [
                'datang_sendiri',
                'diantar',
                'kasus_polisi',
                'kecelakaan',
                'dokter_lain',
                'instalasi_lain'
            ])->nullable();

            $table->string('referensi_masuk_dari')->nullable();

            $table->enum('jenis_bayar', ['bpjs', 'umum', 'asuransi_lain'])->nullable();
            $table->string('asuransi')->nullable();

            $table->enum('cara_keluar', ['ijin_dokter', 'pulang_paksa', 'meninggal_dunia'])->nullable();

            $table->dateTime('tanggal_masuk');
            $table->dateTime('tanggal_keluar')->nullable();

            $table->enum('status', ['aktif', 'pindah', 'selesai'])->default('aktif');
            $table->string('satusehat_id')->nullable();

            $table->timestamps();
        });

        Schema::create('e_resep', function (Blueprint $t) {
            $t->id();
            $t->uuid('uuid')->unique();
            $t->unsignedBigInteger('rawat_pasien_id');
            $t->unsignedBigInteger('dokter_id');
            $t->unsignedBigInteger('apoteker_id')->nullable();

            $t->enum('status', ['DRAFT', 'SUBMITTED', 'SERVED', 'PAID'])->default('DRAFT');
            $t->integer('total_amount')->default(0);
            $t->timestamps();

            $t->foreign('rawat_pasien_id')->references('id')->on('rawat_pasien')->cascadeOnDelete();
            $t->foreign('dokter_id')->references('id')->on('users')->restrictOnDelete();
            $t->foreign('apoteker_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('resep_obat', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->index();
            $table->unsignedBigInteger('e_resep_id');
            $table->uuid('medicine_id')->nullable();
            $table->string('medicine_name')->nullable();
            $table->boolean('is_racikan')->default(false);
            $table->unsignedBigInteger('racikan_instance_id')->nullable();

            $table->integer('qty');
            $table->string('dosis')->nullable();
            $table->string('aturan_pakai')->nullable();
            $table->integer('harga_satuan')->default(0);
            $table->integer('subtotal')->default(0);

            $table->unsignedInteger('urutan')->default(0);
            $table->timestamps();

            $table->foreign('e_resep_id')->references('id')->on('e_resep')->cascadeOnDelete();
        });

        Schema::create('icds', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('icd10_code');
            $table->longText('icd10_en');
            $table->longText('icd10_id')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('tbl_diagnosis', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('pasien_id');
            $table->unsignedBigInteger('rawat_pasien_id')->nullable();
            $table->string('kode', 45)->nullable();
            $table->string('diagnosis', 255)->nullable();
            $table->string('kode_penyerta', 45)->nullable();
            $table->string('diagnosis_penyerta', 255)->nullable();
            $table->text('keterangan')->nullable();
            $table->string('dokter', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        Schema::create('tbl_asesmen', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->unsignedBigInteger('pasien_id');
            $table->unsignedBigInteger('rawat_pasien_id');

            $table->string('file_asesmen')->nullable();
            $table->text('keluhan_utama')->nullable();
            $table->text('riwayat_penyakit_sekarang')->nullable();
            $table->text('riwayat_penyakit_dahulu')->nullable();

            $table->text('riwayat_penyakit_keluarga')->nullable();

            $table->integer('suhu')->nullable();
            $table->integer('tinggi_badan')->nullable();
            $table->integer('berat_badan')->nullable();
            $table->integer('tanda_vital_nadi')->nullable();
            $table->string('tanda_vital_td')->nullable();
            $table->integer('tanda_vital_rr')->nullable();
            $table->integer('tanda_vital_spo2')->nullable();

            $table->enum('penurunan_berat_badan', ['0', '1'])->nullable();
            $table->enum('kurang_nafsu_makan', ['0', '1'])->nullable();
            $table->enum('kondisi_gizi', ['0', '1', '2', '3'])->nullable()->comment('0=baik,1=lebih,2=kurang,3=buruk');

            $table->tinyInteger('level_nyeri')->nullable();
            $table->boolean('susah_jalan')->nullable();
            $table->boolean('alat_bantu_jalan')->nullable();
            $table->boolean('menopang_duduk')->nullable();

            $table->text('alergi_makanan')->nullable();
            $table->text('alergi_obat')->nullable();

            $table->unsignedBigInteger('dokter_id')->nullable();
            $table->unsignedBigInteger('perawat_id')->nullable();

            $table->timestamps();
            $table->string('created_by')->nullable();
        });

        Schema::create('tindakan', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->string('kode_internal')->unique();
            $table->string('nama_tindakan');

            $table->integer('tarif')->default(0);
            $table->integer('fee_dokter')->default(0);
            $table->integer('fee_perawat')->default(0);
            $table->integer('fee_klinik')->default(0);
            $table->integer('bhp')->default(0);

            $table->enum('jenis', ['rawat_jalan', 'rawat_inap', 'gigi', 'laboratorium', 'umum'])->nullable();

            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('rawat_tindakan', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('rawat_pasien_id');
            $t->unsignedBigInteger('tindakan_id');
            $t->unsignedInteger('qty')->default(1);
            $t->timestamps();

            $t->foreign('rawat_pasien_id')->references('id')->on('rawat_pasien')->cascadeOnDelete();
            $t->foreign('tindakan_id')->references('id')->on('tindakan')->restrictOnDelete();
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->morphs('subject');
            $table->json('changes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_asesmen');
        Schema::dropIfExists('tbl_diagnosis');
        Schema::dropIfExists('icds');
        Schema::dropIfExists('resep_obat');
        Schema::dropIfExists('e_resep');
        Schema::dropIfExists('rawat_pasien');
        Schema::dropIfExists('pasien');
        Schema::dropIfExists('tindakan');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('rawat_tindakan');
    }
};
