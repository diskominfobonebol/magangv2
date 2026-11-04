<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class NotificationController extends Controller
{
    /**
     * Halaman index daftar lengkap notifikasi pengguna yang sedang login.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $filter = $request->get('filter', 'all');

        $query = $user->notifications();

        if ($filter === 'unread') {
            $query->whereNull('read_at');
        }

        $notifications = $query->latest()->paginate(15)->withQueryString();
        $unreadCount = $user->unreadNotifications()->count();

        return view('notifications.index', compact('notifications', 'filter', 'unreadCount'));
    }

    /**
     * Endpoint API ringan untuk polling jumlah notifikasi belum dibaca.
     */
    public function unreadCount()
    {
        $count = Auth::user()->unreadNotifications()->count();

        return response()->json([
            'unread_count' => $count,
        ]);
    }

    /**
     * Endpoint API untuk mengambil 10 notifikasi terbaru dropdown header.
     */
    public function recent()
    {
        $notifications = Auth::user()->notifications()
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->data['title'] ?? 'Pemberitahuan',
                    'message' => $notification->data['message'] ?? '',
                    'url' => $notification->data['url'] ?? '#',
                    'icon' => $notification->data['icon'] ?? 'bell',
                    'type' => $notification->data['type'] ?? 'general',
                    'read_at' => $notification->read_at,
                    'is_read' => !is_null($notification->read_at),
                    'created_at_human' => Carbon::parse($notification->created_at)->diffForHumans(),
                ];
            });

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => Auth::user()->unreadNotifications()->count(),
        ]);
    }

    /**
     * Tandai satu notifikasi sebagai telah dibaca.
     */
    public function markAsRead(string $id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'unread_count' => Auth::user()->unreadNotifications()->count(),
            ]);
        }

        return back()->with('success', 'Notifikasi telah ditandai dibaca.');
    }

    /**
     * Tandai semua notifikasi milik pengguna sebagai telah dibaca.
     */
    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'unread_count' => 0,
            ]);
        }

        return back()->with('success', 'Semua notifikasi telah ditandai dibaca.');
    }

    /**
     * Hapus satu notifikasi milik pengguna.
     */
    public function destroy(string $id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->delete();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'unread_count' => Auth::user()->unreadNotifications()->count(),
            ]);
        }

        return back()->with('success', 'Notifikasi berhasil dihapus.');
    }
}

