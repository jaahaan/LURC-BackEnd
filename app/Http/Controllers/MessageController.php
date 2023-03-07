<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Conversation;
use App\Models\ConversationChat;
use Illuminate\Http\Request;
use Auth;
use App\Notifications\ConnectionNotification;
use App\Models\Notification;


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
        $data = ConversationChat::with('fromUser', 'toUser')
                ->where('room_id', $request->roomId)->orderBy('id', 'asc')->get();
        Conversation::where('id', $request->room_id)->update([
            'is_seen'=> now(),
        ]);
        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
    public function addSelectedUserChat(Request $request)
    {
        $data = ConversationChat::create($request->all());
        Conversation::where('id', $request->room_id)->update([
            'last_msg_from_id'=> $data->id,
            'is_seen'=> now(),
            'last_msg'=> now()
        ]);
        $chat = Conversation::where('id', $request->room_id)->first();
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
        $count = Auth::user()->notifications()->where('data->msg', 'msg')->count();
       
            return response()->json([
                'success' => true,
                'count' => $count
        ]);
    }
    public function addUnseenMsgCount(Request $request)
    {
        $authUser = Auth::user();
        $toUser = User::where('id', $request->to_id)->first();
        $connection_id = $request->room_id;
        $msg = "msg";
        $query = Auth::user()->notifications()->where('data->connection_id', $connection_id)->first();
        if(!$query){
            $toUser->notify(new ConnectionNotification($authUser, $connection_id, $msg));
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
        $data = Notification::where(['notifiable_id'=>Auth::user()->id, 'data->msg' => 'msg'])->delete();
        return response()->json([
            'success' => true,
        ]);
        // Auth::user()->notifications()->where(['notifiable_id'=>Auth::user()->id, 'data->msg' => 'msg'])->update([
        //     'seen_at' => now(),
        // ]);
        // return response()->json([
        //     'success' => true,
        // ]);        
    }
    public function markReadMsg(Request $request)
    {
        $data = Notification::where(['notifiable_id'=>Auth::user()->id, 'data->msg' => 'msg'])->delete();
        return response()->json([
            'success' => true,
        ]);
    }
}
