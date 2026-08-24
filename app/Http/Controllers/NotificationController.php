<?php

namespace App\Http\Controllers;

use App\Models\AdminNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        AdminNotification::where('is_read', false)->update(['is_read' => true]);

        $notifications = AdminNotification::with('user')
            ->latest()
            ->paginate(15);

        return view('notifications.index', compact('notifications'));
    }
}