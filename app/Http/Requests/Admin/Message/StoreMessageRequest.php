<?php

namespace App\Http\Requests\Admin\Message;

use Illuminate\Foundation\Http\FormRequest;

class StoreMessageRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'unit_id' => 'required|exists:units,id',
            'judul' => 'required|string|max:200',
            'pesan' => 'required|string|min:10|max:5000',
            'tipe' => 'required|in:saran,instruksi,pengumuman,lainnya',
            'prioritas' => 'required|in:biasa,penting,sangat_penting',
        ];
    }

    public function messages()
    {
        return [
            'unit_id.required' => 'Unit tujuan wajib dipilih',
            'unit_id.exists' => 'Unit tidak ditemukan',
            'judul.required' => 'Judul pesan wajib diisi',
            'judul.max' => 'Judul maksimal 200 karakter',
            'pesan.required' => 'Isi pesan wajib diisi',
            'pesan.min' => 'Isi pesan minimal 10 karakter',
            'pesan.max' => 'Isi pesan maksimal 5000 karakter',
            'tipe.required' => 'Tipe pesan wajib dipilih',
            'tipe.in' => 'Tipe pesan tidak valid',
            'prioritas.required' => 'Prioritas pesan wajib dipilih',
            'prioritas.in' => 'Prioritas pesan tidak valid',
        ];
    }
}