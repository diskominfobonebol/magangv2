<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePegawaiRequest extends FormRequest
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
        return [
            'nama' => 'required|string|max:255',
            'nip' => 'required|string|unique:pegawais,nip',
            'jabatan' => 'required|string',
            'kategori_pegawai' => 'required|in:ASN,P3K',
            'no_wa' => 'required|string|max:15',
            'password' => 'required|string|min:6',
            'jenis' => 'required|in:Kenaikan Pangkat,Berkala,Keduanya',
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
