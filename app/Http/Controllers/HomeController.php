<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Department;
use App\Models\Banner;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $limit = $request->limit? $request->limit : 5;

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
        return response()->json([
            'success'=> true,
            'data'=>$data,
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


    // public function uploadImages(Request $request)
    // {
    //     request()->file('img')->store('uploads');
    //     $pic = $request->img->hashName();
    //     // $pic = "/uploads/$pic";
    //     $url = env('APP_URL');
    //     $pic = $url . "uploads/$pic";
    //     return response()->json([
    //         'image' => $pic
    //     ], 200);
    // }
}
