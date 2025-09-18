@extends('layouts.base')

@push('title')
    Dashboard
@endpush

@section('content')
    <div class="row">
        <div class="col-md-12 mb-4">

            @if (Auth::user()->role == 'doctor')
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Cara Pakai (Dokter)</h5>
                    </div>
                    <div class="card-body">
                        <ol class="mb-3">
                            <li class="mb-2">
                                Buka <strong>Rawat Jalan</strong> ,klik <i>Daftar Pasien</i>, cari pasien lalu simpan.
                            </li>
                            <li class="mb-2">
                                Dari Table jika telah ada data, bisa klik <em>Lihat</em> pada pasien untuk masuk ke halaman detail.
                            </li>
                            <li class="mb-2">
                                Tab <strong>Asesmen</strong>: isi keluhan, vital sign, alergi, (opsional) unggah berkas, klik <em>Simpan Asesmen</em>.
                            </li>
                            <li class="mb-2">
                                Tab <strong>Diagnosa & Tindakan</strong>: pilih ICD-10 dan klik <em>Tambah Diagnosa</em>; cari tindakan lalu <em>Tambah</em>.
                            </li>
                            <li class="mb-2">
                                Tab <strong>E-Resep</strong>:
                                <ul class="mb-2">
                                    <li>Klik <em>Tambah Obat</em>, pilih obat, isi <em>Qty</em>, <em>Dosis</em>, dan <em>Aturan pakai</em>.</li>
                                    <li>Pastikan harga tampil. Jika tidak, sistem akan memberi pesan “Harga tidak ditemukan…”.</li>
                                    <li>Klik <em>Simpan DRAFT</em> untuk menyimpan sementara.</li>
                                    <li>Klik <em>Kirim ke Apoteker</em> untuk mengubah status ke <span class="badge bg-warning text-dark">SUBMITTED</span>.
                                        Setelah terkirim, resep <u>terkunci</u> untuk dokter.</li>
                                </ul>
                            </li>
                        </ol>

                        <div class="table-responsive">
                            <table class="table table-sm w-auto">
                                <tbody>
                                    <tr>
                                        <td><span class="badge bg-secondary">DRAFT</span></td>
                                        <td>Dokter (pemilik kunjungan)</td>
                                        <td>Tambah/ubah/hapus item resep.</td>
                                    </tr>
                                    <tr>
                                        <td><span class="badge bg-warning text-dark">SUBMITTED</span></td>
                                        <td>Apoteker</td>
                                        <td>Dokter tidak bisa mengubah resep.</td>
                                    </tr>
                                    <tr>
                                        <td><span class="badge bg-success">SERVED</span></td>
                                        <td>Apoteker (lanjut proses)</td>
                                        <td>Resep sudah dilayani, siap cetak.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="alert alert-info mt-3 mb-0">
                            Tip: harga obat mengikuti <em>tanggal_masuk</em> kunjungan.
                        </div>
                    </div>
                </div>
            @endif

            @if (Auth::user()->role == 'pharmacist')
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Cara Pakai (Apoteker)</h5>
                        <a href="{{ route('resep.index') }}" class="btn btn-sm btn-primary">Buka Daftar Resep</a>
                    </div>
                    <div class="card-body">
                        <ol class="mb-3">
                            <li class="mb-2">
                                Buka menu <a href="{{ route('resep.index') }}">E-Resep</a>. Resep masuk dari dokter berstatus
                                <span class="badge bg-warning text-dark">SUBMITTED</span>.
                            </li>
                            <li class="mb-2">
                                Klik <em>Lihat</em> untuk membuka detail. Verifikasi item, dosis, dan aturan pakai.
                            </li>
                            <li class="mb-2">
                                Setelah obat disiapkan, klik <em>Selesaikan</em> untuk menandai <span class="badge bg-success">SERVED</span>.
                            </li>
                            <li class="mb-2">
                                Klik <em>Cetak</em> untuk mencetak E-Resep (PDF).
                            </li>
                        </ol>

                        <div class="alert alert-secondary mb-0">
                            Catatan: resep yang sudah <strong>SERVED/PAID</strong> tidak dapat diedit. Jika ada koreksi, minta dokter membuat resep baru.
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>
@endsection
