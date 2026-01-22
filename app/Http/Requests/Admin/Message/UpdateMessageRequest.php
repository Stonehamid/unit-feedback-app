<?php

namespace App\Http\Requests\Admin\Message;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMessageRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'judul' => 'sometimes|string|max:200',
            'pesan' => 'sometimes|string|min:10|max:5000',
            'tipe' => 'sometimes|in:saran,instruksi,pengumuman,lainnya',
            'prioritas' => 'sometimes|in:biasa,penting,sangat_penting',
            'dibaca' => 'sometimes|boolean',
        ];
    }

    public function messages()
    {
        return [
            'judul.max' => 'Judul maksimal 200 karakter',
            'pesan.min' => 'Isi pesan minimal 10 karakter',
            'pesan.max' => 'Isi pesan maksimal 5000 karakter',
            'tipe.in' => 'Tipe pesan tidak valid',
            'prioritas.in' => 'Prioritas pesan tidak valid',
            'dibaca.boolean' => 'Status baca harus benar atau salah',
        ];
    }
}