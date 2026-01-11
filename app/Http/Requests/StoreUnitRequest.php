<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUnitRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Menggunakan Policy untuk otorisasi
        return $this->user()->can('create', \App\Models\Unit::class);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'officer_name' => 'nullable|string|max:255',
            'type' => 'required|string|max:100',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'photo' => 'nullable|url|max:2048', // Asumsi photo adalah URL
        ];
    }
}