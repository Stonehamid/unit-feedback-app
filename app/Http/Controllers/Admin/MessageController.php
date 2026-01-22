<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Http\Requests\Admin\Message\StoreMessageRequest;
use App\Http\Requests\Admin\Message\UpdateMessageRequest;
use App\Services\Admin\MessageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function __construct(
        protected MessageService $messageService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $messages = $this->messageService->getMessages($request->all());

        return response()->json([
            'success' => true,
            'data' => $messages,
        ]);
    }

    public function store(StoreMessageRequest $request): JsonResponse
    {
        $message = $this->messageService->createMessage($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Pesan berhasil dikirim',
            'data' => $message,
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $message = $this->messageService->getMessageDetail($id);

        return response()->json([
            'success' => true,
            'data' => $message,
        ]);
    }

    public function update(UpdateMessageRequest $request, string $id): JsonResponse
    {
        $message = $this->messageService->updateMessage($id, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Pesan berhasil diperbarui',
            'data' => $message,
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $this->messageService->deleteMessage($id);

        return response()->json([
            'success' => true,
            'message' => 'Pesan berhasil dihapus',
        ]);
    }

    public function markAsRead(string $id): JsonResponse
    {
        $message = $this->messageService->markMessageAsRead($id);

        return response()->json([
            'success' => true,
            'message' => 'Pesan ditandai sebagai sudah dibaca',
            'data' => $message,
        ]);
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'unit_id' => 'nullable|exists:units,id',
        ]);

        $count = $this->messageService->markAllMessagesAsRead($validated['unit_id'] ?? null);

        return response()->json([
            'success' => true,
            'message' => $count . ' pesan ditandai sebagai sudah dibaca',
            'count' => $count,
        ]);
    }
}