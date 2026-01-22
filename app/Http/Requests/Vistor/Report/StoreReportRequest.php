<?php

namespace App\Http\Requests\Visitor;

use Illuminate\Foundation\Http\FormRequest;

class StoreReportRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'unit_id' => 'nullable|exists:units,id',
            'judul' => 'required|string|max:200',
            'deskripsi' => 'required|string|min:10|max:2000',
            'tipe' => 'required|in:masalah,saran,keluhan,pujian,lainnya',
            'prioritas' => 'required|in:rendah,sedang,tinggi,kritis',
            'lampiran' => 'nullable|array',
            'lampiran.*' => 'nullable|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'unit_id.exists' => 'Unit tidak ditemukan',
            'judul.required' => 'Judul laporan wajib diisi',
            'judul.max' => 'Judul maksimal 200 karakter',
            'deskripsi.required' => 'Deskripsi laporan wajib diisi',
            'deskripsi.min' => 'Deskripsi minimal 10 karakter',
            'deskripsi.max' => 'Deskripsi maksimal 2000 karakter',
            'tipe.required' => 'Tipe laporan wajib dipilih',
            'tipe.in' => 'Tipe laporan tidak valid',
            'prioritas.required' => 'Prioritas wajib dipilih',
            'prioritas.in' => 'Prioritas tidak valid',
            'lampiran.array' => 'Lampiran harus dalam format array',
            'lampiran.*.max' => 'Nama file lampiran maksimal 255 karakter',
        ];
    }
}