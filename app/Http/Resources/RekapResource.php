<?php

namespace App\Http\Resources;
 
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
 
class RekapResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                   => $this->id,
            'tahun'                => $this->tahun,
            'tanggal_tutup'        => $this->tanggal_tutup?->format('d-m-Y'),
            'grand_total_debet'    => $this->grand_total_debet,
            'grand_total_debet_format'  => 'Rp ' . number_format($this->grand_total_debet, 0, ',', '.'),
            'grand_total_kredit'   => $this->grand_total_kredit,
            'grand_total_kredit_format' => 'Rp ' . number_format($this->grand_total_kredit, 0, ',', '.'),
            'saldo_akhir'          => $this->saldo_akhir,
            'saldo_akhir_format'   => 'Rp ' . number_format($this->saldo_akhir, 0, ',', '.'),
            'ketua_pengurus'       => $this->ketua_pengurus,
            'sekretaris'           => $this->sekretaris,
            'bendahara'            => $this->bendahara,
            'ketua_badan_pengawas' => $this->ketua_badan_pengawas,
            'lokasi'               => $this->lokasi,
            'created_at'           => $this->created_at?->toDateTimeString(),
            'updated_at'           => $this->updated_at?->toDateTimeString(),
        ];
    }
}