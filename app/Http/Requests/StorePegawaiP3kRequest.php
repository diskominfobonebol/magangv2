<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePegawaiP3kRequest extends FormRequest
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
            'nip' => 'required|string|max:50|unique:pegawais,nip',
            'jabatan' => 'required|string|max:255',
        ];
    }

    /**
     * Custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'nama.required' => 'Nama lengkap pegawai wajib diisi.',
            'nip.required' => 'Nomor Identitas P3K wajib diisi.',
            'nip.unique' => 'Nomor Identitas P3K sudah terdaftar di sistem.',
            'jabatan.required' => 'Jabatan pegawai wajib diisi.',
        ];
    }
}
