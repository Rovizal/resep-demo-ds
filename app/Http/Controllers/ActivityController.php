<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ActivityController extends Controller
{
    public function index()
    {
        return view('activity.index');
    }

    public function datatable(Request $request)
    {
        $q = ActivityLog::query()
            ->with('user')
            ->orderByDesc('id');

        return DataTables::eloquent($q)
            ->addColumn('when', fn($v) => optional($v->created_at)->format('d/m/Y H:i'))
            ->addColumn('user_name', fn($v) => $v->user->name ?? '—')
            ->addColumn('subject', function ($v) {
                $type = class_basename($v->subject_type ?? '');
                $id   = $v->subject_id ?? '-';
                return $type ? "{$type} #{$id}" : '-';
            })
            ->addColumn('changes_text', function ($v) {
                $raw = $v->changes;
                if (is_array($raw)) {
                    $raw = json_encode($raw, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                } elseif (is_object($raw)) {
                    $raw = json_encode((array)$raw, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                }
                $str = (string) $raw;
                if (mb_strlen($str) > 300) {
                    $str = mb_substr($str, 0, 300) . '…';
                }
                $str = e($str);
                return "<code class=\"small\">{$str}</code>";
            })
            ->rawColumns(['changes_text'])
            ->toJson();
    }
}
