<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUnitRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->unit);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'officer_name' => 'nullable|string|max:255',
            'type' => 'sometimes|required|string|max:100',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'photo' => 'nullable|url|max:2048',
        ];
    }
}