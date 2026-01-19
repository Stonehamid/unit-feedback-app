<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ExportReadyMail extends Mailable
{
    use Queueable, SerializesModels;

    public $type;
    public $format;
    public $filePath;
    public $userName;
    public $downloadUrl;
    
    public function __construct(string $type, string $format, string $filePath, string $userName)
    {
        $this->type = $type;
        $this->format = $format;
        $this->filePath = $filePath;
        $this->userName = $userName;
        $this->downloadUrl = url("/api/exports/download/" . basename($filePath, '.' . $format));
    }
    
    public function build()
    {
        return $this->subject("Your {$this->type} export is ready")
            ->markdown('emails.export-ready')
            ->with([
                'type' => $this->type,
                'format' => $this->format,
                'userName' => $this->userName,
                'downloadUrl' => $this->downloadUrl,
                'fileSize' => filesize(storage_path('app/' . $this->filePath)),
            ]);
    }
}