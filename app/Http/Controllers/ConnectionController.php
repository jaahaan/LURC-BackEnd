<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Connection;
use App\Models\User;
use Auth;
use App\Notifications\ConnectionNotification;
use App\Models\Notification;
use App\Models\Notify;

class ConnectionController extends Controller
{
    public function addConnection(Request $request){
        $connection = Connection::create([
            'sent_request_user' => Auth::user()->id,
            'received_request_user' => $request->id,
        ]);
        $authUser = Auth::user();
        $received_request_user = User::where('id', $request->id)->first();
        $msg = "requested to connect you";
        // $isRequest = 'true';
        // $received_request_user->notify(new ConnectionNotification($authUser, $connection_id, $msg, $isRequest));

        Notify::create([
            'type' => 'addConnection',
            'notifiable_id' => $request->id,
            'user_id' => Auth::user()->id,
            'connection_id' => $connection->id,
            'msg'=> $msg,
            'isRequest'=> 1,
        ]);  

        $body = Auth::user()->name .' requested to connect you.';

        \Mail::send('email-template', ['body' => $body], function ($message) use ($received_request_user) {
            $message->to($received_request_user->email)
                ->from('noreply@lurc.com', 'LURC')
                ->subject('Connection Request');
        });
        return response()->json([
            'success' => true,
            'data' => $connection
        ], 201);
    }

    public function getUserConnection(Request $request){
        $user = User::where('slug', $request->slug)->first();
        $limit = $request->limit? $request->limit : 4;
        $data1 = Connection::with('user1', 'user2')->where('connected', 1)->where('sent_request_user', $user->id)->orderBy('id', 'desc')->limit($limit)->get();
        $data2 = Connection::with('user1', 'user2')->where('connected', 1)->where('received_request_user', $user->id)->orderBy('id', 'desc')->limit($limit)->get();
        $formattedData = [];
        foreach($data1 as $value){
            array_push($formattedData, $value);
        }
        foreach($data2 as $value){
            // $connected1 = Connection::where('id', $value->id)->where('sent_request_user', Auth::user()->id)->first();
            // $connected2 = Connection::where('id', $value->id)->where('received_request_user', Auth::user()->id)->first();
            // if($connected1 || $connected2){
                array_push($formattedData, $value);
            // }
        }
        return response()->json([
            'success'=> true,
            'data'=>$formattedData,
        ],200);
    }

    public function getAuthUserConnection(Request $request){
        $limit = $request->limit? $request->limit : 6;
        $data1 = Connection::with('user1', 'user2')->where('connected', 1)->where('sent_request_user', Auth::user()->id)->orderBy('id', 'desc')->limit($limit)->get();
        $data2 = Connection::with('user1', 'user2')->where('connected', 1)->where('received_request_user', Auth::user()->id)->orderBy('id', 'desc')->limit($limit)->get();
        $formattedData = [];
        foreach($data1 as $value){
            array_push($formattedData, $value);
        }
        foreach($data2 as $value){
            // $connected1 = Connection::where('id', $value->id)->where('sent_request_user', Auth::user()->id)->first();
            // $connected2 = Connection::where('id', $value->id)->where('received_request_user', Auth::user()->id)->first();
            // if($connected1 || $connected2){
                array_push($formattedData, $value);
            // }
        }
        return response()->json([
            'success'=> true,
            'data'=>$formattedData,
        ],200);

        // return response()->json([
        //     'success'=> true,
        //     'data'=>$data1,
        // ],200);
    }
    public function getConnectionRequest(Request $request){
        $data = Connection::with('user1', 'user2')->where('connected', 0)
                        ->where('received_request_user', Auth::user()->id)
                        ->get();
        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
    public function connectionStatus(Request $request){

        $user = User::where('slug', $request->slug)->first();
        $sendRequest = Connection::with('user1', 'user2')
                        ->where('sent_request_user', Auth::user()->id)
                        ->where('received_request_user', $user->id)
                        ->where('connected', 0)->first();
        $receivedRequest = Connection::with('user1', 'user2')
                        ->where('sent_request_user', $user->id)
                        ->where('received_request_user', Auth::user()->id)
                        ->where('connected', 0)->first();
        $connected1 = Connection::with('user1', 'user2')
                        ->where('sent_request_user', Auth::user()->id)
                        ->where('received_request_user', $user->id)
                        ->where('connected', 1)->first();
        $connected2 = Connection::with('user1', 'user2')
                        ->where('sent_request_user', $user->id)
                        ->where('received_request_user', Auth::user()->id)
                        ->where('connected', 1)->first();

        if($sendRequest){
            return response()->json([
                'success' => true,
                'data' => $sendRequest
            ],203);
        } else if($receivedRequest){
            return response()->json([
                'success' => true,
                'data' => $receivedRequest
            ],202);
        } else if($connected1){
            return response()->json([
                'success' => true,
                'data' => $connected1, $connected2
            ],201);
        } else if( $connected2){
            return response()->json([
                'success' => true,
                'data' => $connected2
            ],201);
        }
        
    }

    public function acceptConnection(Request $request){        
        $connection = Connection::where('id', $request->id)->update([            
            'connected' => 1,
        ]);

        // $authUser = Auth::user();
        $received_request_user = User::where('id', $request->user_id)->first();
        $msg = "accepted your request";
        // $connection_id = $request->id;
        // $isRequest = 'true';
        // $received_request_user->notify(new ConnectionNotification($authUser, $connection_id, $msg, $isRequest));
        // Notify::create([
        //     'type' => 'acceptConnection',
        //     'notifiable' => $request->user_id,
        //     'user_id' => Auth::user()->id,
        //     'connection_id' => $request->id,
        //     'msg'=> $msg,
        //     'isRequest'=> 1,
        // ]);  
        Notify::where(['notifiable_id'=>Auth::user()->id,'connection_id'=>$request->id, 'isRequest' => 1])->update([
            'type' => 'acceptConnection',
            'notifiable_id' => $request->user_id,
            'user_id' => Auth::user()->id,
            'connection_id' => $request->id,
            'msg'=> $msg,
            'seen_at'=> null,
            'read_at'=> null,
            'isRequest'=> 1,
        ]); 
        $body = Auth::user()->name .' accepted your connection request.';

        \Mail::send('email-template', ['body' => $body], function ($message) use ($received_request_user) {
            $message->to($received_request_user->email)
                ->from('noreply@lurc.com', 'LURC')
                ->subject('Connection Request Accepted');
        });
        return response()->json([
            'success' => true,
            'data' => $connection
        ], 201);
    }

    public function ignoreConnection(Request $request){
        $connection = Connection::where('id', $request->id)->delete();
        Notify::where(['notifiable_id'=>Auth::user()->id,'connection_id'=>$request->id, 'isRequest' => 1])->delete();
        Notify::where(['notifiable_id'=>$request->user_id,'connection_id'=>$request->id, 'isRequest' => 1])->delete();
        return response()->json([
            'success' => true,
            'data' => $connection
        ], 201);
    }
}