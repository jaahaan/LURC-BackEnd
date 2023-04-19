<?php

namespace App\Http\Controllers;
use App\Models\User;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Validator;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    
    public function getUserList(Request $request)
    {
        $search = $request->search;
        $limit = $request->limit? $request->limit : 5;
    
        $query =  User::where(['userType' => 'teacher', 'isActive' => 1]);

        if($search){
            $query->where(function ($queryy) use ($search){
                $queryy->where('name',  'like', "%$search%")
                ->orWhere('email', 'like', "%$search%");
            });
        }

        $data = $query->limit($limit)->orderBy('id', 'desc')->get();

        $formattedData = [];
        foreach($data as $value){
            $value['id'] = $value->id;
            $value['image'] = $value->image;
            $value['name'] = $value->name;
            $value['email'] = $value->email;
            $value['userType'] = $value->userType;
            unset($value['user']);

            array_push($formattedData, $value);
        }
        return response()->json([
            'success'=> true,
            'data'=>$formattedData,
        ],200);
    }

    public function adminList(Request $request)
    {
        $search = $request->search;
        $limit = $request->limit? $request->limit : 10;
    
        $query =  User::where(['userType' => 'admin', 'isActive' => 1])->with('department');

        if($search){
            $query->where(function ($queryy) use ($search){
                $queryy->where('name',  'like', "%$search%")
                ->orWhere('email', 'like', "%$search%");
            });
        }

        $data = $query->limit($limit)->orderBy('id', 'desc')->get();

        $formattedData = [];
        foreach($data as $value){
            $value['id'] = $value->id;
            $value['image'] = $value->image;
            $value['name'] = $value->name;
            $value['email'] = $value->email;
            $value['user_slug'] = $value->slug;
            $value['department'] = $value->department;
            $value['designation'] = $value->designation;
            $value['userType'] = $value->userType;
            unset($value['user']);

            array_push($formattedData, $value);
        }
        return response()->json([
            'success'=> true,
            'data'=>$formattedData,
        ],200);
    }

    public function addAdmin(Request $request)
    {
        $validator = Validator::make($request->all(),
        [
            'user_name' => 'required',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(), 422);
        }
        $data = User::where('id', $request->user_name)->update([
            'userType' => 'admin',
        ]);
        return response()->json([
            'success'=> true,
            'data'=>$data,
        ],200);
    }
    public function adminRemove(Request $request)
    {
        
        $data = $request->all();
        $user_id = Auth::user()->id;
        $user = User::select('id','password','email')->where('id',$user_id)->first();
        if(!Hash::check($data['password'], $user->password)){
            return response()->json([
                'message' => 'Incorrect Password!!',
                'status' => false
            ],422);
        }
        $data = User::where('id', $request->id)->update([
            'userType' => 'teacher',
        ]);
        return response()->json([
            'success'=> true,
            'data'=>$data,
        ],200);
    }
}
