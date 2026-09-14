<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePegawaiRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $pegawaiId = $this->route('id');

        return [
            'nama' => 'required|string|max:255',
            'nip' => 'required|string|unique:pegawais,nip,' . $pegawaiId,
            'jabatan' => 'required|string',
            'kategori_pegawai' => 'required|in:ASN,P3K',
            'no_wa' => 'required|string|max:15',
            'jenis' => 'required|in:Kenaikan Pangkat,Berkala,Keduanya',
            'tgl_terakhir' => 'nullable|date',
            'status' => 'required|in:Aktif,Tidak Aktif',
            'progres_berkas' => 'nullable|integer|min:0|max:100',
            'status_acc' => 'required|in:Menunggu,Disetujui,Ditolak',
            'pangkat_golongan' => 'nullable|string',
            'keterangan' => 'nullable|string',
        ];
    }

    /**
     * Custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'kategori_pegawai.required' => 'Kategori pegawai wajib dipilih (ASN atau P3K).',
            'kategori_pegawai.in' => 'Kategori pegawai harus berupa ASN atau P3K.',
            'nip.unique' => 'NIP sudah terdaftar di sistem.',
        ];
    }
}
