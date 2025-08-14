<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\SystemMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SystemMessageController extends Controller
{
    public function index(): Response
    {
        $systemMessages = auth()->user()->systemMessages()
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('settings/SystemMessages', [
            'systemMessages' => $systemMessages,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'message' => 'required|string',
            'is_active' => 'boolean',
        ]);

        auth()->user()->systemMessages()->create($validated);

        return redirect()->route('settings.system-messages')
            ->with('success', 'System message created successfully');
    }

    public function update(Request $request, SystemMessage $systemMessage): RedirectResponse
    {
        if ($systemMessage->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'message' => 'required|string',
            'is_active' => 'boolean',
        ]);

        $systemMessage->update($validated);

        return redirect()->route('settings.system-messages')
            ->with('success', 'System message updated successfully');
    }

    public function destroy(SystemMessage $systemMessage): RedirectResponse
    {
        if ($systemMessage->user_id !== auth()->id()) {
            abort(403);
        }

        $systemMessage->delete();

        return redirect()->route('settings.system-messages')
            ->with('success', 'System message deleted successfully');
    }

    public function toggle(SystemMessage $systemMessage): RedirectResponse
    {
        if ($systemMessage->user_id !== auth()->id()) {
            abort(403);
        }

        // Deactivate all other messages for this user if activating this one
        if (!$systemMessage->is_active) {
            auth()->user()->systemMessages()
                ->where('id', '!=', $systemMessage->id)
                ->update(['is_active' => false]);
        }

        $systemMessage->update(['is_active' => !$systemMessage->is_active]);

        return redirect()->route('settings.system-messages')
            ->with('success', 'System message status updated');
    }
}
