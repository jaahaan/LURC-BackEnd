<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Education;
use App\Models\Project;
use App\Models\Publication;
use App\Models\Skill;
use App\Models\Post;

use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    public function getProfileInfo($id)
    {
        return User::where('id', $id)->with('department')->first();
    }
    public function getEducation($id)
    {
        return Education::where('user_id', $id)->get();
    }

    public function updateProfile(Request $request, $id)
    {
        //validate request
        $this->validate($request, [
            'id' => 'required',
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
            return User::where('id', $id)->update([
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

    public function education(Request $request, $id)
    {    
        //validate request
        $this->validate($request, [
            'id' => 'required',
            'institute' => 'required',
            'degree' => 'required',
            'fieldOfStudy' => 'required',
            'startMonth' => 'required',
            'startYear' => 'required',
        ]);
        $data = $request->only('institute','degree','fieldOfStudy', 'startMonth', 'startYear','endMonth', 'endYear', 'grade', 'activities');
        $test['education'] = json_encode($data);
        $education = User::where('id', $id)->update($test);

        // $education = Education::create([
        //     'user_id' => $id,
        //     'institute' => $request->institute,
        //     'degree' => $request->degree,
        //     'fieldOfStudy' => $request->fieldOfStudy,
        //     'startDate' => $request->startMonth .' '. $request->startYear,
        //     'endDate' => $request->endMonth .' '. $request->endYear,
        //     'grade' => $request->grade,
        //     'activities' => $request->activities,
        // ]);    

        return response()->json(['msg' => 'Education Added Successfully.', 'status' => $education], 201);
    }

    public function skills(Request $request, $id)
    {
        //validate request
        $this->validate($request, [
            'id' => 'required',
            'skills' => 'required',
        ]);
        
        return User::where('id', $id)->update([
            'skills' => $request->skills,
        ]);
        
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
    public function getProject($id)
    {
        return Project::where('user_id', $id)->get();
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

    

    
    public function searchSkills(Request $request){
        $searchString= $request->keyword;
        $limit = $request->limit? $request->limit : 5;

        $skills = Skill::where('name', 'LIKE','%'.$searchString.'%')->limit($limit)->get();
        return response()->json($skills);
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
