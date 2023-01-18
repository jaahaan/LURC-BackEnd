<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Education;
use App\Models\Project;
use App\Models\Publication;
use App\Models\Skill;
use App\Models\UserSkill;
use App\Models\Post;

use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Validator;

date_default_timezone_set('Asia/Dhaka');

class ProfileController extends Controller
{
    public function getProfileInfo($slug)
    {

        $user = User::where('slug', $slug)->with('department', 'user_skills')->first();
        $education = Education::where('user_id', $user->id)->orderBy('start_date', 'desc')->get();
        $formattedData = [];
        foreach($education as $value){            
            $value['start_date'] = date('M Y', strtotime($value->start_date));
            $value['end_date'] = date('M Y', strtotime($value->end_date));
            $value['edit_start_date'] = date('Y-m-d', strtotime($value->start_date));
            $value['edit_end_date'] = date('Y-m-d', strtotime($value->end_date));
            array_push($formattedData, $value);
            
        }
        return response()->json([
            'success'=> true,
            'education'=>$formattedData,
            'user'=>$user,
        ],200);
    }
    public function getAuthUserInfo(){
        return User::where('id', Auth::user()->id)->with('user_skills')->first();
    }

    public function updateProfile(Request $request)
    {
        //validate request
        $this->validate($request, [
            'name' => 'required',
            'designation' => 'required',
            'department' => 'required'
        ]);
        $image = $request->image;
        if(is_null($image)){
            return User::where('id', $id)->update([
                'name' => $request->name,
                'image' => 'http://localhost:8000/profileImages/download.jpg',
                'designation' => $request->designation,
                'department_id' => $request->department,
            ]);
        } else{
            return User::where('id', Auth::user()->id)->update([
                'name' => $request->name,
                'image' => $request->image,
                'designation' => $request->designation,
                'department_id' => $request->department,
            ]);
        }
    }

    public function about(Request $request, $id)
    {
        //validate request
        $this->validate($request, [
            'id' => 'required',
            'about' => 'required',
        ]);
        
        return User::where('id', $id)->update([
            'about' => $request->about,
        ]);
        
    }

    public function deleteAbout(Request $request, $id)
    {
        //validate request
        $this->validate($request, [
            'id' => 'required',
        ]);
        
        return User::where('id', $id)->update([
            'about' => null,
        ]);
        
    }

    //Education
    public function saveEducation(Request $request)
    {    
        //validate request
        
        $validator = Validator::make($request->all(),
        [
            'institute' => 'required',
            'degree' => 'required',
            'fieldOfStudy' => 'required',
            'start_date' => 'required',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(), 422);
        }

        $education = Education::create([
            'user_id' => Auth::user()->id,
            'institute' => $request->institute,
            'degree' => $request->degree,
            'fieldOfStudy' => $request->fieldOfStudy,
            'start_date' => date('Y-m-d H:i:s' , strtotime($request->start_date)),
            'end_date'=> date('Y-m-d H:i:s' , strtotime($request->end_date)),
            'grade' => $request->grade,
            'activities' => $request->activities,
        ]);    

        return response()->json(['msg' => 'Education Added Successfully.', 'status' => $education], 201);
    }

    public function updateEducation(Request $request){
        $validator = Validator::make($request->all(),
        [
            'institute' => 'required',
            'degree' => 'required',
            'fieldOfStudy' => 'required',
            'start_date' => 'required',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(), 422);
        }
        return Education::where('id', $request->id)->update([
            'institute' => $request->institute,
            'degree' => $request->degree,
            'fieldOfStudy' => $request->fieldOfStudy,
            'start_date' => date('Y-m-d H:i:s' , strtotime($request->start_date)),
            'end_date'=> date('Y-m-d H:i:s' , strtotime($request->end_date)),
            'grade' => $request->grade,
            'activities' => $request->activities,
        ]);
        // $update = Education::where('id',$request->id)->first();
        // return $update;
    }

    public function deleteEducation(Request $request){
        // return 'dine';
        return Education::where('id',$request->id)->delete();
    }
    //Search Skills
    public function searchSkills(Request $request){
        $searchString= $request->keyword;
        $limit = $request->limit? $request->limit : 5;

        $skills = Skill::where('name', 'LIKE','%'.$searchString.'%')->limit($limit)->get();
        return response()->json($skills);
    }
    
    //create skills
    public function saveSkills(Request $request)
    {
        $this->validate($request, [
            'skill_id' => 'required',
        ]);
        $skills = $request->skill_id;
        $skill_ids = [];

        DB::beginTransaction();
        try{
        // insert authors
        foreach ($skills as $s) {
            array_push($skill_ids, ['user_id' =>  Auth::user()->id, 'skill_id' => $s]);
        }
        UserSkill::insert($skill_ids);
        DB::commit();
        return response()->json(['msg' => 'Added Successfully.'], 200);
        } 

        catch (\Throwable $e) {
            DB::rollback();
            return response()->json(['msg' => 'Unsuccessfull!!'], 401);
        }   
    }
    
    // update skills
    public function updateSkills(Request $request)
    {
        $this->validate($request, [
            'skill_id' => 'required',
        ]);
        $skills = $request->skill_id;
        $skill_ids = [];
        DB::beginTransaction();
        try {
            // insert 
            foreach ($skills as $s) {
                array_push($skill_ids, ['user_id' =>  Auth::user()->id, 'skill_id' => $s]);
            }
            // delete all previous authors
            UserSkill::where('user_id', Auth::user()->id)->delete();
            UserSkill::insert($skill_ids);
            
            DB::commit();
            return response()->json(['msg' => 'Skills Updated Successfully.'], 200);
        } catch (\Throwable $e) {
            DB::rollback();
            return response()->json(['msg' => 'Unsuccessfully.'], 401);
        }
    }


    public function interests(Request $request, $id)
    {
        //validate request
        $this->validate($request, [
            'id' => 'required',
            'interests' => 'required',
        ]);
        
        return User::where('id', $id)->update([
            'interests' => $request->interests,
        ]);    
    }

    public function saveProject(Request $request, $id)
    {
        
        //validate request
        $this->validate($request, [
            'project_name' => 'required',
            'project_type' => 'required',
        ]);
       
        $project =  Project::create([
            'user_id' => $id,
            'project_name' => $request->project_name,
            'project_type' => $request->project_type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'project_url' => $request->project_url,
            'project_description' => $request->project_description,
        ]);   
        return response()->json(['msg' => 'Project Added Successfully.', 'status' => $project], 200);

    }
    
    public function updateProject(Request $request)
    {
        //validate request
        $this->validate($request, [
            'user_id' => 'required',
            'project_name' => 'required',
            'project_type' => 'required',
        ]);
        $update_project = Project::where('id', $request->id)->update([
            'user_id' => $request->user_id,
            'project_name' => $request->project_name,
            'project_type' => $request->project_type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'project_url' => $request->project_url,
            'project_description' => $request->project_description,
        ]);
        return response()->json(['msg' => 'Project Updateded Successfully.', 'status' => $update_project], 200);

    }

    public function deleteProject($id)
    {
        $delete_project = Project::where('id', $id)->delete();
        return response()->json(['msg' => 'Project Deleted Successfully.', 'status' => $delete_project], 200);

    }

    //image upload
    public function upload(Request $request)
    {
        $this->validate($request, [
            'file' => 'required|mimes:jpeg,jpg,png',
        ]);
        $picName = time() . '.' . $request->file->extension();
        $request->file->move(public_path('profileImages'), $picName);
        return $picName;
    }

    public function deleteImage(Request $request)
    {
        $fileName = $request->imageName;
        $filePath = public_path() . $fileName;
        \Log::info('$filePath');
        \Log::info($filePath);
        $default_image = 'http://localhost:8000/profileImages/download.jpg';
        if (file_exists($filePath)) {
            if($fileName!='http://localhost:8000/profileImages/download.jpg'){
                @unlink($filePath);
            }
        }
        // return 'default' . $default_image; 
        return;
        
    }
    
    public function getTeacherInfo()
    {
        return Teacher::get()->all();
    }

    public function addTeacher(Request $request)
    {    
        //validate request
        $this->validate($request, [
            'email' => [
                'required',
                'max:50',
                'email',
                'unique:teachers,email',
                'regex:/[a-z]+(_cse)?@lus\.ac\.bd/'
            ],
            'designation' => 'required',
            'department' => 'required',
        ],[
            'email.regex' => 'Please provide Institutional email!!',
        ]);
        $teacher = Teacher::create([
            'email' => $request->email,
            'department' => $request->department,
            'designation' => $request->designation,
        ]);    

        return response()->json(['msg' => 'Teacher Added Successfully.', 'status' => $teacher], 201);
    }
    public function editTeacher(Request $request)
    {    
        //validate request
        $this->validate($request, [
            'edit_id' => 'required',
            'email' => [
                'required',
                'max:50',
                'email',
                'unique:teachers,email',
                'regex:/[a-z]+(_cse)?@lus\.ac\.bd/'
            ],
            'designation' => 'required',
            'department' => 'required',
        ],[
            'email.regex' => 'Please provide Institutional email!!',
        ]);
        $teacher = Teacher::where('id', $request->edit_id)->update([
            'email' => $request->email,
            'department' => $request->department,
            'designation' => $request->designation,
        ]);    

        return response()->json(['msg' => 'Teacher updated Successfully.', 'status' => $teacher], 200);
    }


    // public function deleteFileFromServer($fileName)
    // {
    //     $filePath = public_path() . '/profileImages/' . $fileName;
    //     // return $filePath;
    //     // if (!$hasFullPath) {
    //     //     $filePath = public_path() . '/profileImages/' . $fileName;
    //     // }
    //     if (file_exists($filePath)) {
    //         @unlink($filePath);
    //     }
    //     return;
    // }

}
