<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreUnitRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Akan dihandle oleh middleware
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255|unique:units,name',
            'officer_name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'description' => 'required|string|min:10',
            'location' => 'required|string|max:255',
            'photo' => 'nullable|image|max:2048|mimes:jpg,jpeg,png',
        ];
    }

    public function messages()
    {
        return [
            'name.unique' => 'Nama unit sudah digunakan.',
            'photo.max' => 'Ukuran foto maksimal 2MB.',
            'photo.mimes' => 'Format foto harus jpg, jpeg, atau png.',
        ];
    }
}