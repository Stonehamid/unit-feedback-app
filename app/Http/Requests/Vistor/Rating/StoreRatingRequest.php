<?php

namespace App\Http\Requests\Visitor;

use Illuminate\Foundation\Http\FormRequest;

class StoreRatingRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'scores' => 'required|array|min:1',
            'scores.*' => 'required|numeric|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ];
    }

    public function messages()
    {
        return [
            'scores.required' => 'Skor rating wajib diisi',
            'scores.array' => 'Skor harus dalam format array',
            'scores.min' => 'Minimal satu kategori rating harus diisi',
            'scores.*.required' => 'Skor untuk setiap kategori wajib diisi',
            'scores.*.numeric' => 'Skor harus berupa angka',
            'scores.*.min' => 'Skor minimal adalah 1',
            'scores.*.max' => 'Skor maksimal adalah 5',
            'comment.max' => 'Komentar maksimal 1000 karakter',
        ];
    }
}