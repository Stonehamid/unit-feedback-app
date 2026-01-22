<?php

namespace App\Http\Requests\Admin\Unit;

use Illuminate\Foundation\Http\FormRequest;

class StoreUnitRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'kode_unit' => 'required|string|max:20|unique:units,kode_unit',
            'nama_unit' => 'required|string|max:100',
            'deskripsi' => 'nullable|string|max:500',
            'jenis_unit' => 'required|in:kesehatan,akademik,administrasi,fasilitas,lainnya',
            'lokasi' => 'required|string|max:200',
            'gedung' => 'nullable|string|max:50',
            'lantai' => 'nullable|string|max:10',
            'kontak_telepon' => 'nullable|string|max:20',
            'kontak_email' => 'nullable|email|max:100',
            'jam_buka' => 'nullable|date_format:H:i',
            'jam_tutup' => 'nullable|date_format:H:i|after:jam_buka',
            'kapasitas' => 'nullable|integer|min:0',
            'status_aktif' => 'required|boolean',
        ];
    }

    public function messages()
    {
        return [
            'kode_unit.required' => 'Kode unit wajib diisi',
            'kode_unit.max' => 'Kode unit maksimal 20 karakter',
            'kode_unit.unique' => 'Kode unit sudah digunakan',
            'nama_unit.required' => 'Nama unit wajib diisi',
            'nama_unit.max' => 'Nama unit maksimal 100 karakter',
            'deskripsi.max' => 'Deskripsi maksimal 500 karakter',
            'jenis_unit.required' => 'Jenis unit wajib dipilih',
            'jenis_unit.in' => 'Jenis unit tidak valid',
            'lokasi.required' => 'Lokasi unit wajib diisi',
            'lokasi.max' => 'Lokasi maksimal 200 karakter',
            'gedung.max' => 'Nama gedung maksimal 50 karakter',
            'lantai.max' => 'Lantai maksimal 10 karakter',
            'kontak_telepon.max' => 'Nomor telepon maksimal 20 karakter',
            'kontak_email.email' => 'Format email tidak valid',
            'kontak_email.max' => 'Email maksimal 100 karakter',
            'jam_buka.date_format' => 'Format jam buka tidak valid (HH:mm)',
            'jam_tutup.date_format' => 'Format jam tutup tidak valid (HH:mm)',
            'jam_tutup.after' => 'Jam tutup harus setelah jam buka',
            'kapasitas.integer' => 'Kapasitas harus berupa angka',
            'kapasitas.min' => 'Kapasitas minimal 0',
            'status_aktif.required' => 'Status aktif wajib dipilih',
            'status_aktif.boolean' => 'Status aktif harus benar atau salah',
        ];
    }
}