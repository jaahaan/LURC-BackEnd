<?php

namespace App\Http\Controllers;
use App\Models\Theme;

use Illuminate\Http\Request;

class ThemeController extends Controller
{
    public function getTheme(){
        $theme = Theme::first();
        return $theme;
    }
    public function getThemeSetting(){
        return Theme::first();
    }
    public function themeSettingUpdate(Request $request){
        $validator = Validator::make($request->all(),[
            'theme_colour'=>'required',
            'background_color'=>'required',
            'font_color'=>'required',
            'font_hover_color'=>'required'
        ]);
        if($validator->fails()){
            return response()->json($validator->errors(), 422);
        }
        $data=[
            'theme_colour'=>$request->theme_colour,
            'background_color'=>$request->background_color,
            'font_color'=>$request->font_color,
            'font_hover_color'=>$request->font_hover_color,
        ];
        // $actionLogData=[
        //     'user_id'=>Auth::user()->id,
        //     'content'=> Auth::user()->name." Updated Company Theme Settings",
        //     'item_id'=>$request->id,
        //     'action_type'=>'Updated',
        //     'table_name'=>'settings',
        //     'date'=>date('Y-m-d H:i:s'),
        // ];
        // ActionLog::create($actionLogData);
        return Theme::where('id',$request->id)->update($data);
    }
}
