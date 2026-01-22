<?php

namespace App\Services\Admin;

use App\Models\Message;
use Carbon\Carbon;

class MessageService
{
    public function getMessages(array $filters = [])
    {
        $query = Message::with(['unit', 'admin']);
        
        if (isset($filters['unit_id'])) {
            $query->where('unit_id', $filters['unit_id']);
        }
        
        if (isset($filters['admin_id'])) {
            $query->where('admin_id', $filters['admin_id']);
        }
        
        if (isset($filters['type'])) {
            $query->where('tipe', $filters['type']);
        }
        
        if (isset($filters['priority'])) {
            $query->where('prioritas', $filters['priority']);
        }
        
        if (isset($filters['read'])) {
            $query->where('dibaca', filter_var($filters['read'], FILTER_VALIDATE_BOOLEAN));
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
                  ->orWhere('pesan', 'like', '%' . $filters['search'] . '%');
            });
        }
        
        $sort = $filters['sort'] ?? 'created_at';
        $order = $filters['order'] ?? 'desc';
        
        return $query->orderBy($sort, $order)->paginate($filters['per_page'] ?? 20);
    }
    
    public function createMessage(array $data): Message
    {
        $data['admin_id'] = auth()->id();
        
        return Message::create($data);
    }
    
    public function getMessageDetail(string $id)
    {
        return Message::with(['unit', 'admin'])->findOrFail($id);
    }
    
    public function updateMessage(string $id, array $data): Message
    {
        $message = Message::findOrFail($id);
        $message->update($data);
        
        return $message;
    }
    
    public function deleteMessage(string $id): void
    {
        $message = Message::findOrFail($id);
        $message->delete();
    }
    
    public function markMessageAsRead(string $id): Message
    {
        $message = Message::findOrFail($id);
        $message->markAsRead();
        
        return $message;
    }
    
    public function markAllMessagesAsRead(?string $unitId = null): int
    {
        $query = Message::where('dibaca', false);
        
        if ($unitId) {
            $query->where('unit_id', $unitId);
        }
        
        return $query->update([
            'dibaca' => true,
            'dibaca_pada' => Carbon::now()
        ]);
    }
}