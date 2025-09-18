<?php

namespace App\Http\Controllers;

use App\Models\EResep;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Mpdf\Mpdf;
use App\Models\ActivityLog;

class ResepController extends Controller
{
    public function index()
    {
        return view("resep.index");
    }

    public function datatable(Request $r)
    {
        $q = EResep::query()
            ->with(['rawat.pasien', 'dokter'])
            ->whereIn('status', [EResep::STATUS_DRAFT, EResep::STATUS_SUBMITTED])
            ->orderByDesc('id');

        return DataTables::eloquent($q)
            ->addColumn('tanggal', fn($v) => optional($v->created_at)->format('d/m/Y H:i'))
            ->addColumn('no_rm', fn($v) => $v->rawat?->pasien?->no_rm ?? '-')
            ->addColumn('pasien', fn($v) => $v->rawat?->pasien?->nama ?? '-')
            ->addColumn('dokter', fn($v) => $v->dokter?->name ?? '-')
            ->addColumn('total',  fn($v) => number_format((int)$v->total_amount, 0, ',', '.'))
            ->addColumn('status', fn($v) => $v->status)
            ->addColumn('action', function ($v) {
                $url = route('resep.show', $v->uuid);
                return '<a href="' . $url . '" class="btn btn-sm btn-primary">
                            <i class="fa fa-eye"></i> Lihat
                        </a>';
            })
            ->rawColumns(['action'])
            ->toJson();
    }

    public function show(string $uuid)
    {
        $erx = EResep::with(['items', 'rawat.pasien', 'dokter', 'apoteker'])
            ->where('uuid', $uuid)->firstOrFail();

        return view('resep.show', compact('erx'));
    }

    public function serve(string $uuid, Request $r)
    {
        $erx = EResep::where('uuid', $uuid)->firstOrFail();

        if (!in_array($erx->status, [EResep::STATUS_DRAFT, EResep::STATUS_SUBMITTED])) {
            return response()->json(['message' => 'Status tidak valid untuk diselesaikan.'], 422);
        }

        $erx->update([
            'status'       => EResep::STATUS_SERVED,
            'apoteker_id'  => $r->user()->id,
        ]);

        ActivityLog::record('eresep.served', $erx, ['e_resep_id' => $erx->id], $r->user()->id);

        return response()->json(['message' => 'Resep diselesaikan (SERVED).']);
    }

    public function print(string $uuid)
    {
        $erx = EResep::with(['items', 'rawat.pasien', 'dokter'])
            ->where('uuid', $uuid)->firstOrFail();

        $html = view('resep.print', compact('erx'))->render();

        $mpdf = new Mpdf(['mode' => 'utf-8', 'format' => 'A5-P']);
        $mpdf->SetTitle('Resep - ' . $erx->uuid);
        $mpdf->WriteHTML($html);

        return response($mpdf->Output('', 'S'), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="resep-' . $erx->uuid . '.pdf"',
        ]);
    }
}
