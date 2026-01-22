<?php

namespace App\Services\Admin;

use App\Models\Report;
use App\Models\User;
use Carbon\Carbon;

class ReportService
{
    public function getReports(array $filters = [])
    {
        $query = Report::with(['unit', 'admin', 'session']);
        
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (isset($filters['priority'])) {
            $query->where('prioritas', $filters['priority']);
        }
        
        if (isset($filters['type'])) {
            $query->where('tipe', $filters['type']);
        }
        
        if (isset($filters['unit_id'])) {
            $query->where('unit_id', $filters['unit_id']);
        }
        
        if (isset($filters['admin_id'])) {
            $query->where('admin_id', $filters['admin_id']);
        }
        
        if (isset($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        
        if (isset($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }
        
        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('judul', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('deskripsi', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('session_id', 'like', '%' . $filters['search'] . '%');
            });
        }
        
        $sort = $filters['sort'] ?? 'created_at';
        $order = $filters['order'] ?? 'desc';
        
        return $query->orderBy($sort, $order)->paginate($filters['per_page'] ?? 20);
    }
    
    public function getReportDetail(string $id)
    {
        return Report::with(['unit', 'admin', 'session'])->findOrFail($id);
    }
    
    public function replyToReport(string $id, array $data): Report
    {
        $report = Report::findOrFail($id);
        
        $report->update([
            'status' => 'selesai',
            'tanggapan_admin' => $data['tanggapan'],
            'ditanggapi_pada' => Carbon::now(),
            'admin_id' => auth()->id()
        ]);
        
        return $report;
    }
    
    public function updateReportStatus(string $id, string $status): Report
    {
        $report = Report::findOrFail($id);
        
        $report->update([
            'status' => $status,
            'ditanggapi_pada' => $status === 'selesai' ? Carbon::now() : null
        ]);
        
        return $report;
    }
    
    public function updateReportPriority(string $id, string $priority): Report
    {
        $report = Report::findOrFail($id);
        
        $report->update([
            'prioritas' => $priority
        ]);
        
        return $report;
    }
    
    public function assignReport(string $id, string $adminId): Report
    {
        $report = Report::findOrFail($id);
        $admin = User::findOrFail($adminId);
        
        $report->update([
            'admin_id' => $adminId,
            'status' => 'diproses'
        ]);
        
        return $report;
    }
    
    public function getReportStats(): array
    {
        $total = Report::count();
        $new = Report::where('status', 'baru')->count();
        $inProgress = Report::where('status', 'diproses')->count();
        $completed = Report::where('status', 'selesai')->count();
        $rejected = Report::where('status', 'ditolak')->count();
        
        $unassigned = Report::whereNull('admin_id')->count();
        $highPriority = Report::whereIn('prioritas', ['tinggi', 'kritis'])->count();
        
        $today = Report::whereDate('created_at', Carbon::today())->count();
        $week = Report::where('created_at', '>=', Carbon::now()->startOfWeek())->count();
        $month = Report::where('created_at', '>=', Carbon::now()->startOfMonth())->count();
        
        $byType = Report::groupBy('tipe')
            ->selectRaw('tipe, count(*) as total')
            ->get()
            ->pluck('total', 'tipe');
        
        $byPriority = Report::groupBy('prioritas')
            ->selectRaw('prioritas, count(*) as total')
            ->orderByRaw("FIELD(prioritas, 'kritis', 'tinggi', 'sedang', 'rendah')")
            ->get();
        
        return [
            'total' => $total,
            'by_status' => [
                'baru' => $new,
                'diproses' => $inProgress,
                'selesai' => $completed,
                'ditolak' => $rejected
            ],
            'by_period' => [
                'hari_ini' => $today,
                'minggu_ini' => $week,
                'bulan_ini' => $month
            ],
            'unassigned' => $unassigned,
            'high_priority' => $highPriority,
            'by_type' => $byType,
            'by_priority' => $byPriority,
            'resolution_rate' => $total > 0 ? round(($completed + $rejected) / $total * 100, 1) : 0
        ];
    }
}