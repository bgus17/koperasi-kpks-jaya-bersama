<?php

namespace App\Http\Requests;
 
use Illuminate\Foundation\Http\FormRequest;
 
class PendapatanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $kategori = trim((string) $this->input('kategori'));
        $tanggal = (string) $this->input('tanggal');
        $timestamp = $tanggal !== '' ? strtotime($tanggal) : false;
        $tahun = $this->input('tahun') ?: ($timestamp ? date('Y', $timestamp) : now()->year);

        $this->merge([
            'nomor_kategori' => $this->filled('nomor_kategori')
                ? trim((string) $this->input('nomor_kategori'))
                : 'I',
            'kategori' => $kategori,
            'sub_kategori' => $this->filled('sub_kategori')
                ? trim((string) $this->input('sub_kategori'))
                : $kategori,
            'debet' => $this->cleanMoney($this->input('debet')),
            'kredit' => $this->cleanMoney($this->input('kredit')),
            'saldo' => $this->cleanMoney($this->input('saldo')),
            'tahun' => (int) $tahun,
        ]);
    }
 
    public function rules(): array
    {
        return [
            'nomor_kategori' => 'required|string|max:10',
            'kategori'       => 'required|string|max:100',
            'sub_kategori'   => 'required|string|max:200',
            'debet'          => 'nullable|integer|min:0',
            'kredit'         => 'nullable|integer|min:0',
            'saldo'          => 'nullable|integer|min:0',
            'tahun'          => 'required|integer|min:2000|max:2100',
            'tanggal'        => 'required|date',
            'keterangan'     => 'nullable|string',
        ];
    }
 
    public function messages(): array
    {
        return [
            'nomor_kategori.required' => 'Nomor kategori wajib diisi.',
            'kategori.required'       => 'Kategori wajib diisi.',
            'sub_kategori.required'   => 'Sub kategori wajib diisi.',
            'debet.required'          => 'Nilai debet wajib diisi.',
            'debet.integer'           => 'Nilai debet harus berupa angka.',
            'kredit.required'         => 'Nilai kredit wajib diisi.',
            'kredit.integer'          => 'Nilai kredit harus berupa angka.',
            'saldo.required'          => 'Saldo wajib diisi.',
            'tahun.required'          => 'Tahun wajib diisi.',
            'tanggal.required'        => 'Tanggal wajib diisi.',
            'tanggal.date'            => 'Format tanggal tidak valid.',
        ];
    }

    private function cleanMoney(mixed $value): int
    {
        return (int) preg_replace('/\D/', '', (string) $value);
    }
}
