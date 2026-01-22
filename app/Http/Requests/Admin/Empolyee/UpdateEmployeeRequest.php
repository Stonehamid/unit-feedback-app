<?php

namespace App\Http\Requests\Admin\Empolyee;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'nama' => 'sometimes|string|max:100',
            'jabatan' => 'sometimes|string|max:100',
            'bidang' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:100',
            'telepon' => 'nullable|string|max:20',
            'status' => 'sometimes|in:aktif,cuti,resign',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after:tanggal_mulai',
            'keterangan' => 'nullable|string|max:500',
        ];
    }

    public function messages()
    {
        return [
            'nama.max' => 'Nama maksimal 100 karakter',
            'jabatan.max' => 'Jabatan maksimal 100 karakter',
            'bidang.max' => 'Bidang maksimal 100 karakter',
            'email.email' => 'Format email tidak valid',
            'email.max' => 'Email maksimal 100 karakter',
            'telepon.max' => 'Nomor telepon maksimal 20 karakter',
            'status.in' => 'Status pekerja tidak valid',
            'tanggal_mulai.date' => 'Format tanggal mulai tidak valid',
            'tanggal_selesai.date' => 'Format tanggal selesai tidak valid',
            'tanggal_selesai.after' => 'Tanggal selesai harus setelah tanggal mulai',
            'keterangan.max' => 'Keterangan maksimal 500 karakter',
        ];
    }
}