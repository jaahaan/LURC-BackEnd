<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;
// use Illuminate\Support\Facades\DB;
use Auth;
date_default_timezone_set('Asia/Dhaka');

class NotificationController extends Controller
{
    public function getNotification()
    {
        $notification = Auth::user()->notifications;
        // $count = Auth::user()->notifications->where('seen_at', null)->count();
        $count = Auth::user()->notifications->where('seen_at', null)->count();

        \Log::info('count notification');
        \Log::info($count);

        return response()->json([
            'success' => true,
            'count' => $count,
            'data' =>$notification,
        ]);
    }
    public function getReadNotification()
    {
        $notification = Auth::user()->readnotifications;

        return response()->json([
            'success' => true,
            'data' =>$notification,
        ]);
    }
    public function getUnreadNotification()
    {
        $notification = Auth::user()->unreadnotifications;

        return response()->json([
            'success' => true,
            'data' =>$notification,
        ]);
    }
    public function getRequestNotification()
    {
        // $notification =Auth::user()->isRequest; 
        $notification = Auth::user()->notifications->where('seen_at', null)->get();

        return response()->json([
            'success' => true,
            'data' =>$notification,
        ]);
    }
    public function markAsRead($id)
    {
        if($id){
            Auth::user()->notifications->where('id', $id)->markAsRead();
        }
        return response()->json([
            'success' => true,
        ]);
    }

    public function markAsSeen()
    {
        Auth::user()->notifications->markAsSeen();
        return response()->json([
            'success' => true,
        ]);    }
}
