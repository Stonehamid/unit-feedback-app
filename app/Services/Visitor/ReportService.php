<?php

namespace App\Services\Visitor;

use App\Models\Report;
use App\Models\Unit;
use App\Models\VisitorSession;
use Carbon\Carbon;

class ReportService
{
    public function storeReport(
        ?string $unitId,
        string $title,
        string $description,
        string $type,
        string $priority,
        array $attachments,
        string $sessionId,
        string $ipAddress,
        string $userAgent
    ): Report {
        if ($unitId) {
            Unit::findOrFail($unitId);
        }
        
        $this->ensureVisitorSession($sessionId, $ipAddress, $userAgent);
        
        return Report::create([
            'unit_id' => $unitId,
            'session_id' => $sessionId,
            'visitor_ip' => $ipAddress,
            'judul' => $title,
            'deskripsi' => $description,
            'tipe' => $type,
            'prioritas' => $priority,
            'status' => 'baru',
            'lampiran' => !empty($attachments) ? $attachments : null,
            'metadata' => ['created_via' => 'visitor']
        ]);
    }
    
    public function getReportStatus(string $reportId)
    {
        return Report::findOrFail($reportId);
    }
    
    public function getVisitorReports(string $sessionId)
    {
        return Report::where('session_id', $sessionId)
            ->with('unit')
            ->orderBy('created_at', 'desc')
            ->get();
    }
    
    public function getReportTypes(): array
    {
        return [
            ['value' => 'masalah', 'label' => 'Masalah/Problem'],
            ['value' => 'saran', 'label' => 'Saran/Usulan'],
            ['value' => 'keluhan', 'label' => 'Keluhan'],
            ['value' => 'pujian', 'label' => 'Pujian'],
            ['value' => 'lainnya', 'label' => 'Lainnya']
        ];
    }
    
    public function getPriorityLevels(): array
    {
        return [
            ['value' => 'rendah', 'label' => 'Rendah', 'color' => 'green'],
            ['value' => 'sedang', 'label' => 'Sedang', 'color' => 'yellow'],
            ['value' => 'tinggi', 'label' => 'Tinggi', 'color' => 'orange'],
            ['value' => 'kritis', 'label' => 'Kritis', 'color' => 'red']
        ];
    }
    
    private function ensureVisitorSession(string $sessionId, string $ipAddress, string $userAgent): void
    {
        VisitorSession::updateOrCreate(
            ['session_id' => $sessionId],
            [
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'terakhir_aktivitas' => Carbon::now(),
                'metadata' => ['last_action' => 'submit_report']
            ]
        );
    }
}