<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\EResep;
use App\Models\Pasien;
use App\Models\Asesmen;
use App\Models\Diagnosis;
use App\Models\ResepObat;
use App\Models\ActivityLog;
use App\Models\RawatPasien;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Services\PriceService;
use App\Services\RsdApiService;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class RawatJalanController extends Controller
{
    public function index()
    {
        return view("rajal.index");
    }

    public function datatable(Request $r)
    {
        $q = RawatPasien::query()
            ->with('pasien')
            ->where('jenis_layanan', 'rajal')
            ->orderByDesc('tanggal_masuk');

        return DataTables::eloquent($q)
            ->addColumn('no_rm', fn($v) => $v->pasien->no_rm ?? '-')
            ->addColumn('nama',  fn($v) => $v->pasien->nama ?? '-')
            ->addColumn('jk', function ($v) {
                return $v->pasien?->jenis_kelamin === 'male'
                    ? 'Laki-laki'
                    : ($v->pasien?->jenis_kelamin === 'female' ? 'Perempuan' : '-');
            })
            ->addColumn('dob', fn($v) => optional($v->pasien->tanggal_lahir)->format('d/m/Y') ?: '-')
            ->addColumn('tgl_daftar', fn($v) => optional($v->tanggal_masuk)->format('d/m/Y H:i') ?: '-')
            ->addColumn('action', function ($v) {
                $url = route('rajal.detail') . '?uuid=' . urlencode($v->uuid);
                return '<a href="' . $url . '" class="btn btn-sm btn-primary"><i class="fa fa-eye"></i></a>';
            })
            ->rawColumns(['action'])
            ->toJson();
    }

    public function addRajal()
    {
        return view("rajal.add");
    }

    public function searchPasien(Request $r)
    {
        $term = trim($r->get('term', ''));
        $page = max(1, $r->get('page', 1));
        $per  = 10;

        $q = Pasien::query()
            ->when($term, function ($qq) use ($term) {
                $qq->where(function ($w) use ($term) {
                    $w->where('nama', 'like', "%{$term}%")
                        ->orWhere('no_rm', 'like', "%{$term}%")
                        ->orWhere('nik', 'like', "%{$term}%")
                        ->orWhere('nohp', 'like', "%{$term}%");
                });
            })
            ->whereDoesntHave('rawat', fn($qq) => $qq->where('status', 'aktif'));

        $total = (clone $q)->count();
        $rows  = $q->orderBy('nama')
            ->skip(($page - 1) * $per)
            ->take($per)
            ->get(['id', 'no_rm', 'nama', 'jenis_kelamin', 'tanggal_lahir', 'tanggal_daftar', 'nohp', 'alamat']);

        return response()->json([
            'results' => $rows->map(function ($p) {
                $jk = $p->jenis_kelamin === 'male' ? 'L' : ($p->jenis_kelamin === 'female' ? 'P' : '-');
                $dob = $p->tanggal_lahir ? Carbon::parse($p->tanggal_lahir)->format('d/m/Y') : '-';
                $tdf = $p->tanggal_daftar ? Carbon::parse($p->tanggal_daftar)->format('d/m/Y') : '-';
                return [
                    'id'               => $p->id,
                    'text'             => ($p->no_rm ?? '-') . ' • ' . $p->nama . " • {$jk} • {$dob}",
                    'no_rm'            => $p->no_rm,
                    'nama'             => $p->nama,
                    'jk'               => $jk,
                    'tanggal_lahir'    => $dob,
                    'tanggal_daftar'   => $tdf,
                    'nohp'             => $p->nohp,
                    'alamat'           => $p->alamat,
                ];
            })->all(),
            'pagination' => ['more' => ($page * $per) < $total],
        ]);
    }

    public function daftar(Request $r)
    {
        $data = $r->validate([
            'pasien_id' => ['required', 'exists:pasien,id'],
        ]);

        return DB::transaction(function () use ($data, $r) {
            $exists = RawatPasien::where('pasien_id', $data['pasien_id'])
                ->where('status', 'aktif')->exists();
            if ($exists) {
                return response()->json(['message' => 'Pasien sudah memiliki kunjungan aktif.'], 409);
            }

            $row = RawatPasien::create([
                'uuid'           =>  Str::uuid(),
                'pasien_id'      => $data['pasien_id'],
                'dokter_id'      => $r->user()->id,
                'poli_id'        => null,
                'jenis_layanan'  => 'rajal',
                'cara_masuk'     => 'datang_sendiri',
                'tanggal_masuk'  => now(),
                'status'         => 'aktif',
            ]);

            ActivityLog::record('rajal.created', $row, [
                'pasien_id'     => $row->pasien_id,
                'dokter_id'     => $row->dokter_id,
                'tanggal_masuk' => optional($row->tanggal_masuk)->toDateTimeString(),
                'status'        => $row->status,
            ], $r->user()?->id);

            return response()->json([
                'message' => 'Pendaftaran rawat jalan berhasil.',
                'data'    => ['id' => $row->id, 'uuid' => $row->uuid],
            ]);
        });
    }

    public function detail(Request $r)
    {
        $uuid = $r->query('uuid');
        abort_if(!$uuid, 404);

        $row = RawatPasien::with(['pasien'])
            ->where('uuid', $uuid)
            ->firstOrFail();

        return view('rajal.detail', compact('row'));
    }

    public function asesmenGet(Request $r)
    {
        $rawatId  =  $r->query('rawat_pasien_id');
        $pasienId =  $r->query('pasien_id');

        abort_if(!$rawatId, 400, 'rawat_pasien_id wajib.');

        $row = Asesmen::where('rawat_pasien_id', $rawatId)
            ->when($pasienId, fn($q) => $q->where('pasien_id', $pasienId))
            ->latest('id')
            ->first();

        if (!$row) {
            return response()->json(['data' => null]);
        }

        $data = $row->only([
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
        ]);

        $data['file_url'] = $row->file_url;

        return response()->json(['data' => $data]);
    }

    public function asesmenSave(Request $r)
    {
        $data = $r->validate([
            'pasien_id'         => ['required', 'exists:pasien,id'],
            'rawat_pasien_id'   => ['required', 'exists:rawat_pasien,id'],

            'file_asesmen'      => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:3072'],

            'keluhan_utama'                 => ['nullable', 'string'],
            'riwayat_penyakit_sekarang'     => ['nullable', 'string'],
            'riwayat_penyakit_dahulu'       => ['nullable', 'string'],
            'riwayat_penyakit_keluarga'     => ['nullable', 'string'],

            'suhu'               => ['nullable', 'numeric', 'between:30,45'],
            'tinggi_badan'       => ['nullable', 'integer', 'between:0,300'],
            'berat_badan'        => ['nullable', 'numeric', 'between:0,500'],
            'tanda_vital_nadi'   => ['nullable', 'integer', 'between:20,250'],
            'tanda_vital_rr'     => ['nullable', 'integer', 'between:5,80'],
            'tanda_vital_spo2'   => ['nullable', 'integer', 'between:50,100'],
            'tanda_vital_td'     => ['nullable', 'string', 'max:20'],

            'penurunan_berat_badan' => ['nullable', 'in:0,1'],
            'kurang_nafsu_makan'    => ['nullable', 'in:0,1'],
            'kondisi_gizi'          => ['nullable', 'in:0,1,2,3'],
            'level_nyeri'           => ['nullable', 'integer', 'between:0,10'],

            'susah_jalan'       => ['nullable', 'boolean'],
            'alat_bantu_jalan'  => ['nullable', 'boolean'],
            'menopang_duduk'    => ['nullable', 'boolean'],

            'alergi_makanan'    => ['nullable', 'string'],
            'alergi_obat'       => ['nullable', 'string'],
        ]);

        $data['susah_jalan']      = $r->boolean('susah_jalan');
        $data['alat_bantu_jalan'] = $r->boolean('alat_bantu_jalan');
        $data['menopang_duduk']   = $r->boolean('menopang_duduk');

        $ase = Asesmen::firstOrNew([
            'rawat_pasien_id' => $data['rawat_pasien_id'],
            'pasien_id'       => $data['pasien_id'],
        ]);

        if ($r->hasFile('file_asesmen')) {
            if ($ase->exists && $ase->file_asesmen) {
                Storage::disk('public')->delete($ase->file_asesmen);
            }
            $data['file_asesmen'] = $r->file('file_asesmen')->store('asesmen', 'public');
        } else {
            unset($data['file_asesmen']);
        }

        if (!$ase->exists) {
            $ase->uuid =  Str::uuid();
        }
        $ase->dokter_id  = $r->user()->id;
        $ase->created_by = $r->user()->name;

        $ase->fill($data)->save();

        ActivityLog::record('asesmen.upserted', $ase, [
            'is_update' => $ase->wasChanged() ? true : false,
            'file'      => (bool) $ase->file_asesmen,
        ], $r->user()?->id);

        return response()->json([
            'message'  => 'Asesmen tersimpan.',
            'id'       => $ase->id,
            'file_url' => $ase->file_url,
        ]);
    }

    public function icdSearch(Request $r)
    {
        $term = trim($r->get('q', ''));
        $page = max(1, $r->get('page', 1));
        $per  = 10;

        $q = DB::table('icds')->where('active', true)
            ->when($term, function ($qq) use ($term) {
                $qq->where(function ($w) use ($term) {
                    $w->where('icd10_code', 'like', "%{$term}%")
                        ->orWhere('icd10_id', 'like', "%{$term}%")
                        ->orWhere('icd10_en', 'like', "%{$term}%");
                });
            });

        $total = (clone $q)->count();

        $rows = $q->orderBy('icd10_code')
            ->skip(($page - 1) * $per)
            ->take($per)
            ->get(['icd10_code', 'icd10_id', 'icd10_en']);

        $results = $rows->map(function ($row) {
            $name = $row->icd10_id ?: $row->icd10_en;
            return [
                'id'   => $row->icd10_code,
                'text' => "{$row->icd10_code} • {$name}",
                'name' => $name,
            ];
        });

        return response()->json([
            'results'    => $results,
            'pagination' => ['more' => ($page * $per) < $total],
        ]);
    }

    public function diagnosisList(Request $r)
    {
        $uuid = $r->query('uuid');
        $rawat = RawatPasien::where('uuid', $uuid)->firstOrFail();

        $rows = Diagnosis::where('rawat_pasien_id', $rawat->id)
            ->orderByDesc('id')
            ->get(['id', 'kode', 'diagnosis', 'kode_penyerta', 'diagnosis_penyerta', 'keterangan']);

        return response()->json(['data' => $rows]);
    }

    public function diagnosisAdd(Request $r)
    {
        $data = $r->validate([
            'pasien_id'            => ['required', 'exists:pasien,id'],
            'rawat_pasien_id'      => ['required', 'exists:rawat_pasien,id'],
            'kode'                 => ['nullable', 'string', 'max:45'],
            'diagnosis'            => ['nullable', 'string', 'max:255'],
            'kode_penyerta'        => ['nullable', 'string', 'max:45'],
            'diagnosis_penyerta'   => ['nullable', 'string', 'max:255'],
            'keterangan'           => ['nullable', 'string', 'max:500'],
        ]);

        $row = Diagnosis::create(array_merge($data, [
            'uuid'   =>  Str::uuid(),
            'dokter' => $r->user()->name,
        ]));

        return response()->json(['message' => 'Diagnosa ditambahkan.', 'id' => $row->id]);
    }

    public function diagnosisDelete(Request $r)
    {
        $data = $r->validate([
            'id' => ['required', 'integer', 'exists:tbl_diagnosis,id'],
        ]);

        $row = Diagnosis::findOrFail($data['id']);

        $rawat = RawatPasien::findOrFail($row->rawat_pasien_id);
        if ($rawat->dokter_id !== $r->user()->id) {
            abort(403);
        }

        $row->delete();

        ActivityLog::record('diagnosis.deleted', $row, [
            'diagnosis' => $row->diagnosis,
            'kode'      => $row->kode,
        ], $r->user()?->id);

        return response()->json(['message' => 'Diagnosa dihapus.']);
    }

    public function tindakanSearch(Request $r)
    {
        $term = trim($r->get('q', ''));
        $page = max(1, $r->get('page', 1));
        $per  = 10;

        $q = DB::table('tindakan')
            ->when($term, function ($qq) use ($term) {
                $qq->where(function ($w) use ($term) {
                    $w->where('nama_tindakan', 'like', "%{$term}%")
                        ->orWhere('kode_internal', 'like', "%{$term}%");
                });
            })
            ->where('is_aktif', true);

        $total = (clone $q)->count();
        $rows  = $q->orderBy('nama_tindakan')
            ->skip(($page - 1) * $per)->take($per)
            ->get(['id', 'kode_internal', 'nama_tindakan']);

        return response()->json([
            'results' => $rows->map(fn($t) => [
                'id'   => $t->id,
                'text' => "{$t->kode_internal} • {$t->nama_tindakan}",
            ]),
            'pagination' => ['more' => ($page * $per) < $total],
        ]);
    }

    public function tindakanList(Request $r)
    {
        $uuid = $r->query('uuid');
        $rawat = RawatPasien::where('uuid', $uuid)->firstOrFail();

        $rows = DB::table('rawat_tindakan')
            ->join('tindakan', 'tindakan.id', '=', 'rawat_tindakan.tindakan_id')
            ->where('rawat_tindakan.rawat_pasien_id', $rawat->id)
            ->orderByDesc('rawat_tindakan.id')
            ->get([
                'rawat_tindakan.id',
                'tindakan.kode_internal',
                'tindakan.nama_tindakan',
                'rawat_tindakan.qty'
            ]);

        return response()->json(['data' => $rows]);
    }

    public function tindakanAdd(Request $r)
    {
        $data = $r->validate([
            'rawat_pasien_id' => ['required', 'exists:rawat_pasien,id'],
            'tindakan_id'     => ['required', 'exists:tindakan,id'],
            'qty'             => ['required', 'integer', 'min:1'],
        ]);

        DB::table('rawat_tindakan')->insert([
            'rawat_pasien_id' => $data['rawat_pasien_id'],
            'tindakan_id'     => $data['tindakan_id'],
            'qty'             => $data['qty'],
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        return response()->json(['message' => 'Tindakan ditambahkan.']);
    }

    public function tindakanDelete(Request $r)
    {
        $data = $r->validate([
            'id' => ['required', 'integer'],
        ]);

        $row = DB::table('rawat_tindakan')->where('id', $data['id'])->first();
        if (!$row) {
            return response()->json(['message' => 'Data tidak ditemukan.'], 404);
        }

        $rawat = RawatPasien::findOrFail($row->rawat_pasien_id);
        if ($rawat->dokter_id !== $r->user()->id) {
            abort(403);
        }

        DB::table('rawat_tindakan')->where('id', $data['id'])->delete();

        ActivityLog::record('tindakan.deleted', $rawat, [
            'rawat_tindakan_id' => $data['id'],
        ], $r->user()?->id);

        return response()->json(['message' => 'Tindakan dihapus.']);
    }

    public function eresepList(Request $request)
    {
        $rawatId = $request->input('rawat_pasien_id');

        $rx = EResep::where('rawat_pasien_id', $rawatId)
            ->latest('id')->first();

        if (!$rx) {
            return response()->json(['data' => [], 'meta' => [
                'status'      => 'DRAFT',
                'editable'    => true,
                'can_submit'  => false,
                'total'       => 0,
                'header_uuid' => null,
            ]]);
        }

        $rows = $rx->items()->where('is_racikan', false)
            ->orderBy('urutan')->orderBy('id')
            ->get(['medicine_id', 'medicine_name', 'qty', 'dosis', 'aturan_pakai']);

        $data = $rows->map(fn($r) => [
            'medicine_id'   => $r->medicine_id,
            'medicine_name' => $r->medicine_name ?? '',
            'qty'           => $r->qty,
            'dosis'         => $r->dosis ?? '',
            'aturan_pakai'  => $r->aturan_pakai ?? '',
        ])->values();

        $editable   = ($rx->status === EResep::STATUS_DRAFT)
            && (auth()->id() === optional($rx->rawat)->dokter_id);
        $canSubmit  = $editable && $rows->count() > 0;

        return response()->json([
            'data' => $data,
            'meta' => [
                'status'      => $rx->status,
                'editable'    => $editable,
                'can_submit'  => $canSubmit,
                'total'       => $rx->total_amount,
                'header_uuid' => $rx->uuid,
            ],
        ]);
    }

    public function eresepSave(Request $r, PriceService $priceSvc)
    {
        $payload = $r->validate([
            'rawat_pasien_id' => ['required', 'exists:rawat_pasien,id'],
            'items'           => ['required', 'string'],
        ]);

        $rawat = RawatPasien::with('pasien')->findOrFail($payload['rawat_pasien_id']);
        if ($rawat->dokter_id !== $r->user()->id) abort(403);

        $examAt = Carbon::parse($rawat->tanggal_masuk);
        $items  = json_decode($payload['items'], true) ?? [];
        if (empty($items)) return response()->json(['message' => 'Minimal 1 obat.'], 422);

        $computed = [];
        $missing  = [];

        foreach ($items as $row) {
            $mid   = $row['medicine_id']   ?? null;
            $name  = $row['medicine_name'] ?? null;
            $qty   = ($row['qty']    ?? 0);
            $dose  = trim($row['dosis'] ?? '');
            $rules = trim($row['aturan_pakai'] ?? '');
            if (!$mid || !$name || $qty < 1) continue;

            $unit = $priceSvc->priceAt($mid, $examAt);
            if (is_null($unit)) {
                $missing[] = $name;
                continue;
            }

            $computed[] = [
                'mid'   => $mid,
                'name'  => $name,
                'qty'   => $qty,
                'unit'  => $unit,
                'sub'   => $unit * $qty,
                'dosis' => $dose,
                'rule'  => $rules,
            ];
        }

        if (!empty($missing)) {
            return response()->json(['message' => 'Harga tidak ditemukan untuk: ' . implode(', ', $missing)], 422);
        }
        if (empty($computed)) return response()->json(['message' => 'Tidak ada item valid.'], 422);

        return DB::transaction(function () use ($rawat, $computed, $r) {
            $header = EResep::where('rawat_pasien_id', $rawat->id)->latest('id')->first();

            if ($header && $header->status !== EResep::STATUS_DRAFT) {
                return response()->json(['message' => 'Resep sudah dikirim/diproses. Tidak bisa diubah.'], 409);
            }

            if (!$header) {
                $header = EResep::create([
                    'uuid'             => Str::uuid(),
                    'rawat_pasien_id'  => $rawat->id,
                    'dokter_id'        => $r->user()->id,
                    'status'           => EResep::STATUS_DRAFT,
                    'total_amount'     => 0,
                ]);
            } else {
                $header->items()->delete();
            }

            $total  = 0;
            $urutan = 1;
            foreach ($computed as $c) {
                $total += $c['sub'];
                ResepObat::create([
                    'uuid'           => Str::uuid(),
                    'e_resep_id'     => $header->id,
                    'is_racikan'     => false,
                    'medicine_id'    => $c['mid'],
                    'medicine_name'  => $c['name'],
                    'qty'            => $c['qty'],
                    'dosis'          => $c['dosis'] ?: null,
                    'aturan_pakai'   => $c['rule'] ?: null,
                    'harga_satuan'   => $c['unit'],
                    'subtotal'       => $c['sub'],
                    'urutan'         => $urutan++,
                ]);
            }

            $header->update(['total_amount' => $total]);
            ActivityLog::record('eresep.saved', $header, ['total' => $total], $r->user()->id);

            return response()->json(['message' => 'Resep disimpan (DRAFT).', 'total' => $total]);
        });
    }

    public function eresepSubmit(Request $r)
    {
        $data = $r->validate([
            'rawat_pasien_id' => ['required', 'exists:rawat_pasien,id'],
        ]);

        $rawat = RawatPasien::findOrFail($data['rawat_pasien_id']);
        if ($rawat->dokter_id !== $r->user()->id) abort(403);

        $header = EResep::withCount('items')
            ->where('rawat_pasien_id', $rawat->id)
            ->latest('id')
            ->first();

        if (!$header || $header->items_count === 0) {
            return response()->json(['message' => 'Tambahkan minimal 1 obat sebelum mengirim.'], 422);
        }
        if ($header->status !== EResep::STATUS_DRAFT) {
            return response()->json(['message' => 'Resep sudah dikirim/diproses.'], 409);
        }

        $header->status = EResep::STATUS_SUBMITTED;
        $header->save();
        $header->recalcTotal();

        ActivityLog::record('eresep.submitted', $header, ['e_resep_id' => $header->id], $r->user()->id);

        return response()->json([
            'message' => 'Resep dikirim ke Apoteker.',
            'status'  => $header->status,
        ]);
    }

    public function apiMedicines(Request $r, RsdApiService $api)
    {
        $q = trim($r->query('q', ''));
        $list = collect($api->listMedicines());
        if ($q !== '') {
            $list = $list->filter(fn($m) => str_contains(strtolower($m['name']), strtolower($q)));
        }
        return $list->take(20)->values()->all();
    }

    public function apiMedicinePriceAt(string $id, Request $r, PriceService $priceSvc)
    {
        $at = $r->query('date');
        $date = $at ? Carbon::parse($at) : now();
        $price = $priceSvc->priceAt($id, $date);
        return $price ? ['unit_price' => $price] : response()->json([], 404);
    }
}
