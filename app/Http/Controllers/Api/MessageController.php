<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    /**
     * Store new message (public/authenticated users)
     */
    public function store(Request $request, Unit $unit)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
        ]);

        // Jika user login, override name dengan nama user
        if (Auth::check()) {
            $validated['name'] = Auth::user()->name;
        }

        $message = $unit->messages()->create($validated);

        return [
            'message' => $message,
            'notification' => 'Message sent successfully'
        ];
    }

    /**
     * Delete message (admin only)
     */
    public function destroy(Message $message)
    {
        $message->delete();

        return [
            'message' => 'Message deleted successfully'
        ];
    }

    /**
     * Get my messages (authenticated users)
     */
    public function myMessages()
    {
        $user = Auth::user();
        
        $messages = Message::where('name', $user->name)
                          ->with('unit:id,name')
                          ->latest()
                          ->paginate(10);

        return $messages;
    }
}