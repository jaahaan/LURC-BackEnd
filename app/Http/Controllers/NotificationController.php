<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Notify;
use Illuminate\Http\Request;
// use Illuminate\Support\Facades\DB;
use Auth;
class NotificationController extends Controller
{
    public function getNotification(Request $request){
        $limit = $request->limit? $request->limit : 5;
        $user_id = $request->user_id? $request->user_id : Auth::user()->id;
        $data = Notify::where('post_user_id', $user_id)->with('user', 'post')->get();
        $seenCount = Notify::where('seen_count', 0)->count();
        // $seen = Notify::where('seen', 0)->count();
        $formattedData = [];
        foreach($data as $value){
            $notify = $value;
            
            $notify['seen_count'] = $seenCount;
            $notify['post_slug'] = $notify->post->slug;

            $notify['image'] = $notify->user->image;
            $notify['user_id'] = $notify->user->user_id;
            $notify['user_name'] = $notify->user->name;
            $notify['user_slug'] = $notify->user->slug;
            // $notify['department'] = $notify->user->department;
            // $notify['designation'] = $notify->user->designation;

            unset($notify['post']);
            unset($notify['user']);

            array_push($formattedData, $notify);

        }
        return response()->json([
            'success'=> true,
            'seenCount'=>$seenCount,
            'data'=>$formattedData,
        ],200);
        
    }

    public function updateSeenCount(Request $request){
        $user_id = $request->user_id? $request->user_id : Auth::user()->id;
        $data = Notify::where('post_user_id', $user_id)->where('seen_count', 0)->get();
        
        $formattedData = [];
        foreach($data as $value){
            $notify = $value;
            Notify::where('id', $notify->id)->update([
                'seen_count' => 1,
            ]);
        }
        return response()->json([
            'success'=> true,
        ],200);
        
    }

    public function updateSeen($id){
        
            Notify::where('id', $id)->update([
                'seen' => 1,
            ]);
        
        return response()->json([
            'success'=> true,
        ],200);
        
    }
}
