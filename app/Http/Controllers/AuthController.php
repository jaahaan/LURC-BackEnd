<?php

namespace App\Http\Controllers;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Post;
use App\Models\Vote;
use App\Models\Read;
use App\Models\Like;
use App\Models\Connection;
use App\Models\Notification;
use App\Models\Notify;
use App\Models\CommentLike;
use App\Models\Comment;
use App\Models\CommentReply;
use App\Models\CommentReplyLike;
use App\Models\Author;
use App\Models\Image;
use App\Models\Conversation;
use App\Models\ConversationChat;
use App\Models\Education;
use App\Models\UserSkill;
use Auth;
use Validator;
use Illuminate\Support\Facades\Hash;
use DateTime;

date_default_timezone_set('Asia/Dhaka');

class AuthController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::check() && $request->path() != '/') {
            return redirect('/');
        }

        if (!Auth::check()) {
            return view('welcome');
        }

        if (Auth::check() && ($request->path() == 'login' || $request->path() == 'register' || $request->path() == '/')) {
            return redirect('/home');
        }
        return view('welcome');
    }
    
    function authUser(){
        try {
            \Log::info('I am in Auth try');
            $user_id = Auth::user()->id;
            $data = User::where('id', $user_id)->first();
            return $data;
        } catch (\Throwable $th) {
            \Log::info('I am in Auth catch');
            return response()->json([
                'msg'=>'Auth not found'
            ], 401);
        }

    }
    //create new teacher in db
    function register_t(Request $request)
    {
        //return $request->input();
        $validator = Validator::make($request->all(),
        [
            'name' => 'bail|required|regex:/^[a-zA-z. ]+$/',
            'email' => [
                'required',
                'max:50',
                'email',
                'unique:users,email',
                'exists:teachers,email',
                'regex:/[a-z]+(_cse|_eee|_ce|_arch|_eng)?@lus\.ac\.bd/'
            ],
            'password' => ['required',
               'min:8',
               'max:20',
               'regex:/^.*((?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*[!@#$%&*<+_-])).*$/',
               'confirmed'],
            'password_confirmation' => 'required',
            'designation' => 'required',
            'department' => 'required',  
        ],
        [
            'name.regex' => 'Only Characters are allowed!!',
            'email.regex' => 'Please provide your Institutional email!!',
            'email.exists' => 'This is not a teacher email!!',
            'password.regex' => '1 Upper, 1 Lower, 1 Digit, 1 Special Character'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(), 422);
        }

        $passwordToken = rand(100000, 999999);
        $token_expired_at = now();
        // $name = $request->name;
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'department_id' => $request->department,
            'designation' => $request->designation,
            'userType' => 'teacher',
            'passwordToken' => $passwordToken,
            'token_expired_at' => $token_expired_at,
        ]);
        $body = 'You have created an <b>LURC<b> account associated with ' . $request->email . '. Your OTP for Email verification is: ' . $passwordToken;

        \Mail::send('email-template', ['body' => $body], function ($message) use ($request) {
            $message->to($request->email)
                ->from('noreply@lurc.com', 'LURC')
                ->subject('Email Verification');
        });

        return response()->json(['msg' => 'Registered successfully. We have sent an OTP to your email. Submit your OTP to verify your account. To login, wait for the approval of admin.', 'status' => $user], 201);
    }

    //create new student in db
    function register_s(Request $request)
    {

        $validator = Validator::make($request->all(),
        [
            'name' => 'bail|required|regex:/^[a-zA-z. ]+$/',
            'email' => [
                'required',
                'max:50',
                'email',
                'unique:users,email',
                'regex:/(cse|eee|ce|arch|law|bba|eng|bng)_\d{10}@lus\.ac\.bd/'
            ],
            // 'password' => 'bail|required|confirmed|min:2|max:20',
            'password' => ['required',
               'min:8',
               'max:20',
               'regex:/^.*((?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*[!@#$%&*<+_-])).*$/',
               'confirmed'],
            'password_confirmation' => 'required',
            'batch' => 'required',
            'department' => 'required',  
        ],
        [
            'name.regex' => 'Only Characters are allowed!!',
            'email.regex' => 'Please provide your Institutional email!!',
            'password.regex' => '1 Upper, 1 Lower, 1 Digit, 1 Special Character'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(), 422);
        }
        
        $passwordToken = rand(100000, 999999);
        $token_expired_at = now();
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'department_id' => $request->department,
            'designation' => 'Student',
            'userType' => 'student',
            'passwordToken' => $passwordToken,
            'token_expired_at' => $token_expired_at,
        ]);

        $body = 'You have created an <b>LURC<b> account associated with ' . $request->email . '. Your OTP for Email verification is: ' . $passwordToken;

        \Mail::send('email-template', ['body' => $body], function ($message) use ($request) {
            $message->to($request->email)
                ->from('noreply@lurc.com', 'LURC')
                ->subject('Email Verification');
        });

        return response()->json(['msg' => 'Registered successfully. We have sent an OTP to your email. Submit your OTP to verify your account.', 'status' => $user], 201);
    }

    //resend Otp
    public function resendOtp(Request $request){
        $validator = Validator::make($request->all(),
        [
            'email' => 'required',
            'password' => 'required',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(), 422);
        }
    
        
        $passwordToken = rand(100000, 999999);
        $token_expired_at = now();
        $user = User::where('email', $request->email)->update([
            'passwordToken' => $passwordToken,
            'token_expired_at' => $token_expired_at,
        ]);

        $body = 'You have created an <b>LURC<b> account associated with ' . $request->email . '. Your OTP for Email verification is: ' . $passwordToken;

        \Mail::send('email-template', ['body' => $body], function ($message) use ($request) {
            $message->to($request->email)
                ->from('noreply@lurc.com', 'LURC')
                ->subject('Email Verification');
        });
        return response()->json(['msg' => 'We have re-sent an OTP to your email. Submit your OTP to verify your account.', 'status' => $user], 201);
    }

    //email verification
    public function verifyEmail(Request $request){
        $validator = Validator::make($request->all(),
        [
            'otp' => 'required|digits:6',
            'email' => 'required',
            'password' => 'required',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(), 422);
        }
    
        $user = User::where('email', $request->email)->where('passwordToken', $request->otp)->first();

        if(!$user){
            return response()->json([
                'success' => false,
                'msg' => 'Invalid OTP!!'
            ], 401);
        }
        $time_now = now();
        $previous_time = now()->subMinutes(5);
        if(User::where('email', $request->email)->whereBetween('token_expired_at', [$previous_time, $time_now])->count()==0){
            $passwordToken = rand(100000, 999999);
            User::where('email', $request->email)->update([
                'passwordToken' => $passwordToken,
                'token_expired_at' => now(),
            ]);
            $body = 'You have register for LU. Your OTP for Email verification is: ' . $passwordToken;

            \Mail::send('email-template', ['body' => $body], function ($message) use ($request) {
                $message->to($request->email)
                    ->from('noreply@lurc.com', 'LURC')
                    ->subject('Email Verification');
            });

            return response()->json([
                'success' => false,
                'msg' => 'OTP Expired!!  We have sent an OTP to your email. Submit your OTP to verify your account.'
            ], 402);
            
        }
        User::where('email', $request->email)->update([
            'isActive' => 1,
            'passwordToken' => null,
            'token_expired_at' => null,
        ]);

        $validator = Validator::make($request->all(),
            [
                'email' => 'bail|required|exists:users,email',
                'password' => 'bail|required|min:2|max:20',
            ], ['email.exists' => 'No account found for this email']);

            if($token = auth()->attempt($validator->validated())){
                return response()->json([
                    'user' => $user,
                    'token'=> $token,
                ],200);
            }

    }
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(),
        [
            'email' => 'bail|required|email|exists:users,email',
            'password' => 'bail|required|min:8|max:20',
        ], ['email.exists' => 'No account found for this email']);

        if($validator->fails()){
            return response()->json($validator->errors(), 422);
        }

        $input = $request->all();
        $data = User::select('id', 'email', 'password')->where('email', $input['email'])->first();

        //The makeVisible method returns the model instance
        $data->makeVisible('password')->toArray();

        $checkUser = Hash::check($input['password'], $data->password);
        if (!$checkUser) {
            return response()->json(['msg'=>'Invalid password'], 401);
        }
        $user = User::where('email', $request->email)->where('isActive', 1)->first();
        if ($user) {
            if($user->twoFactor =="Off"){
                if($token = auth()->attempt($validator->validated())){
                    return response()->json([
                        'user' => $user,
                        'token'=>$token,
                    ], 200);
                } else {
                    return response()->json(['msg'=>'Invalid credentials'], 401);
                }
            } else {
                $twoFactorCode = rand( 100000 , 999999 );

                User::where('email', $request->email)->update([
                    'passwordToken' => $twoFactorCode,
                    'token_expired_at' => now(),
                ]);
                $body = 'We received a request to login your LURC account associated with ' . $request->email . '. Your Two factor authentication OTP is: '. $twoFactorCode;
                \Mail::send('email-template', ['body' => $body], function ($message) use ($request) {
                    $message->to($request->email)
                        ->from('noreply@lurc.com', 'LURC')
                        ->subject('Two Factor Authentication');
                });
                $datetime1 = new DateTime(now());
                // $dateTime = DateTime::createFromFormat('Y-m-d\TH:i:s.u\Z', $time->token_expired_at);
                // $t = date('Y-m-d H:i:s' , strtotime($time->token_expired_at));

                $exp = $datetime1->modify('+5 minutes');

                $datetime2 = now();

                $interval = $datetime2->diff($exp);
                list($hours, $minutes, $seconds) = explode(':', $interval->format('%H:%I:%S'));

                $totalSeconds = ($hours * 3600) + ($minutes * 60) + $seconds;
                return response()->json([
                    'success' => true,
                    'msg'=> 'We have sent a Two factor authentication OTP to your email. Submit your OTP to login your account.',
                    'data' => $totalSeconds
                ], 201);
            }
        } else {
            $passwordToken = rand(100000, 999999);
            User::where('email', $request->email)->update([
                'passwordToken' => $passwordToken,
                'token_expired_at' => now(),
            ]);
            $body = 'You have register for LU. Your OTP for Email verification is: ' . $passwordToken;

            \Mail::send('email-template', ['body' => $body], function ($message) use ($request) {
                $message->to($request->email)
                    ->from('noreply@lurc.com', 'LURC')
                    ->subject('Email Verification');
            });

            return response()->json([
                'success' => false,
                'msg' => 'Your email is not verified!!  We have sent an OTP to your email. Submit your OTP to verify your account.'
            ], 402);
        }

    }

    public function refesh(){
        return $this->respondWithToken(auth()->refresh);
    }

    public function submitTwoFactorCode(Request $request)
    {
        $validator = Validator::make($request->all(),
        [
            'otp' => 'required|digits:6',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(), 422);
        }
    
        $user = User::where('email', $request->email)->where('passwordToken', $request->otp)->first();

        if(!$user){
            return response()->json([
                'success' => false,
                'msg' => 'Invalid OTP!!'
            ], 401);
        }
        $time_now = now();
        $previous_time = now()->subMinutes(5);
        if(User::where('email', $request->email)->whereBetween('token_expired_at', [$previous_time, $time_now])->count()==0){
            $passwordToken = rand(100000, 999999);
            User::where('email', $request->email)->update([
                'passwordToken' => $passwordToken,
                'token_expired_at' => now(),
            ]);
            $body = 'We received a request to login your LURC account associated with ' . $request->email . '. Your Two factor authentication OTP is: '. $passwordToken;
            \Mail::send('email-template', ['body' => $body], function ($message) use ($request) {
                $message->to($request->email)
                    ->from('noreply@lurc.com', 'LURC')
                    ->subject('Two Factor Authentication');
            });
            $datetime1 = new DateTime(now());
            // $dateTime = DateTime::createFromFormat('Y-m-d\TH:i:s.u\Z', $time->token_expired_at);
            // $t = date('Y-m-d H:i:s' , strtotime($time->token_expired_at));

            $exp = $datetime1->modify('+5 minutes');

            $datetime2 = now();

            $interval = $datetime2->diff($exp);
            list($hours, $minutes, $seconds) = explode(':', $interval->format('%H:%I:%S'));

            $totalSeconds = ($hours * 3600) + ($minutes * 60) + $seconds;
            return response()->json([
                'success' => false,
                'msg'=> 'OTP expired!! We have re-sent a Two factor authentication OTP to your email. Submit your OTP to login your account.',
                'data' => $totalSeconds
            ], 402);
            
        }
        User::where('email', $request->email)->update([
            'passwordToken' => null,
            'token_expired_at' => null,
        ]);

        $validator = Validator::make($request->all(),
        [
            'email' => 'bail|required|exists:users,email',
            'password' => 'bail|required|min:2|max:20',
        ], ['email.exists' => 'No account found for this email']);

        if($token = auth()->attempt($validator->validated())){
            return response()->json([
                'user' => $user,
                'token'=> $token,
            ],200);
        }
    }

    //For Reset password
    public function sendResetPassOtp(Request $request)
    {
        $validator = Validator::make($request->all(),
        [
            'email' => 'required|email|exists:users,email',
        ], ['email.exists' => 'No account found for this email']);

        if($validator->fails()){
            return response()->json($validator->errors(), 422);
        }

        $token = rand(100000, 999999);
        $token_expired_at = now();
        User::where('email', $request->email)->update([
            'passwordToken' => $token,
            'token_expired_at' => $token_expired_at,
        ]);

        //$action_link = redirect('/reset')->route( ['token' => $token, 'email' => $request->email]);

        $body = 'We have received a request to reset the password for <b>LURC<b> account associated with ' . $request->email . '. Your OTP for reset password: ' . $token;

        \Mail::send('email-template', ['body' => $body], function ($message) use ($request) {
            $message->to($request->email)
                ->from('noreply@example.com', 'LURC')
                ->subject('Reset Password');
        });

        return response()->json(['msg' => 'We have sent an OTP to your email.', 'status' => $request->email], 200);
    }
    function getOtpTime (Request $request)
    { 

        // $time = User::select('id', 'email', 'token_expired_at')->where('email', $request->email)->first();
        $time_now = now();
        $previous_time = now()->subMinutes(5);
        $time = User::where('email', $request->email)->whereBetween('token_expired_at', [$previous_time, $time_now])->first();
        if(!$time){
            return response()->json([
                'success'=> false,
            ],422);
        } else{
            $datetime1 = new DateTime($time->token_expired_at);
            // $dateTime = DateTime::createFromFormat('Y-m-d\TH:i:s.u\Z', $time->token_expired_at);
            // $t = date('Y-m-d H:i:s' , strtotime($time->token_expired_at));

            $exp = $datetime1->modify('+5 minutes');

            $datetime2 = now();

            $interval = $datetime2->diff($exp);
            list($hours, $minutes, $seconds) = explode(':', $interval->format('%H:%I:%S'));

            $totalSeconds = ($hours * 3600) + ($minutes * 60) + $seconds;
            return response()->json([
                'success'=> true,
                'data'=> $totalSeconds,
            ],200);
            
        }
    }

    public function resendPassResetOtp(Request $request){
        $validator = Validator::make($request->all(),
        [
            'email' => 'required',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(), 422);
        }
    
        
        $passwordToken = rand(100000, 999999);
        $token_expired_at = now();
        $user = User::where('email', $request->email)->update([
            'passwordToken' => $passwordToken,
            'token_expired_at' => $token_expired_at,
        ]);

        $body = 'We have received a request to reset the password for <b>LURC<b> account associated with ' . $request->email . '. Your OTP for reset password: ' . $passwordToken;

        \Mail::send('email-template', ['body' => $body], function ($message) use ($request) {
            $message->to($request->email)
                ->from('noreply@lurc.com', 'LURC')
                ->subject('Reset Password');
        });
        return response()->json(['msg' => 'We have re-sent an OTP to your email. Submit your OTP to reset your password.', 'status' => $user], 201);
    }
    public function submitResetPassOtp(Request $request)
    {
        $validator = Validator::make($request->all(),
        [
            'otp' => 'required|digits:6',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(), 422);
        }
        $time_now = now();
        \Log::info('Time Now');
        \Log::info($time_now);  
        
        $previous_time = now()->subMinutes(5);
        \Log::info($previous_time);

        if(User::where('email', $request->email)->where('passwordToken', $request->otp)->count()==0){
        
            return response()->json([
                'success' => false,
                'msg' => 'Invalid OTP!!'
            ], 401);
        }
        if(User::where('email', $request->email)->whereBetween('token_expired_at', [$previous_time, $time_now])->count()==0){
            $token = rand(100000, 999999);
            $token_expired_at = now();
            User::where('email', $request->email)->update([
                'passwordToken' => $token,
                'token_expired_at' => $token_expired_at,
            ]);

            //$action_link = redirect('/reset')->route( ['token' => $token, 'email' => $request->email]);

            $body = 'We have received a request to reset the password for <b>LURC<b> account associated with ' . $request->email . '. Your OTP for reset password: ' . $token;

            \Mail::send('email-template', ['body' => $body], function ($message) use ($request) {
                $message->to($request->email)
                    ->from('noreply@example.com', 'LURC')
                    ->subject('Reset Password');
            });

            return response()->json([
                'success' => false,
                'msg' => 'OTP Expired!! We have sent an OTP to your email.'
            ], 402);
        }   
        else {
            return response()->json(['msg' => 'Now you can reset your password!!', 'status' => 'success'], 200);
        } 
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(),
        [
            'email' => 'required|email|exists:users,email',
            'password' => ['required',
               'min:8',
               'max:20',
               'regex:/^.*((?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*[!@#$%&*<+_-])).*$/',
               'confirmed'],
            'password_confirmation' => 'required',
        ],
        [
            'password.regex' => '1 Upper, 1 Lower, 1 Digit, 1 Special Character'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(), 422);
        }
        $check = User::where('email', $request->email)->where('passwordToken', $request->otp)->count();
        if ($check == 1) {
            User::where('email', $request->email)->update([
                'password' => \Hash::make($request->password),
                'passwordToken' => null,
                'token_expired_at' => null,
            ]);
            return response()->json(['msg' => 'Password updated successfully', 'status' => 'success'], 200);
        } else {
            return response()->json(['msg' => 'Invalid OTP', 'status' => 'error'], 401);
        }
    }

    public function updatePass(Request $request)
    {
        
        $validator = Validator::make($request->all(),
        [
            'old_password' => 'required',
            'password' => ['required',
                   'min:8',
                   'max:20',
                   'regex:/^.*((?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*[!@#$%&*<+_-])).*$/',
                   'confirmed'],
            'password_confirmation' => 'required',
        ], [
            'password.regex' => '1 Upper, 1 Lower, 1 Digit, 1 Special Character'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(), 422);
        }


        $input = $request->all();
        $data = User::select('id', 'email', 'password')->where('email', Auth::user()->email)->first();

        //The makeVisible method returns the model instance
        $data->makeVisible('password')->toArray();

        $checkUser = Hash::check($input['old_password'], $data->password);
        if (!$checkUser) {
            return response()->json(['msg'=>'Old password is incorrect'], 401);
        }
        else {
            User::where('email', Auth::user()->email)->update([
                'password' => \Hash::make($request->password),
            ]);
            return response()->json(['msg' => 'Password updated successfully', 'status' => 'success'], 201);
        } 
    }

    public function updateTwoFactor(Request $request)
    {
        $data = User::where('id', Auth::user()->id)->update([
            'twoFactor' => $request->status,
        ]);
        
        return response()->json([
            'success'=> true,
            'data'=>$data
        ],200);
    }

    public function logout(){
        auth()->logout();
        return response()->json([
            'msg' => 'Logged Out'
        ]);
    }

    public function deleteAccount(Request $request){
        $validator = Validator::make($request->all(),
        [
            'password' => 'required',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(), 422);
        }
        $data = $request->all();
        $user_id = Auth::user()->id;
        $user = User::select('id','password','email')->where('id',$user_id)->first();
        if(!Hash::check($data['password'], $user->password)){
            return response()->json([
                'message' => 'Incorrect Password!!',
                'status' => false
            ],422);
        }
        $comment = Comment::where('user_id', Auth::user()->id)->get();
        if($comment){
            foreach ($comment as $a) {
                CommentLike::where('comment_id', $a->id)->delete();
            }
        }
        $commentReply = CommentReply::where('user_id', Auth::user()->id)->get();
        if($comment){
            foreach ($comment as $a) {
                CommentReplyLike::where('comment_reply_id', $a->id)->delete();
            }
        }
        
        $Author = Author::where('user_id', Auth::user()->id)->first();
        if($Author){
            Author::where('user_id', Auth::user()->id)->delete();
        }
        $Read = Read::where('user_id', Auth::user()->id)->first();
        if($Read){
            Read::where('user_id', Auth::user()->id)->delete();
        }
        $Like = Like::where('user_id', Auth::user()->id)->first();
        if($Like){
            Like::where('user_id', Auth::user()->id)->delete();
        }
        $Comment = Comment::where('user_id', Auth::user()->id)->first();
        if($Comment){
            Comment::where('user_id', Auth::user()->id)->delete();
        }
        $CommentReply = CommentReply::where('user_id', Auth::user()->id)->first();
        if($CommentReply){
            CommentReply::where('user_id', Auth::user()->id)->delete();
        }
        $Vote = Vote::where('user_id', Auth::user()->id)->first();
        if($Vote){
            Vote::where('user_id', Auth::user()->id)->delete();
        }
        $Notification = Notify::where(['user_id'=>Auth::user()->id])->first();
        if($Notification){
            Notify::where(['user_id'=>Auth::user()->id])->delete();
        }
        $connection1 = Connection::where(['sent_request_user'=>Auth::user()->id])->first();
        if($connection1){
            Connection::where(['sent_request_user'=>Auth::user()->id])->delete();
        }
        $connection2 = Connection::where(['received_request_user'=>Auth::user()->id])->first();
        if($connection2){
            Connection::where(['received_request_user'=>Auth::user()->id])->delete();
        }
        $Conversation1 = Conversation::where(['from_id'=>Auth::user()->id])->first();
        if($Conversation1){
            Conversation::where(['from_id'=>Auth::user()->id])->delete();
        }
        $Conversation2 = Conversation::where(['to_id'=>Auth::user()->id])->first();
        if($Conversation2){
            Conversation::where(['to_id'=>Auth::user()->id])->delete();
        }
        $ConversationChat1 = ConversationChat::where(['from_id'=>Auth::user()->id])->first();
        if($ConversationChat1){
            ConversationChat::where(['from_id'=>Auth::user()->id])->delete();
        }
        $ConversationChat2 = ConversationChat::where(['to_id'=>Auth::user()->id])->first();
        if($ConversationChat2){
            ConversationChat::where(['to_id'=>Auth::user()->id])->delete();
        }
        $Education = Education::where(['user_id'=>Auth::user()->id])->first();
        if($Education){
            Education::where(['user_id'=>Auth::user()->id])->delete();
        }
        $UserSkill = UserSkill::where(['user_id'=>Auth::user()->id])->first();
        if($UserSkill){
            UserSkill::where(['user_id'=>Auth::user()->id])->delete();
        }
        $AuthUserPost = Post::where('user_id', Auth::user()->id)->get();
        if($AuthUserPost){
            foreach ($AuthUserPost as $a) {
                $image = Image::where('post_id', $a->id)->first();
                if($image){
                    Image::where('post_id', $a->id)->delete();
                }
            }
        }
        
        $delete_post = Post::where('user_id', Auth::user()->id)->delete();

        $data = User::where('id', Auth::user()->id)->delete();
        auth()->logout();
        return response()->json([
            'msg' => 'Account Deleted'
        ], 201);
    }
}
