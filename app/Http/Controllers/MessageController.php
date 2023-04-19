<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Conversation;
use App\Models\ConversationChat;
use Illuminate\Http\Request;
use Auth;
use App\Notifications\ConnectionNotification;
use App\Models\Notification;
use App\Models\Notify;


class MessageController extends Controller
{
    public function getConversation(Request $request)
    {
        $limit = $request->limit? $request->limit : 4;
       
        $data = Conversation::with('fromUser', 'toUser', 'latestMessage')->where('last_msg', "!=", null)->get();

        $formattedData = [];
        foreach($data as $value){
            $connected1 = Conversation::where('from_id', Auth::user()->id)->first();
            $connected2 = Conversation::where('to_id', Auth::user()->id)->first();
            if($connected1 || $connected2){
                array_push($formattedData, $value);
            } if(count($formattedData)>$request->limit){
                break;
            } else{
                continue;
            }
        }
        return response()->json([
            'success' => true,
            'data' => $formattedData
        ]);
    }
    public function getSelectedUserChat(Request $request)
    {
        $limit = $request->limit? $request->limit : 6;
        // $data = ConversationChat::with('fromUser', 'toUser')->where('room_id', $request->roomId)->orderBy('id', 'asc')->limit($limit)->get();
        Conversation::where('id', $request->room_id)->update([
            'is_seen'=> now(),
        ]);
        $data = ConversationChat::with('fromUser', 'toUser')
                ->where('room_id', $request->roomId)->orderBy('id', 'asc')->get();
        
        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
    public function addChat(Request $request)
    {
        $data = ConversationChat::create($request->all());
        Conversation::where('id', $request->room_id)->update([
            'last_msg_from_id'=> $data->id,
            'last_msg_to_id'=> $request->to_id,
            'is_seen'=> now(),
            'last_msg'=> now()
        ]);
        $chat = Conversation::where('id', $request->room_id)->first();
        // if(Auth::check($request->to_id)){
            
        // } else{
        //     $authUser = Auth::user();
        //     $toUser = User::where('id', $request->to_id)->first();
        //     $connection_id = $request->room_id;
        //     $msg = "msg";
        //     $isRequest = false;
        //     $query = Auth::user()->notifications()->where(['data->connection_id' => $connection_id, 'notifiable_id'=> $request->to_id, 'data->isRequest' => false])->first();
        //     if(!$query){
        //         $toUser->notify(new ConnectionNotification($authUser, $connection_id, $msg, $isRequest));
        //     }
            
        //     Conversation::where('id', $request->room_id)->update([
        //         'last_msg_to_id' => $request->to_id,
        //         'is_seen'=> null
        //     ]);
        //     $conversation = Conversation::with('fromUser', 'toUser', 'latestMessage')->where('last_msg', "!=", null)->get();

        //     return response()->json([
        //         'success' => true,
        //         'data' => $conversation
        //     ]);
        // }
        return response()->json([
            'success' => true,
            'data' => $chat
        ]);
        
    }
    public function addConversation(Request $request)
    {
        $check1 = Conversation::where('from_id', $request->from_id)->where('to_id', $request->to_id)->first();
        $check2 = Conversation::where('to_id', $request->from_id)->where('from_id', $request->to_id)->first();
        if(!$check1 && !$check2){
            $data = Conversation::create($request->all());
            $query = Conversation::with('fromUser', 'toUser', 'latestMessage')->where('id',$data->id)->get();
            return response()->json([
                'success' => true,
                'data' => $query
            ], 200);
        }else if($check1){
            $query = Conversation::with('fromUser', 'toUser', 'latestMessage')->where('id',$check1->id)->get();
            return response()->json([
                'success' => true,
                'data' => $query
            ], 201);
        } else if($check2){
            $query = Conversation::with('fromUser', 'toUser', 'latestMessage')->where('id',$check2->id)->get();
            return response()->json([
                'success' => true,
                'data' => $query
            ], 201);
        }
        
    }
    public function getUnseenMsgCount(Request $request)
    {
        // $count = Auth::user()->notifications->where('seen_at', null)->where('data->msg','!=', 'msg')->count();
        $count = Notify::where(['notifiable_id' => Auth::user()->id, 'msg' => 'msg', 'isRequest' => 0])->count();
            return response()->json([
                'success' => true,
                'count' => $count
        ]);
    }
    public function addUnseenMsgCount(Request $request)
    {
        // $authUser = User::where('id', $request->from_id)->first();
        // $toUser = User::where('id', $request->to_id)->first();
        // $connection_id = $request->room_id;
        $msg = "msg";
        // $isRequest = false;
        // $query = Notification::where(['data->connection_id' => $connection_id, 'notifiable_id'=> $request->to_id, 'data->isRequest' => false])->first();
        $query = Notify::where(['connection_id' => $request->room_id, 'notifiable_id'=> $request->to_id, 'isRequest' => 0])->first();

        if(!$query){
            // $toUser->notify(new ConnectionNotification($authUser, $connection_id, $msg, $isRequest));
            Notify::create([
                'type' => 'msg',
                'notifiable_id' => $request->to_id,
                'user_id' => $request->from_id,
                'connection_id' => $request->room_id,
                'msg'=> $msg,
                'isRequest'=> 0,
            ]); 
        }
        
        Conversation::where('id', $request->room_id)->update([
            'last_msg_to_id' => $request->to_id,
            'is_seen'=> null
        ]);
        $conversation = Conversation::with('fromUser', 'toUser', 'latestMessage')->where('last_msg', "!=", null)->get();

        return response()->json([
            'success' => true,
            'data' => $conversation
        ]);
    }
    public function markSeenMsg(Request $request)
    {
        $data = Notify::where(['notifiable_id'=>Auth::user()->id, 'msg' => 'msg', 'isRequest' => 0])->delete();
        return response()->json([
            'success' => true,
        ]);
      
    }
    public function markReadMsg(Request $request)
    {
        $data = Notify::where(['notifiable_id'=>Auth::user()->id, 'msg' => 'msg', 'isRequest' => 0])->delete();
        return response()->json([
            'success' => true,
        ]);
    }
}
