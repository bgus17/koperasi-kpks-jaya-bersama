<?php
// ============================================================
// FILE: app/Http/Resources/PendapatanResource.php
// ============================================================
 
namespace App\Http\Resources;
 
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
 
class PendapatanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'kategori'       => $this->kategori,
            'debet'          => $this->debet,
            'debet_format'   => 'Rp ' . number_format($this->debet, 0, ',', '.'),
            'kredit'         => $this->kredit,
            'kredit_format'  => 'Rp ' . number_format($this->kredit, 0, ',', '.'),
            'saldo'          => $this->saldo,
            'saldo_format'   => 'Rp ' . number_format($this->saldo, 0, ',', '.'),
            'tanggal'        => $this->tanggal?->format('d-m-Y'),
            'keterangan'     => $this->keterangan,
            'created_at'     => $this->created_at?->toDateTimeString(),
            'updated_at'     => $this->updated_at?->toDateTimeString(),
        ];
    }
}
 