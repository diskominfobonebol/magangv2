<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSuratMasukRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Hanya Admin Kasubag (role_id = 2) yang berhak menambah Surat Masuk
        return auth()->check() && (int) auth()->user()->role_id === 2;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nomor_surat' => 'required|string|max:255',
            'tanggal_surat' => 'required|date',
            'asal_surat' => 'required|string|max:255',
            'uraian' => 'required|string|max:200',
            'keterangan' => 'nullable|string|max:150',
            'link_google_drive' => [
                'required',
                'url',
                'regex:/^(https?:\/\/)?([\w-]+\.)*drive\.google\.com\/.+$/i'
            ],
        ];
    }

    /**
     * Custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'nomor_surat.required' => 'Nomor surat wajib diisi.',
            'nomor_surat.max' => 'Nomor surat maksimal 255 karakter.',
            'tanggal_surat.required' => 'Tanggal surat wajib diisi.',
            'tanggal_surat.date' => 'Format tanggal surat tidak valid.',
            'asal_surat.required' => 'Asal surat / instansi pengirim wajib diisi.',
            'asal_surat.max' => 'Asal surat maksimal 255 karakter.',
            'uraian.required' => 'Uraian / perihal surat wajib diisi.',
            'uraian.max' => 'Uraian surat maksimal 200 karakter.',
            'keterangan.max' => 'Keterangan tambahan maksimal 150 karakter.',
            'link_google_drive.required' => 'Link Google Drive bukti surat fisik wajib diisi.',
            'link_google_drive.url' => 'Link Google Drive harus berupa format URL yang valid (diawali https:// atau http://).',
            'link_google_drive.regex' => 'Link harus berupa tautan Google Drive yang valid (contoh: https://drive.google.com/...).',
        ];
    }
}
