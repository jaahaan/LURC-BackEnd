<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Stroage;

use App\Models\User;
use App\Models\Post;
use App\Models\Vote;
use App\Models\Read;
use App\Models\Like;
use App\Models\Comment;
use App\Models\Connection;
use App\Models\Notification;

use App\Models\Author;
use App\Models\Attachment;
use Illuminate\Support\Facades\DB;
use Auth;
use DateTime;
use App\Notifications\PostNotification;

date_default_timezone_set('Asia/Dhaka');


class PostController extends Controller
{
    public function getAllPost(Request $request){

        $limit = $request->limit? $request->limit : 4;

        $data =  Post::with(['user', 'read', 'vote', 'like', 'authors', 'attachments'])->orderBy('id', 'desc')->limit($limit)->get();
        // $data =  Post::with(['user', 'read', 'vote', 'like', 'authors', 'attachments'])->inRandomOrder()->limit($limit)->get();
        



        $formattedData = [];
        foreach($data as $post){
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
            } if(!$likecheck){
                $post['like_count'] = 0;
            } if(!$AuthLikeCheck){
                $post['authUserLike'] = 'no';
            } 

            $post['image'] = $post->user->image;
            $post['name'] = $post->user->name;
            $post['user_slug'] = $post->user->slug;
            $post['department'] = $post->user->department;
            $post['designation'] = $post->user->designation;
            $post['start_date'] = date('M Y', strtotime($post->start_date));
            $post['end_date'] = date('M Y', strtotime($post->end_date));
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
            $connected1 = Connection::with('user1', 'user2')
                    ->where('sent_request_user', Auth::user()->id)
                    ->where('received_request_user', $post->user_id)
                    ->where('connected', 1)->first();
            $connected2 = Connection::with('user1', 'user2')
                    ->where('sent_request_user', $post->user_id)
                    ->where('received_request_user', Auth::user()->id)
                    ->where('connected', 1)->first();
            if($connected1 || $connected2 || $post->user_id == Auth::user()->id){
                array_push($formattedData, $post);
            }
        }
        return response()->json([
            'success'=> true,
            'data'=>$formattedData,
        ],200);
    }

    public function postDetails($slug){
        $data = Post::where('slug',$slug)->with('user','read', 'vote', 'like', 'authors', 'attachments')->first();
        $check = Read::where(['post_id'=>$data->id])->first();
        $voteCheck = Vote::where(['post_id'=>$data->id])->first();
        if(Auth::check()){
            $checkUpVote = Vote::where(['user_id'=>Auth::user()->id,'post_id'=>$data->id, 'upVote'=>1])->first();
            $checkDownVote = Vote::where(['user_id'=>Auth::user()->id,'post_id'=>$data->id, 'downVote'=>1])->first();
            $likecheck = Like::where(['post_id'=>$data->id])->first();
            $AuthLikeCheck = Like::where(['user_id'=>Auth::user()->id,'post_id'=>$data->id])->first();

            if($checkUpVote){
                $data['authUserVote']= "up";
            } if($checkDownVote){
                $data['authUserVote']= "down";
            } if(!$checkUpVote && !$checkDownVote){
                $data['authUserVote']= "none";
            } if($likecheck){
                $data['like_count'] =$data->like->like_count;
            } if($AuthLikeCheck){
                $data['authUserLike'] = 'yes';
            } 
            if(!$likecheck){
                $data['like_count'] = 0;
            } if(!$AuthLikeCheck){
                $data['authUserLike'] = 'no';
            } 
        }
        $formattedData = [];

        $data['image'] = $data->user->image;
        $data['name'] = $data->user->name;
        $data['user_slug'] = $data->user->slug;
        $data['department'] = $data->user->department;
        $data['designation'] = $data->user->designation;
        //$data['commented_user'] = $data->comments->user->name;

    	if (!$check) {
            $data['read_count'] = 0;
    	} if($check){
            $data['read_count'] = $data->read->read_count;
        } if(!$voteCheck){
            $data['upVote'] = 0;
            $data['downVote'] = 0;
            $data['avgVote'] = 0;
        }  if($voteCheck){
            $data['upVote'] = $data->vote->upVote;
            $data['downVote'] = $data->vote->downVote;
            $data['avgVote'] = $data->vote->upVote - $data->vote->downVote;
        }
        unset($data['vote']);
        unset($data['like']);
        unset($data['user']);
        unset($data['read']);

        array_push($formattedData, $data);
        return response()->json([
            'success'=> true,
            'data'=>$formattedData,
        ],200);
    }

    public function postAbstract($slug){
        $data = Post::where('slug',$slug)->first();
    
        $formattedData = [];
        unset($data['user_name']);
        unset($data['department_id']);
        unset($data['user_id']);
        unset($data['slug']);
        unset($data['title']);
        unset($data['url']);
        unset($data['affiliation']);
        unset($data['attachment']);
        unset($data['conference']);
        unset($data['publication_date']);
        unset($data['isApproved']);
        unset($data['approved_at']);
        unset($data['end_date']);
        unset($data['start_date']);
        unset($data['updated_at']);
        unset($data['created_at']);
        
        array_push($formattedData, $data);
        return response()->json([
            'success'=> true,
            'data'=>$formattedData,
        ],200);
    }

    //create post
    public function savePost(Request $request)
    {
        $this->validate($request, [
            'type' => 'required',
            'title' => 'required',
        ]);
        $authors = $request->author_id;
        $images = $request->images;
        $publicationAuthors = [];
        $postImages = [];

        DB::beginTransaction();
        try{
        $post = Post::create([
            'user_id' => Auth::user()->id,
            'user_name' => Auth::user()->name,
            'department_id' => Auth::user()->department_id,
            'type' => $request->type,
            'title' => $request->title,
            'abstract' => $request->abstract,
            'url' => $request->url,
            'affiliation'=> $request->affiliation,
            'attachment'=> $request->attachment,
            'publication_date' => date('Y-m-d H:i:s' , strtotime($request->publication_date)),
            'start_date' => date('Y-m-d H:i:s' , strtotime($request->start_date)),
            'end_date'=> date('Y-m-d H:i:s' , strtotime($request->end_date)),
            
        ]);

        // insert authors
        foreach ($authors as $a) {
            array_push($publicationAuthors, ['post_id' => $post->id, 'user_id' => $a]);
        }
        Author::insert($publicationAuthors);
        // Attachment::insert(['post_id' => $post->id,'type' => 'pdf', 'url' => $request->attachment]);
        // insert images
        // foreach ($images as $i) {
        //     array_push($postImages, ['post_id' => $post->id,'type' => 'image', 'attachments' => $i]);
        // }
        // Attachment::insert($postImages);
        DB::commit();
        return response()->json(['msg' => 'Added Successfully.', 'status' => $post], 200);
        } 

        catch (\Throwable $e) {
            DB::rollback();
            return response()->json(['msg' => 'Unsuccessfull!!'.$e], 401);
        }   
    }

    //upload attachment
    public function uploadAttachment(Request $request)
    {
        // $this->validate($request, [
        //     'file' => 'required|mimes:jpeg,jpg,png,pdf,pptx',
        // ]);
        // $fileName = time() . '.' . $request->file->extension();
        $fileName = time() . '_' . $request->file->getClientOriginalName();
        $request->file->move('attachments', $fileName);
        return $fileName;
    }

    public function deleteAttachment(Request $request)
    {
        $fileName = $request->Name;
        \Log::info($fileName);
        $this->deleteFileFromServer($fileName, false);
        return 'done';
    }
    public function deleteFileFromServer($fileName, $hasFullPath = false)
    {
        if (!$hasFullPath) {
            $filePath = public_path('attachments') .'\\'. $fileName;
            \Log::info($filePath);
        }
        if (file_exists($filePath)) {
            @unlink($filePath);
        }
        return;
    }
    // update post
    public function updatePost(Request $request)
    {
        $this->validate($request, [
            'type' => 'required',
            'title' => 'required',
        ]);
        $authors = $request->author_id;
        $images = $request->images;
        $publicationAuthors = [];
        $postImages = [];
        DB::beginTransaction();
        // try {
            $post = Post::where('id', $request->id)->update([
                'user_id' => $request->user_id,
                'type' => $request->type,
                'title' => $request->title,
                'abstract' => $request->abstract,
                'url' => $request->url,
                'affiliation'=> $request->affiliation,
                'start_date' => date('Y-m-d H:i:s' , strtotime($request->start_date)),
                'end_date'=> date('Y-m-d H:i:s' , strtotime($request->end_date)),
                ]);
            // insert 
            foreach ($authors as $a) {
                array_push($publicationAuthors, ['post_id' => $request->id, 'user_id' => $a]);
            }
            // delete all previous authors
            Author::where('post_id', $request->id)->delete();
            Author::insert($publicationAuthors);
            // insert 
            // foreach ($images as $i) {
            //     array_push($postImages, ['post_id' => $post->id,'type' => 'image', 'attachments' => $i]);
            // }
            // Attachment::where('post_id', $id)->delete();
            // Attachment::insert($postImages);
            DB::commit();
            return response()->json(['msg' => 'Updated Successfully.', 'status' => $post], 200);
        // } catch (\Throwable $e) {
        //     DB::rollback();
        //     return response()->json(['msg' => 'Unsuccessfully.'], 401);
        // }
    }

    public function deletePost(Request $request)
    {
        $delete_post = Post::where('id', $request->id)->delete();
        return response()->json(['msg' => 'Post Deleted Successfully.', 'status' => $delete_post], 200);
    }

    public function upVote(Request $request){
        $checkUpVote = Vote::where(['user_id'=>Auth::user()->id,'post_id'=>$request->id, 'upVote'=>$request->upVote])->first();
        $checkDownVote = Vote::where(['user_id'=>Auth::user()->id,'post_id'=>$request->id, 'downVote'=>$request->upVote])->first();
        $post = Post::where('id',$request->id)->first();

        \Log::info('check');
        \Log::info($checkUpVote);

    	if ($checkUpVote) {
            $count = $post->count - 1;
            Post::where('id', $post->id)->update([
                'count' => $count,
            ]);
    		$data = Vote::where(['user_id'=>Auth::user()->id,'post_id'=>$request->id, 'upVote'=>$request->upVote])->delete();
            Notification::where(['data->user_id'=>Auth::user()->id,'data->post_id'=>$request->id])->delete();

    		return response()->json([
                'success'=> true,
                'data'=> 'del',
            ],200);

    	} else if($checkDownVote){
            $count = $post->count + 2;
            Post::where('id', $post->id)->update([
                'count' => $count,
            ]);
    		Vote::where(['user_id'=>Auth::user()->id,'post_id'=>$request->id, 'downVote'=>$request->upVote])->delete();
            Notification::where(['data->user_id'=>Auth::user()->id,'data->post_id'=>$request->id])->delete();

            $authUser = Auth::user();
            $postUser = $post->user;
            $msg = "up voted your";
            $postUser->notify(new PostNotification($authUser, $post, $msg));
            
            $data = Vote::create([
                'user_id' => Auth::user()->id,
	    	    'post_id' => $request->id,
                'upVote'=> $request->upVote,
            ]);
            return response()->json([
                'success'=> true,
                'data'=> 'del_up',
            ],201);
        } else{
            $count = $post->count + 1;
            Post::where('id', $post->id)->update([
                'count' => $count,
            ]);
	    	$data = Vote::create([
                'user_id' => Auth::user()->id,
	    	    'post_id' => $request->id,
                'upVote'=> $request->upVote,
            ]);
            $authUser = Auth::user();
            $postUser = $post->user;
            $msg = "up voted your";
            $postUser->notify(new PostNotification($authUser, $post, $msg));
            
            return response()->json([
                'success'=> true,
                'data'=> 'up',
            ],202);
    	}
        
        
    }

    public function downVote(Request $request){
        $checkDownVote = Vote::where(['user_id'=>Auth::user()->id,'post_id'=>$request->id, 'downVote'=>$request->downVote])->first();
        $checkUpVote = Vote::where(['user_id'=>Auth::user()->id,'post_id'=>$request->id, 'upVote'=>$request->downVote])->first();
        $post = Post::where('id',$request->id)->first();

        \Log::info('$check');
        \Log::info($checkDownVote);

    	if ($checkDownVote) {
    		$data = Vote::where(['user_id'=>Auth::user()->id,'post_id'=>$request->id, 'downVote'=>$request->downVote])->delete();
            Notification::where(['data->user_id'=>Auth::user()->id,'data->post_id'=>$request->id])->delete();
            $count = $post->count + 1;
            \Log::info('count');
            \Log::info($count);
            Post::where('id', $post->id)->update([
                'count' => $count,
            ]);
              
    		return response()->json([
                'success'=> true,
                'data'=> 'del',
            ],200);
    	} else if($checkUpVote){
    		Vote::where(['user_id'=>Auth::user()->id,'post_id'=>$request->id, 'upVote'=>$request->downVote])->delete();
            $count = $post->count - 2;
            Post::where('id', $post->id)->update([
                'count' => $count,
            ]);
            
	    	$data = Vote::create([
                'user_id' => Auth::user()->id,
	    	    'post_id' => $request->id,
                'downVote'=> $request->downVote,
            ]);
            $authUser = Auth::user();
            $postUser = $post->user;
            $msg = "down voted your";
            $postUser->notify(new PostNotification($authUser, $post, $msg));
            
            return response()->json([
                'success'=> true,
                'data'=> 'del_up',
            ],201);
    	} else{
            $count = $post->count - 1;
            Post::where('id', $post->id)->update([
                'count' => $count,
            ]);
            
            $data = Vote::create([
                'user_id' => Auth::user()->id,
	    	    'post_id' => $request->id,
                'downVote'=> $request->downVote,
            ]);
            $authUser = Auth::user();
            $postUser = $post->user;
            $msg = "down voted your";
            $postUser->notify(new PostNotification($authUser, $post, $msg));
            
            return response()->json([
                'success'=> true,
                'data'=> 'up',
            ],202);
        }
        
        
    }

    public function read($id){
        $check = Read::where(['user_id'=>Auth::user()->id,'post_id'=>$id])->first();
        $post = Post::where('id', $id)->first;
    	if (!$check) {
            $count = $post->count + 1;
            Post::where('id', $post->id)->update([
                'count' => $count,
            ]);
    		return Read::create([
                'user_id' => Auth::user()->id,
	    	    'post_id' => $id,
            ]);
    	}
    }

    public function like(Request $request){
        $checkLike = Like::where(['user_id'=>Auth::user()->id,'post_id'=>$request->id])->first();

        $post = Post::where('id',$request->id)->first();

    	if ($checkLike) {
    		Like::where(['user_id'=>Auth::user()->id,'post_id'=>$request->id])->delete();
            Notification::where(['data->user_id'=>Auth::user()->id,'data->post_id'=>$request->id])->delete();
            $count = $post->count - 1;
            Post::where('id', $post->id)->update([
                'count' => $count,
            ]);
    		return 'deleted';
    	} else{
            $authUser = Auth::user();
            $postUser = $post->user;
            $msg = "liked your";
            $postUser->notify(new PostNotification($authUser, $post, $msg));
            $count = $post->count + 1;
            Post::where('id', $post->id)->update([
                'count' => $count,
            ]);
            
            return Like::create([
                'user_id' => Auth::user()->id,
	    	    'post_id' => $request->id,
            ]);            
        }        
    }

    public function getLikedUser(Request $request){
        \Log::info($request);
        $data = Like::where('post_id',$request->id)->with('user')->get();
        
        $formattedData = [];

        foreach($data as $value){
            $LikedUser = $value;

            $LikedUser['image'] = $LikedUser->user->image;
            $LikedUser['name'] = $LikedUser->user->name;
            $LikedUser['user_slug'] = $LikedUser->user->slug;
            unset($LikedUser['user']);
            array_push($formattedData, $LikedUser);
        }
        return response()->json([
            'success'=> true,
            'data'=>$formattedData,
        ],200);
        
    }
    
    public function downloadAttachment(Request $request, $url){
        \Log::info('$url');

        \Log::info($url);

        return response()->download(public_path('attachments/'. $url));
        
        // return $download;
    }

    public function viewAttachment($id){
        \Log::info($id);
        $data = Post::find($id);
        return redirect('/viewPost');

        return view('welcome', compact('data')) ;

    }

    
}
