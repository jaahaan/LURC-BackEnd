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
    public function getNotification(Request $request)
    {
        $limit = $request->limit? $request->limit : 3;

        $notification = Auth::user()->notifications()->where('data->msg','!=', 'msg')->limit($limit)->get();
        // return auth()->user()->unreadNotifications()->limit(5)->get()->toArray();

        // $count = Auth::user()->notifications->where('seen_at', null)->count();
        $count = Auth::user()->notifications()->where('seen_at', null)->where('data->msg','!=', 'msg')->count();

        \Log::info('count notification');
        \Log::info($count);

        return response()->json([
            'success' => true,
            'count' => $count,
            'data' =>$notification,
        ]);
    }
    public function getNotificationCount(Request $request)
    {
        
        $count = Auth::user()->notifications()->where('seen_at', null)->where('data->msg','!=', 'msg')->count();

        \Log::info('count notification');
        \Log::info($count);

            return response()->json([
                'success' => true,
                'data' => $count,
            ]);
        
    }
    public function getReadNotification()
    {
        $notification = Auth::user()->readnotifications->where('data->msg','!=', 'msg');

        return response()->json([
            'success' => true,
            'data' =>$notification,
        ]);
    }
    public function getUnreadNotification()
    {
        $notification = Auth::user()->unreadnotifications->where('data->msg','!=', 'msg');

        return response()->json([
            'success' => true,
            'data' =>$notification,
        ]);
    }
    public function getRequestNotification()
    {
        // $notification =Auth::user()->isRequest; 
        $notification = Auth::user()->notifications->where('seen_at', null)->where('data->msg','!=', 'msg')->get();

        return response()->json([
            'success' => true,
            'data' =>$notification,
        ]);
    }
    public function markAsRead($id)
    {
        if($id){
            Auth::user()->notifications->where('id', $id)->where('data->msg','!=', 'msg')->markAsRead();
        }
        return response()->json([
            'success' => true,
        ]);
    }

    public function markAsSeen()
    {
        Auth::user()->notifications()->where('data->msg','!=', 'msg')->update([
            'seen_at' => now(),
        ]);
        return response()->json([
            'success' => true,
        ]);    
    }
}
