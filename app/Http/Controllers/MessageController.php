<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Conversation;
use App\Models\ConversationChat;
use Illuminate\Http\Request;
use Auth;
class MessageController extends Controller
{
    public function getConversation(Request $request)
    {
        $limit = $request->limit? $request->limit : 4;
        // $query = Conversation::with('fromUser', 'toUser')->where('last_msg', "!=", null);
        $query = Conversation::with('fromUser', 'toUser');

        $data = $query->where('from_id', Auth::user()->id)->orWhere('to_id', Auth::user()->id)->orderBy('last_msg', 'desc')->limit($limit)->get();
        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
    public function getSelectedUserChat(Request $request)
    {
        $limit = $request->limit? $request->limit : 6;
        // $data = ConversationChat::with('fromUser', 'toUser')->where('room_id', $request->roomId)->limit($limit)->get();
        $data = ConversationChat::with('fromUser', 'toUser')
                ->where('room_id', $request->roomId)->orderBy('id', 'asc')->get();
        
        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
    public function addSelectedUserChat(Request $request)
    {
        $data = ConversationChat::create($request->all());
        Conversation::where('id', $request->room_id)->update([
            'last_msg'=> now()
        ]);
        return $data;
    }
    public function addConversation(Request $request)
    {
        $check1 = Conversation::where('from_id', $request->from_id)->where('to_id', $request->to_id)->first();
        $check2 = Conversation::where('to_id', $request->from_id)->where('from_id', $request->to_id)->first();
        if(!$check1 && !$check2){
            $data = Conversation::create($request->all());
            $query = Conversation::with('fromUser', 'toUser')->where('id',$data->id)->get();
            return response()->json([
                'success' => true,
                'data' => $query
            ], 200);
        }else if($check1){
            $query = Conversation::with('fromUser', 'toUser')->where('id',$check1->id)->get();
            return response()->json([
                'success' => true,
                'data' => $query
            ], 201);
        } else if($check2){
            $query = Conversation::with('fromUser', 'toUser')->where('id',$check2->id)->get();
            return response()->json([
                'success' => true,
                'data' => $query
            ], 201);
        }
        
    }
}
