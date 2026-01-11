<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Unit;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function store(Request $request, Unit $unit)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $message = $unit->messages()->create($request->validated());
        return response()->json($message, 201);
    }

    public function destroy(Message $message)
    {
        // Hanya admin yang bisa hapus pesan
        $this->authorize('delete', $message->unit);
        
        $message->delete();
        return response()->json(null, 204);
    }
}