<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Show the form to create a new notification (Admin/HR only)
     */
    public function createForm()
    {
        // Check if user is admin or HR
        if (!in_array(auth()->user()->role, ['admin', 'hr'])) {
            abort(403, 'Unauthorized');
        }

        // Get all users for the form
        $users = \App\Models\User::select('id', 'name', 'email', 'role')->orderBy('name')->get();

        return view('notifications.create', ['users' => $users]);
    }

    /**
     * Get all notifications for the authenticated user
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $notifications = $user->notifications()->recent()->paginate(15);

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $user->notifications()->unread()->count(),
        ]);
    }

    /**
     * Get unread notifications only
     */
    public function unread(Request $request)
    {
        $user = auth()->user();
        $notifications = $user->notifications()
            ->unread()
            ->recent()
            ->get();

        return response()->json([
            'notifications' => $notifications,
            'count' => $notifications->count(),
        ]);
    }

    /**
     * Get unread count (for bell badge)
     */
    public function unreadCount()
    {
        $count = auth()->user()->notifications()->unread()->count();
        return response()->json(['count' => $count]);
    }

    /**
     * Mark a notification as read
     */
    public function markAsRead($id)
    {
        $notification = Notification::findOrFail($id);
        
        // Check authorization
        if ($notification->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
        ]);
    }

    /**
     * Mark a notification as unread
     */
    public function markAsUnread($id)
    {
        $notification = Notification::findOrFail($id);
        
        // Check authorization
        if ($notification->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $notification->markAsUnread();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as unread',
        ]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        auth()->user()->notifications()->unread()->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read',
        ]);
    }

    /**
     * Delete a notification
     */
    public function destroy($id)
    {
        $notification = Notification::findOrFail($id);
        
        // Check authorization
        if ($notification->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted',
        ]);
    }

    /**
     * Delete all notifications for authenticated user
     */
    public function deleteAll()
    {
        try {
            $user = auth()->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            // Delete all notifications for this user only
            $deleted = $user->notifications()->delete();

            return response()->json([
                'success' => true,
                'message' => 'All notifications cleared successfully',
                'deleted' => $deleted
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete notifications: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new notification (Admin/HR only)
     */
    public function store(Request $request)
    {
        // Check if user is admin or HR
        $user = auth()->user();
        if (!in_array($user->role, ['admin', 'hr'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'role' => 'required|in:admin,hr,accountant,employee,all,public',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        // Handle different role types
        if ($validated['role'] === 'public') {
            // For public notifications, get all users
            $users = \App\Models\User::all();
        } elseif ($validated['role'] === 'all') {
            // Send to all users regardless of role
            $users = \App\Models\User::all();
        } else {
            // Send to specific role
            $users = \App\Models\User::where('role', $validated['role'])->get();
        }

        if ($users->isEmpty()) {
            return back()->with('error', 'No users found with the selected role.');
        }

        // Create notification for each user
        foreach ($users as $recipient) {
            Notification::create([
                'user_id' => $recipient->id,
                'title' => $validated['title'],
                'message' => $validated['message'],
                'type' => $validated['role'] === 'public' ? 'public' : 'general',
                'created_by' => auth()->id(),
            ]);
        }

        $roleLabel = $validated['role'] === 'all' ? 'all users' : ($validated['role'] === 'public' ? 'everyone (public)' : 'all ' . $validated['role'] . ' users');
        
        return redirect()->route('dashboard')->with('success', 'Notification sent to ' . $roleLabel . '!');
    }

    /**
     * Create notifications for multiple users (Admin/HR only)
     */
    public function storeBatch(Request $request)
    {
        // Check if user is admin or HR
        $user = auth()->user();
        if (!in_array($user->role, ['admin', 'hr'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|in:general,leave,salary,attendance,employee,department',
            'related_model' => 'nullable|string|max:100',
            'related_id' => 'nullable|integer',
        ]);

        $notifications = [];
        foreach ($validated['user_ids'] as $userId) {
            $notifications[] = Notification::create([
                'user_id' => $userId,
                'title' => $validated['title'],
                'message' => $validated['message'],
                'type' => $validated['type'],
                'related_model' => $validated['related_model'] ?? null,
                'related_id' => $validated['related_id'] ?? null,
                'created_by' => auth()->id(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Notifications created successfully',
            'count' => count($notifications),
            'notifications' => $notifications,
        ], 201);
    }
}
