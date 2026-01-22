<?php

namespace App\Http\Requests\Admin\Rating;

use Illuminate\Foundation\Http\FormRequest;

class ReplyRatingRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'balasan' => 'required|string|min:5|max:1000',
        ];
    }

    public function messages()
    {
        return [
            'balasan.required' => 'Balasan wajib diisi',
            'balasan.min' => 'Balasan minimal 5 karakter',
            'balasan.max' => 'Balasan maksimal 1000 karakter',
        ];
    }
}