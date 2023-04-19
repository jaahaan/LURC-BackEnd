<?php

namespace App\Http\Controllers;
use App\Models\Banner;
use Illuminate\Http\Request;
use Validator;
use DB;
class BannerController extends Controller
{
    public function AddBanner(Request $request){
        $validator = Validator::make($request->all(),
        [
            'images' => 'array|min:1'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(), 422);
        }


        $images = $request->images;
        $Banners = [];
        DB::beginTransaction();
        try{

        foreach ($images as $i) {
            array_push($Banners, ['image' => $i, 'type' => 'slider']);
        }
        Banner::insert($Banners);
        DB::commit();
        return response()->json(['msg' => 'Added Successfully.', 'status' => $Banners], 200);
        } 

        catch (\Throwable $e) {
            DB::rollback();
            return response()->json(['msg' => 'Unsuccessfull!!'.$e], 401);
        }  
    }
    public function GetBanners(Request $request)
    {
        $limit = $request->limit? $request->limit : 8;

        $data = Banner::limit($limit)->orderby('order_no','asc')->get();
        return response()->json([
            'success'=> true,
            'data'=>$data
        ],200);
    }

    public function ActiveBanner(Request $request)
    {
        $count = Banner::where('isActive', 1)->count();
        if($request->status == 1){
            if($count<15){
                $data = Banner::where('id', $request->id)->update([
                    'isActive' => $request->status,
                ]);
                return response()->json([
                    'success'=> true,
                    'data'=>$data
                ],200);
            }
            else{
                return response()->json([
                    'success'=> false,
                    'msg'=> 'Sorry you can not active more then 15 banners!!'
                ],401);
            }
        } else {
            $data = Banner::where('id', $request->id)->update([
                'isActive' => $request->status,
            ]);
            return response()->json([
                'success'=> true,
                'data'=>$data
            ],200);
           
        } 
    }

    public function DelBanner(Request $request){
        // return 'dine';
        return Banner::where('id',$request->id)->delete();
    }

    public function LandingBanners(Request $request)
    {
        $data = Banner::where('isActive', 1)->orderby('order_no','asc')->get();
        return response()->json([
            'success'=> true,
            'data'=>$data
        ],200);
    }

    public function getAllBanners(Request $request){
        $page = $request->page;
        $firstItem = $request->firstItem;
        $lastItem = $request->lastItem;
        if($page==1){
            return Banner::where('isActive',1)->orderby('order_no','asc')->get();
        }
        return Banner::where('is_active',1)->where('order_no','>=',$lastItem)->orderby('order_no','asc')->get();
    }
    public function resetAllBanner(Request $request){
        $data = $request->all();
        foreach($data as $item){
            Banner::where('id',$item['id'])->update(['order_no'=>$item['order_no']]);
        }
        return 'updated';
    }
}
