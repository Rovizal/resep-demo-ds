@php
    $pasien = $erx->rawat?->pasien;
@endphp
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Resep {{ $pasien?->nama ?? '-' }}</title>
    <style>
        @page {
            margin: 12mm 10mm;
        }

        * {
            box-sizing: border-box
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10pt;
            line-height: 1.35;
            color: #111
        }

        .text-right {
            text-align: right
        }

        .text-center {
            text-align: center
        }

        .text-nowrap {
            white-space: nowrap
        }

        .brand {
            text-align: center;
            margin-bottom: 6px
        }

        .brand .name {
            font-weight: 700;
            font-size: 13pt;
            letter-spacing: .2px
        }

        .brand .addr {
            font-size: 9pt;
            color: #555;
            margin-top: 2px
        }

        .rule {
            border: 0;
            border-top: 1px solid #999;
            margin: 6px 0 0
        }

        .title {
            text-align: center;
            font-weight: 700;
            font-size: 12pt;
            margin: 8px 0 8px
        }

        .meta {
            width: 100%;
            margin: 8px 0 10px;
            font-size: 10pt;
        }

        .kv {
            width: 100%;
            border-collapse: collapse
        }

        .kv td {
            padding: 1.2mm 0;
            vertical-align: top
        }

        .kv .k {
            width: 32mm;
            color: #333;
            font-weight: 600
        }

        .kv .v {
            padding-left: 2mm;
            word-break: break-word
        }

        table.items {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 9pt
        }

        .items col.c-no {
            width: 7mm
        }

        .items col.c-name {
            width: auto
        }

        .items col.c-qty {
            width: 9mm
        }

        .items col.c-dose {
            width: 18mm
        }

        .items col.c-rule {
            width: 28mm
        }

        .items col.c-price {
            width: 16mm
        }

        .items col.c-sub {
            width: 20mm
        }

        .items thead th {
            background: #f1f3f5;
            color: #111;
            border: 1px solid #bfc5ca;
            padding: 4px 5px;
            font-weight: 600
        }

        .items tbody td {
            border: 1px solid #d4d8dc;
            padding: 4px 5px;
            vertical-align: top
        }

        .items tbody tr:nth-child(even) td {
            background: #fbfbfb
        }

        .items tfoot td {
            border: 1px solid #bfc5ca;
            border-top-width: 1.2px;
            font-weight: 700;
            padding: 5px;
            background: #f8f9fa
        }

        .med-name {
            font-weight: 600;
            word-break: break-word
        }

        .med-extra {
            color: #555
        }

        .note {
            margin-top: 8px;
            color: #444;
            font-size: 9pt
        }
    </style>
</head>

<body>

    <div class="brand">
        <div class="name">Rumah Sakit Delta Surya</div>
        <div class="addr">Jl. Pahlawan No 9, Telp (031) 8961272</div>
        <hr class="rule">
    </div>
    <div class="title">E-Resep</div>

    <div class="meta">
        <table class="kv">
            <tr>
                <td class="k">Pasien</td>
                <td class="v">: {{ $pasien?->nama ?? '-' }}</td>
            </tr>
            <tr>
                <td class="k">No. RM</td>
                <td class="v">: {{ $pasien?->no_rm ?? '-' }}</td>
            </tr>
            <tr>
                <td class="k">Tgl Cetak</td>
                <td class="v">: {{ now()->format('d/m/Y H:i') }}</td>
            </tr>
            <tr>
                <td class="k">Dokter</td>
                <td class="v">: {{ $erx->dokter?->name ?? '-' }}</td>
            </tr>
        </table>
    </div>

    <table class="items">
        <colgroup>
            <col class="c-no">
            <col class="c-name">
            <col class="c-qty">
            <col class="c-dose">
            <col class="c-rule">
            <col class="c-price">
            <col class="c-sub">
        </colgroup>
        <thead>
            <tr>
                <th class="text-center">#</th>
                <th>Nama Obat</th>
                <th class="text-center">Qty</th>
                <th>Dosis</th>
                <th>Aturan Pakai</th>
                <th class="text-right">Harga</th>
                <th class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($erx->items->sortBy('urutan') as $i => $it)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>
                        <div class="med-name">{{ $it->medicine_name ?? '-' }}</div>
                        @if (!empty($it->keterangan_obat))
                            <div class="med-extra">{{ $it->keterangan_obat }}</div>
                        @endif
                    </td>
                    <td class="text-center">{{ (int) $it->qty }}</td>
                    <td>{{ $it->dosis ?: '-' }}</td>
                    <td>{{ $it->aturan_pakai ?: '-' }}</td>
                    <td class="text-right text-nowrap">{{ number_format((int) $it->harga_satuan, 0, ',', '.') }}</td>
                    <td class="text-right text-nowrap">{{ number_format((int) $it->subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6" class="text-right">Total</td>
                <td class="text-right text-nowrap">{{ number_format((int) $erx->total_amount, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <p class="note">* Obat harap digunakan sesuai aturan dokter. Simpan pada tempat sejuk &amp; kering.</p>

</body>

</html>
