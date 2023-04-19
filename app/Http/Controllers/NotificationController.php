<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Notification;
use App\Models\Notify;
use Illuminate\Http\Request;
// use Illuminate\Support\Facades\DB;
use Auth;
date_default_timezone_set('Asia/Dhaka');

class NotificationController extends Controller
{
    public function getNotification(Request $request)
    {
        $limit = $request->limit? $request->limit : 8;

        // $notification = Auth::user()->notifications()->where('data->msg','!=', 'msg')->orderBy('id', 'desc')->limit($limit)->get();
        // $notification = Auth::user()->notifications()->where('data->msg','!=', 'msg')->limit($limit)->get();
        $notification = Notify::with(['user', 'post'])->where('notifiable_id', Auth::user()->id)->where('msg','!=', 'msg')->orderBy('id', 'desc')->limit($limit)->get();
        // return auth()->user()->unreadNotifications()->limit(5)->get()->toArray();

        // $count = Auth::user()->notifications->where('seen_at', null)->count();
        $count = Notify::where('notifiable_id', Auth::user()->id)->where('seen_at', null)->where('msg','!=', 'msg')->count();

        $formattedData = [];
        foreach($notification as $post){
            $post['user_image'] = $post->user->image;
            $post['user_name'] = $post->user->name;
            $post['user_slug'] = $post->user->slug;
            if($post->post){
                $post['post_title'] = $post->post->title;
                $post['post_type'] = $post->post->type;
                $post['post_slug'] = $post->post->slug;
                
                unset($post['post']);
            }
            
            unset($post['user']);
            
            
            array_push($formattedData, $post);
        }

        return response()->json([
            'success' => true,
            'count' => $count,
            'data' =>$formattedData,
        ]);
    }
    public function getNotificationCount(Request $request)
    {
        
        $count = Notify::where('notifiable_id', Auth::user()->id)->where('seen_at', null)->where('msg','!=', 'msg')->count();

        \Log::info('count notification');
        \Log::info($count);

            return response()->json([
                'success' => true,
                'data' => $count,
            ]);
        
    }
    public function getReadNotification(Request $request)
    {
        $limit = $request->limit? $request->limit : 8;

        $notification = Notify::with(['user', 'post'])->where('notifiable_id', Auth::user()->id)->where('read_at', '!=', null)->where('msg','!=', 'msg')->limit($limit)->get();

        $formattedData = [];
        foreach($notification as $post){
            $post['user_image'] = $post->user->image;
            $post['user_name'] = $post->user->name;
            $post['user_slug'] = $post->user->slug;
            if($post->post){
                $post['post_title'] = $post->post->title;
                $post['post_type'] = $post->post->type;
                $post['post_slug'] = $post->post->slug;
                
                unset($post['post']);
            }
            
            unset($post['user']);
            
            
            array_push($formattedData, $post);
        }

        return response()->json([
            'success' => true,
            'data' =>$formattedData,
        ]);
    }
    public function getUnreadNotification(Request $request)
    {
        $limit = $request->limit? $request->limit : 8;

        $notification = Notify::with(['user', 'post'])->where('notifiable_id', Auth::user()->id)->where('read_at', null)->where('msg','!=', 'msg')->limit($limit)->get();

        $formattedData = [];
        foreach($notification as $post){
            $post['user_image'] = $post->user->image;
            $post['user_name'] = $post->user->name;
            $post['user_slug'] = $post->user->slug;
            if($post->post){
                $post['post_title'] = $post->post->title;
                $post['post_type'] = $post->post->type;
                $post['post_slug'] = $post->post->slug;
                
                unset($post['post']);
            }
            
            unset($post['user']);
            
            
            array_push($formattedData, $post);
        }

        return response()->json([
            'success' => true,
            'data' =>$formattedData,
        ]);
    }
    public function getRequestNotification()
    {
        // $notification =Auth::user()->isRequest; 
        $notification = Notify::where('notifiable_id', Auth::user()->id )->where('seen_at', null)->where('msg','!=', 'msg')->get();

        return response()->json([
            'success' => true,
            'data' =>$notification,
        ]);
    }
    public function markAsRead($id)
    {
        if($id){
            Notify::where('id', $id)->where('notifiable_id', Auth::user()->id)->where('msg','!=', 'msg')->update([
                'read_at' => now(),
            ]);
        }
        return response()->json([
            'success' => true,
        ]);
    }

    public function markAsSeen()
    {
        Notify::where('notifiable_id', Auth::user()->id)->where('msg','!=', 'msg')->update([
            'seen_at' => now(),
        ]);
        return response()->json([
            'success' => true,
        ]);    
    }
}
