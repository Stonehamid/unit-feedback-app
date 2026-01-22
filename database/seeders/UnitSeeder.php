<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    public function run()
    {
        $units = [
            [
                'kode_unit' => 'UK-001',
                'nama_unit' => 'Unit Kesehatan Pusat',
                'deskripsi' => 'Layanan kesehatan utama universitas',
                'jenis_unit' => 'kesehatan',
                'lokasi' => 'Gedung Rektorat Lantai 1',
                'gedung' => 'Rektorat',
                'lantai' => '1',
                'kontak_telepon' => '021-1234567',
                'kontak_email' => 'kesehatan@university.edu',
                'jam_buka' => '08:00:00',
                'jam_tutup' => '16:00:00',
                'kapasitas' => 50,
                'status_aktif' => true,
            ],
            [
                'kode_unit' => 'UA-001',
                'nama_unit' => 'Fakultas Teknik',
                'deskripsi' => 'Fakultas teknik dengan berbagai program studi',
                'jenis_unit' => 'akademik',
                'lokasi' => 'Gedung Teknik Lt. 3-5',
                'gedung' => 'Teknik',
                'lantai' => '3',
                'kontak_telepon' => '021-1234568',
                'kontak_email' => 'teknik@university.edu',
                'jam_buka' => '07:00:00',
                'jam_tutup' => '20:00:00',
                'kapasitas' => 500,
                'status_aktif' => true,
            ],
            [
                'kode_unit' => 'UF-001',
                'nama_unit' => 'Perpustakaan Utama',
                'deskripsi' => 'Perpustakaan dengan koleksi lengkap',
                'jenis_unit' => 'fasilitas',
                'lokasi' => 'Gedung Perpustakaan Lt. 1-3',
                'gedung' => 'Perpustakaan',
                'lantai' => '1',
                'kontak_telepon' => '021-1234569',
                'kontak_email' => 'perpustakaan@university.edu',
                'jam_buka' => '08:00:00',
                'jam_tutup' => '21:00:00',
                'kapasitas' => 300,
                'status_aktif' => true,
            ],
        ];

        foreach ($units as $unit) {
            Unit::create($unit);
        }
    }
}