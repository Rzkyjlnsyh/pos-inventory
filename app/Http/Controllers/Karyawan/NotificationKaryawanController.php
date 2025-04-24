<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notification;

class NotificationKaryawanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $notifications = Notification::orderBy('created_at', 'desc')->paginate(5);

        if ($request->ajax()) {
            $view = view('karyawan.notification.partials.notifications', compact('notifications'))->render();
            return response()->json([
                'notifications' => $view,
                'hasMorePages' => $notifications->hasMorePages(),
                'nextPageUrl' => $notifications->nextPageUrl()
            ]);
        }

        return view('karyawan.notification.index', compact('notifications'));
    }

    public function unreadCount()
    {
        $unreadCount = Notification::where('read', false)->count();

        return response()->json(['count' => $unreadCount]);
    }

    public function markAsRead()
    {
        Notification::where('read', false)->update(['read' => true]);

        $unreadCount = Notification::where('read', false)->count();

        return response()->json(['message' => 'Notifications marked as read', 'count' => $unreadCount]);
    }

    public function clearAll()
    {
        Notification::truncate();

        return response()->json(['success' => true]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
