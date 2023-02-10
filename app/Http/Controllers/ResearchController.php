<?php

namespace App\Http\Controllers;
use App\Models\Post;
use App\Models\Vote;
use App\Models\Read;
use App\Models\Like;
use Auth;

// use App\Models\Comment;
// use App\Models\Author;
// use App\Models\Attachment;
use Illuminate\Http\Request;
date_default_timezone_set('Asia/Dhaka');

class ResearchController extends Controller
{
    public function getAllResearch(Request $request)
    {
        $search = $request->search;
        $userId = $request->user;
        $departmentName=  $request->department;
        $limit = $request->limit? $request->limit : 5;
        $default = $request->default;
        $order=$request->order;

        $query =  Post::where('type', '!=', 'post')->with('user', 'read', 'vote', 'like', 'authors', 'department', 'images');

        if($search){
            $query->where(function ($queryy) use ($search){
                $queryy->where('user_name',  'like', "%$search%")
                ->orWhere('type', 'like', "%$search%")
                 ->orWhere('title', 'like', "%$search%");
            });
        }
        
        if($departmentName){
            $query->where('department_id', $departmentName);
        }      
        if($default && $order){
            $query->orderBy($default, $order);
        }

        $data = $query->limit($limit)->get();

        $formattedData = [];
        foreach($data as $value){
            $post = $value;
            $check = Read::where(['post_id'=>$post->id])->first();
            $voteCheck = Vote::where(['post_id'=>$post->id])->first();
            if(Auth::check()){
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
            }
            

            $post['image'] = $post->user->image;
            $post['name'] = $post->user->name;
            $post['user_slug'] = $post->user->slug;
            $post['department'] = $post->user->department;
            $post['designation'] = $post->user->designation;
            $post['formatedDateTime'] = date('M Y', strtotime($post->created_at));

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
            unset($post['vote']);
            unset($post['user']);
            unset($post['read']);
            unset($post['like']);
            unset($post['department']);

            array_push($formattedData, $post);

        }
        return response()->json([
            'success'=> true,
            'data'=>$formattedData,
        ],200);
    }

    public function getRelatedResearch(Request $request)
    {

        // $post= Post::where('slug', $request->slug)->first();
        $query =  Post::with('authors')->where('slug','!=', $request->slug);
        $limit = $request->limit? $request->limit : 4;
        
        $data = $query->where('type', $request->type)->limit($limit)->get();

        $formattedData = [];
        foreach($data as $value){
            $post = $value;
            unset($post['abstract']);
            unset($post['start_date']);
            unset($post['url']);
            unset($post['user_id']);
            unset($post['approved_at']);
            unset($post['attachment']);
            unset($post['department_id']);
            unset($post['end_date']);
            unset($post['isApproved']);
            unset($post['publication_date']);
            unset($post['count']);
            unset($post['user_id']);
            unset($post['updated_at']);
            unset($post['created_at']);

            array_push($formattedData, $post);
        }
        return response()->json([
            'success'=> true,
            'data'=>$formattedData,
        ],200);
    }
}
