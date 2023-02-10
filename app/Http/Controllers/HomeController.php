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
        $searchString= $request->keyword;
        $limit = $request->limit? $request->limit : 3;

        $users = User::where('name', 'LIKE','%'.$searchString.'%')->limit($limit)->get();
        return response()->json($users);
    }

    public function getUserInfo(Request $request)
    {
        // $limit = $request->limit? $request->limit : 3;
        return User::get();
    }
    public function getDepartments(Request $request)
    {
        $data = Department::get();
        // return response()->json([
        //     'success'=> true,
        //     'data'=>$data,
        // ],200);
        return $data;
    }
    public function getPeopleYouMayKnow(Request $request)
    {
        $limit = $request->limit? $request->limit : 3;
        $department_id=  $request->department_id;
        $query = User::with('department');
        
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
                array_push($formattedData1, $value);
            } else{
                if (count($formattedData) > 2) {
                    break;
                }
                array_push($formattedData, $value);
            }
        }
        
        return response()->json([
            'success'=> true,
            'data'=>$formattedData,
        ],200);
    }
    public function admin(Request $request)
    {
        //first check if you are loggedin and admin user ...
        // if(!Auth::check()){
        //     return redirect('/login');
        // }
        return view('admin');
    }

}
