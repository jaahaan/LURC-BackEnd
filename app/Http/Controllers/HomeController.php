<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Department;
use App\Models\Banner;
use App\Models\Connection;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
date_default_timezone_set('Asia/Dhaka');

class HomeController extends Controller
{
    public function getBanner()
    {
        $data = Banner::get();
        return response()->json([
            'success'=> true,
            'data'=>$data
        ],200);
    }
    public function search(Request $request){
        $search= $request->keyword;
        $limit = $request->limit? $request->limit : 10;
        
        $query =  User::where(['isActive' => 1]);

        if($search){
            $query->where(function ($queryy) use ($search){
                $queryy->where('name',  'like', "%$search%")
                ->orWhere('email', 'like', "%$search%");
            });
        }

        $users = $query->limit($limit)->get();
        return response()->json($users);
    }

    
    public function getDepartments(Request $request)
    {
        $data = Department::get();
        return $data;
    }
    public function getPeopleYouMayKnow(Request $request)
    {
        $limit = $request->limit? $request->limit : 10;
        $department_id=  $request->department_id;
        $query = User::with('department')->where(['isActive' => 1]);
        
        // if(!$department_id) {
        //     $query->where('department_id', Auth::user()->department_id)
        //     ->orWhere('designation', Auth::user()->designation);           
        // } else if($department_id) {
        //     $query->where('department_id', $request->department_id);
        // } else if($query == []) {
        //     $query->orWhere('designation', Auth::user()->designation);
        // }
        $data = $query->inRandomOrder()->get();
        \Log::info('getPeopleYouMayKnow');
        \Log::info($data);

        $formattedData = [];
        $formattedData1 = [];

        foreach($data as $value){
            $connected1 = Connection::
                    where('sent_request_user', Auth::user()->id)
                    ->where('received_request_user', $value->id)->first();
            $connected2 = Connection::
                    where('sent_request_user', $value->id)
                    ->where('received_request_user', Auth::user()->id)->first();
            \Log::info('connected1');
            \Log::info($connected1);
            \Log::info('connected2');
            \Log::info($connected2);
            $value['status'] = "connect";
            
            if($connected1 || $connected2 || $value->id == Auth::user()->id){
                // array_push($formattedData1, $value);
            } else{
                if (count($formattedData) == $limit) {
                    break;
                }
                unset($value['about']);
                unset($value['created_at']);
                unset($value['department_id']);
                unset($value['email']);
                unset($value['honors_and_awards']);
                unset($value['isActive']);
                unset($value['passwordToken']);
                unset($value['status']);
                unset($value['token_expired_at']);
                unset($value['twoFactor']);
                unset($value['updated_at']);
                unset($value['userType']);

                array_push($formattedData, $value);
            }
        }
        
        return response()->json([
            'success'=> true,
            'data'=>$formattedData,
        ],200);
    }

}
