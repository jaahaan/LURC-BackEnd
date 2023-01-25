<?php

namespace App\Http\Controllers;
use App\Models\Teacher;

use Illuminate\Http\Request;
date_default_timezone_set('Asia/Dhaka');

class TeacherController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function GetTeachers()
    {
        $data=Teacher::get(); 
        return response()->json([
            'success'=> true,
            'data'=>$data,
        ],200);
    }

    public function AddTeacher(Request $request){
        $this->validate($request, [
            'email' => [
                'required',
                'max:50',
                'email',
                'unique:teachers,email',
                'regex:/[a-z]?@lus\.ac\.bd/'
            ],
            'designation' => 'required',
            'department' => 'required',
        ]);
        $data = $request->all();
        return Teacher::create($data);
    }
    public function TeacherUpdate(Request $request){
        Teacher::where('id',$request->id)->update($request->all());
        $update=Teacher::where('id',$request->id)->first();
        return $update;

    }
    public function TeacherDel(Request $request){
        // return 'dine';
        return Teacher::where('id',$request->id)->delete();
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}