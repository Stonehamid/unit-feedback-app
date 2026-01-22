<?php

namespace Database\Seeders;

use App\Models\RatingCategory;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class RatingCategorySeeder extends Seeder
{
    public function run()
    {
        $units = Unit::all();
        
        foreach ($units as $unit) {
            $categories = [];
            
            if ($unit->jenis_unit === 'kesehatan') {
                $categories = [
                    ['nama_kategori' => 'Fasilitas', 'slug' => 'fasilitas', 'urutan' => 1],
                    ['nama_kategori' => 'Pelayanan', 'slug' => 'pelayanan', 'urutan' => 2],
                    ['nama_kategori' => 'Kebersihan', 'slug' => 'kebersihan', 'urutan' => 3],
                    ['nama_kategori' => 'Kenyamanan', 'slug' => 'kenyamanan', 'urutan' => 4],
                ];
            } elseif ($unit->jenis_unit === 'akademik') {
                $categories = [
                    ['nama_kategori' => 'Pengajaran', 'slug' => 'pengajaran', 'urutan' => 1],
                    ['nama_kategori' => 'Fasilitas Lab', 'slug' => 'fasilitas-lab', 'urutan' => 2],
                    ['nama_kategori' => 'Administrasi', 'slug' => 'administrasi', 'urutan' => 3],
                    ['nama_kategori' => 'Suasana Belajar', 'slug' => 'suasana-belajar', 'urutan' => 4],
                ];
            } else {
                $categories = [
                    ['nama_kategori' => 'Fasilitas', 'slug' => 'fasilitas', 'urutan' => 1],
                    ['nama_kategori' => 'Pelayanan', 'slug' => 'pelayanan', 'urutan' => 2],
                    ['nama_kategori' => 'Kebersihan', 'slug' => 'kebersihan', 'urutan' => 3],
                    ['nama_kategori' => 'Aksesibilitas', 'slug' => 'aksesibilitas', 'urutan' => 4],
                ];
            }
            
            foreach ($categories as $cat) {
                RatingCategory::create([
                    'unit_id' => $unit->id,
                    'nama_kategori' => $cat['nama_kategori'],
                    'slug' => $cat['slug'] . '-' . $unit->kode_unit,
                    'deskripsi' => 'Penilaian untuk ' . strtolower($cat['nama_kategori']),
                    'urutan' => $cat['urutan'],
                    'wajib_diisi' => true,
                    'status_aktif' => true,
                ]);
            }
        }
    }
}