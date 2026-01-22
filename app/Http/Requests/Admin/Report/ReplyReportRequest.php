<?php

namespace App\Http\Requests\Admin\Report;

use Illuminate\Foundation\Http\FormRequest;

class ReplyReportRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'tanggapan' => 'required|string|min:10|max:2000',
        ];
    }

    public function messages()
    {
        return [
            'tanggapan.required' => 'Tanggapan wajib diisi',
            'tanggapan.min' => 'Tanggapan minimal 10 karakter',
            'tanggapan.max' => 'Tanggapan maksimal 2000 karakter',
        ];
    }
}