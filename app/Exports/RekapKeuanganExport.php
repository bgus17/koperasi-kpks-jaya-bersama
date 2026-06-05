<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class RekapKeuanganExport implements FromView, ShouldAutoSize, WithTitle
{
    public function __construct(private array $context)
    {
    }

    public function view(): View
    {
        return view('rekap.excel', $this->context);
    }

    public function title(): string
    {
        $periode = $this->context['laporan']['periode'];

        return $periode['bulan']
            ? 'Rekap ' . str_pad((string) $periode['bulan'], 2, '0', STR_PAD_LEFT) . '-' . $periode['tahun']
            : 'Rekap ' . $periode['tahun'];
    }
}
