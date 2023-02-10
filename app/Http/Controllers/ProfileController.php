<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Education;
use App\Models\Project;
use App\Models\Publication;
use App\Models\Skill;
use App\Models\UserSkill;
use App\Models\Post;
use App\Models\Vote;
use App\Models\Read;
use App\Models\Like;

use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Validator;

date_default_timezone_set('Asia/Dhaka');

class ProfileController extends Controller
{
    public function getUser(Request $request)
    {
        return User::get();
    }
    public function getProfileHeaderInfo($slug)
    {

        $user = User::where('slug', $slug)->with('department')->first();
        unset($user['about']);
        unset($user['interests']);
        unset($user['isActive']);
        unset($user['passwordToken']);
        unset($user['token_expired_at']);
        unset($user['honors_and_awards']);
        unset($user['updated_at']);
        unset($user['created_at']);
        return response()->json([
            'success'=> true,
            'user'=>$user,
        ],200);
    }
    public function getProfileInfo($slug)
    {

        $user = User::where('slug', $slug)->with('department', 'user_skills')->first();
        $education = Education::where('user_id', $user->id)->orderBy('start_date', 'desc')->get();
        $formattedData = [];
        foreach($education as $value){
            if($value['start_date'] != null){
                $value['start_date'] = date('M Y', strtotime($value->start_date));
                $value['edit_start_date'] = date('Y-m-d', strtotime($value->start_date));
            }            
            if($value['end_date'] != null){
                $value['end_date'] = date('M Y', strtotime($value->end_date));
                $value['edit_end_date'] = date('Y-m-d', strtotime($value->end_date));
            }  
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

    public function updateAbout(Request $request)
    {
        //validate request
        $this->validate($request, [
            'about' => 'required',
        ]);
        
        return User::where('id', Auth::user()->id)->update([
            'about' => $request->about,
        ]);
        
    }


    public function deleteAbout(Request $request)
    {
        return User::where('id', Auth::user()->id)->update([
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
            'start_date' => $request->start_date,
            'end_date'=> $request->end_date,
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
            'start_date' => $request->start_date,
            'end_date'=> $request->end_date,
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

    //Skills
    public function searchSkills(Request $request){
        $searchString= $request->keyword;
        $limit = $request->limit? $request->limit : 5;

        $skills = Skill::where('name', 'LIKE','%'.$searchString.'%')->limit($limit)->get();
        return response()->json($skills);
    }
    public function getSkills(Request $request){
        return Skill::get();
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
    
    
    //User Research Items
    public function getUserResearch(Request $request)
    {
        $user = User::where('slug', $request->slug)->first();
        $limit = $request->limit? $request->limit : 3;
        $data =  Post::where('user_id', $user->id)->where('type','!=', 'project')->with(['user', 'read', 'vote', 'like', 'authors', 'images'])->orderBy('id', 'desc')->limit($limit)->get();
        $formattedData = [];
        foreach($data as $value){
            $post = $value;
            $check = Read::where(['post_id'=>$post->id])->first();
            $voteCheck = Vote::where(['post_id'=>$post->id])->first();
            
            $checkUpVote = Vote::where(['user_id'=>Auth::user()->id,'post_id'=>$post->id, 'upVote'=>1])->first();
            $checkDownVote = Vote::where(['user_id'=>Auth::user()->id,'post_id'=>$post->id, 'downVote'=>1])->first();
            $likecheck = Like::where(['post_id'=>$post->id])->first();
            $AuthLikeCheck = Like::where(['user_id'=>Auth::user()->id,'post_id'=>$post->id])->first();

            if($checkUpVote){
                $post['authUserVote']= "up";
            } if($checkDownVote){
                $post['authUserVote']= "down";
            } if(!$checkUpVote && !$checkDownVote){
                $post['authUserVote']= "none";
            } if($likecheck){
                $post['like_count'] =$post->like->like_count;
            } if($AuthLikeCheck){
                $post['authUserLike'] = 'yes';
            } 
            if(!$likecheck){
                $post['like_count'] = 0;
            } if(!$AuthLikeCheck){
                $post['authUserLike'] = 'no';
            } 
            if(!$check){
                $post['read_count'] = 0;
            } 
            if($check){
                $post['read_count'] = $post->read->read_count;
            } if(!$voteCheck){
                $post['upVote'] = 0;
                $post['downVote'] = 0;
                $post['avgVote'] = 0;
            }  if($voteCheck){
                $post['upVote'] = $post->vote->upVote;
                $post['avgVote'] = $post->vote->upVote - $post->vote->downVote;
                $post['downVote'] = $post->vote->downVote;
            }
            $post['image'] = $post->user->image;
            $post['name'] = $post->user->name;
            $post['user_slug'] = $post->user->slug;
            $post['department'] = $post->user->department;
            $post['designation'] = $post->user->designation;
            if($post['publication_date']!=null)
            {
                $post['publication_date'] = date('M Y', strtotime($post->publication_date));
                $post['edit_publication_date'] = date('Y-m', strtotime($post->publication_date));
            }
            
            // $post['start_date'] = date('M Y', strtotime($post->start_date));
            // $post['end_date'] = date('M Y', strtotime($post->end_date));
            $post['formatedDateTime'] = date('M Y', strtotime($post->created_at));

            
            unset($post['vote']);
            unset($post['user']);
            unset($post['read']);
            unset($post['like']);

            array_push($formattedData, $post);

        }
        return response()->json([
            'success'=> true,
            'data'=>$formattedData,
        ],200);
    }

    //User Projects
    public function getUserProject(Request $request, $slug)
    {
        $user = User::where('slug',$slug)->first();
        $limit = $request->limit? $request->limit : 5;
        $data =  Post::where('user_id', $user->id)->where('type', 'project')->with(['user', 'read', 'vote', 'like', 'authors', 'images'])->orderBy('id', 'desc')->limit($limit)->get();

        $formattedData = [];
        
        foreach($data as $value){
            $post = $value;
            $check = Read::where(['post_id'=>$post->id])->first();
            $voteCheck = Vote::where(['post_id'=>$post->id])->first();
            
            $checkUpVote = Vote::where(['user_id'=>Auth::user()->id,'post_id'=>$post->id, 'upVote'=>1])->first();
            $checkDownVote = Vote::where(['user_id'=>Auth::user()->id,'post_id'=>$post->id, 'downVote'=>1])->first();
            $likecheck = Like::where(['post_id'=>$post->id])->first();
            $AuthLikeCheck = Like::where(['user_id'=>Auth::user()->id,'post_id'=>$post->id])->first();

            if($checkUpVote){
                $post['authUserVote']= "up";
            } if($checkDownVote){
                $post['authUserVote']= "down";
            } if(!$checkUpVote && !$checkDownVote){
                $post['authUserVote']= "none";
            } if($likecheck){
                $post['like_count'] =$post->like->like_count;
            } if($AuthLikeCheck){
                $post['authUserLike'] = 'yes';
            } 
            if(!$likecheck){
                $post['like_count'] = 0;
            } if(!$AuthLikeCheck){
                $post['authUserLike'] = 'no';
            } 
            if(!$check){
                $post['read_count'] = 0;
            } 
            if($check){
                $post['read_count'] = $post->read->read_count;
            } if(!$voteCheck){
                $post['upVote'] = 0;
                $post['downVote'] = 0;
                $post['avgVote'] = 0;
            }  if($voteCheck){
                $post['upVote'] = $post->vote->upVote;
                $post['avgVote'] = $post->vote->upVote - $post->vote->downVote;
                $post['downVote'] = $post->vote->downVote;
            }
            $post['image'] = $post->user->image;
            $post['name'] = $post->user->name;
            $post['user_slug'] = $post->user->slug;
            $post['department'] = $post->user->department;
            $post['designation'] = $post->user->designation;
            if($post['start_date']!=null){
                $post['start_date'] = date('M Y', strtotime($post->start_date));
                $post['edit_start_date'] = date('Y-m', strtotime($post->start_date));
            }
            if($post['end_date']!=null){
                $post['end_date'] = date('M Y', strtotime($post->end_date));
                $post['edit_end_date'] = date('Y-m', strtotime($post->end_date));
            }
            
            // $post['edit_start_date'] = date('Y-m', strtotime($post->start_date));
            // $post['edit_end_date'] = date('Y-m', strtotime($post->end_date));
            $post['formatedDateTime'] = date('M Y', strtotime($post->created_at));
            unset($post['vote']);
            unset($post['user']);
            unset($post['read']);
            unset($post['like']);

            array_push($formattedData, $post);

        }
        return response()->json([
            'success'=> true,
            'data'=>$formattedData,
        ],200);
    }
}
