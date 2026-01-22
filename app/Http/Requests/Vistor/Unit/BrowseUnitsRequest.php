<?php

namespace App\Http\Requests\Visitor;

use Illuminate\Foundation\Http\FormRequest;

class BrowseUnitsRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'jenis' => 'nullable|in:kesehatan,akademik,administrasi,fasilitas,lainnya',
            'gedung' => 'nullable|string|max:50',
            'search' => 'nullable|string|max:100',
            'per_page' => 'nullable|integer|min:1|max:100',
            'sort' => 'nullable|in:nama_unit,kode_unit,created_at',
            'order' => 'nullable|in:asc,desc',
            'lat' => 'nullable|numeric|between:-90,90',
            'lng' => 'nullable|numeric|between:-180,180',
            'radius' => 'nullable|numeric|min:0.1|max:50',
        ];
    }

    public function messages()
    {
        return [
            'jenis.in' => 'Jenis unit tidak valid',
            'gedung.max' => 'Nama gedung maksimal 50 karakter',
            'search.max' => 'Pencarian maksimal 100 karakter',
            'per_page.integer' => 'Per page harus berupa angka',
            'per_page.min' => 'Per page minimal 1',
            'per_page.max' => 'Per page maksimal 100',
            'sort.in' => 'Sort field tidak valid',
            'order.in' => 'Order harus asc atau desc',
            'lat.numeric' => 'Latitude harus berupa angka',
            'lat.between' => 'Latitude harus antara -90 dan 90',
            'lng.numeric' => 'Longitude harus berupa angka',
            'lng.between' => 'Longitude harus antara -180 dan 180',
            'radius.numeric' => 'Radius harus berupa angka',
            'radius.min' => 'Radius minimal 0.1 km',
            'radius.max' => 'Radius maksimal 50 km',
        ];
    }
}