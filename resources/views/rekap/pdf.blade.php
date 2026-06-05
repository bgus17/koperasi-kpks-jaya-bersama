<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Keuangan {{ $laporan['periode']['label'] }}</title>
    <style>
        @page { margin: 24px 22px; }
        body {
            color: #111827;
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
        }
        h1, h2 {
            margin: 0;
            text-align: center;
            text-transform: uppercase;
        }
        h1 { font-size: 14px; }
        h2 { margin-top: 4px; font-size: 12px; font-weight: normal; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 14px;
        }
        th, td {
            padding: 5px 6px;
            border: 1px solid #333;
            vertical-align: middle;
        }
        th {
            background: #e5e7eb;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .rp { white-space: nowrap; }
        .ledger-section-row td {
            background: #f3f4f6;
            font-weight: bold;
            text-transform: uppercase;
        }
        .ledger-total-row td,
        .ledger-balance-row td {
            font-weight: bold;
        }
        .ledger-balance-row td:last-child {
            background: #e5e7eb;
        }
        .ledger-empty { color: #6b7280; font-style: italic; }
        .signatures {
            width: 100%;
            margin-top: 26px;
            page-break-inside: avoid;
        }
        .signatures td {
            width: 50%;
            border: 0;
            text-align: center;
            vertical-align: top;
        }
        .role {
            margin-bottom: 52px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .name {
            min-height: 14px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <h1>{{ $laporan['periode']['judul'] }}</h1>
    <h2>{{ $laporan['periode']['subjudul'] }}</h2>

    @include('rekap.partials.ledger-table', ['laporan' => $laporan])

    <table class="signatures">
        <tr>
            <td>
                <div class="role">Badan Pengawas</div>
                <div class="name">{{ $rekap->ketua_badan_pengawas ?? 'Ketua' }}</div>
            </td>
            <td>
                <div>{{ $rekap->lokasi ?? 'Cahaya Mulya' }}, {{ $laporan['periode']['tanggal_tutup']->translatedFormat('d F Y') }}</div>
                <div class="role">Pengurus</div>
                <div class="name">{{ $rekap->ketua_pengurus ?? 'Ketua' }}</div>
                <div>{{ $rekap->sekretaris ?? 'Sekretaris' }} / {{ $rekap->bendahara ?? 'Bendahara' }}</div>
            </td>
        </tr>
    </table>
</body>
</html>
