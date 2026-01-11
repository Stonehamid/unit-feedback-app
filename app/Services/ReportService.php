<?php

namespace App\Services;

use App\Models\Report;
use App\Models\User;

class ReportService
{
    /**
     * Membuat laporan baru dari data yang sudah divalidasi.
     *
     * @param array $data Data yang sudah divalidasi dari controller.
     * @param \App\Models\User $admin Objek user yang sedang login (admin).
     * @return \App\Models\Report
     */
    public function createReport(array $data, User $admin): Report
    {
        return Report::create([
            'admin_id' => $admin->getKey(), // Mengambil ID user secara aman
            'unit_id' => $data['unit_id'],
            'title'   => $data['title'],
            'content' => $data['content'],
        ]);
    }
}