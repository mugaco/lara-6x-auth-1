<?php
namespace Mupi\AuthPac;

use App\Http\Controllers\Controller as Controller;
use Illuminate\Http\Request;
use Mupi\AuthPac\Facade as AuthPac;


use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\PasswordReset;

use App\User;

class AuthPacController extends Controller
{
    public function register(Request $request)
    {
        $res = AuthPac::register($request);
        if ($res['status']==200) {
            $user = $res['user'];
            $user->url_back = $request->header('origin');
            // return response()->json(['bu'=>$user], 200);

            
            event(new Registered($user));
            return response()->json($res, 200);
        }
        return response()->json($res, $res['status']);
    }

    public function login(Request $request)
    {
        $res = AuthPac::login($request);
        if ($res['status']==200) {
            if(!$res['user']['email_verified_at']){
                $res['token'] = false;
                //correo con link para verificar email
                $this->newVerificationLink($request);
            }
            return response()->json($res, 200);
        }
        return response()->json($res, $res['status']);
    }
    public function recoverPass(Request $request)
    {
        $res = AuthPac::recoverPass($request);
        if ($res['status']==200) {
            event(new PasswordReset($res['user']));

            return response()->json($res, 200);
        }
        return response()->json($res, $res['status']);

        // return $auth->recoverPass();
        // //return response()->json($request->all(),200);
    }
    public function verifyEmail($id,Request $request)
    {
        $url = urldecode(base64_decode($request->url_back));
        
        $user = User::find(base64_decode($id));
        if($user->email_verified_at){
            header('Location:'. $url);
        }else{
            $user->update(['email_verified_at'=>\Carbon\Carbon::now()]);
            $token = $user->createToken('Laravel Personal Access Client')->accessToken;
            $url .= "?kY2Y0=".$token."&name=".$user->name."&id=".$user->id; 
            header('Location:'. $url);
        }

    }
    public function resetPasswordShowForm (Request $request, $email, $token) {
        $reset = \DB::table('password_resets')
      ->where('email', urldecode(base64_decode($email)))
      ->where('token', $token)->first();
        if ($reset) {
            $url = urldecode(base64_decode($request->url_back)) ."/new-password?email=".urldecode(base64_decode($email))."&recover_token=".$token;
            
            header('Location:'. $url);
        }
        // echo "ko";
        // die;
    }
    public function resetPassword(Request $request) {
        if (!$request->email) {
            return response()->json(['error'=>'email required'], 422);
        }
        if (!filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['status'=>422,'error'=>'email invalid'], 422);
        }
        if (!$request->password) {
            return response()->json(['error'=>'password required'], 422);
        }
        if (! $request->confirm_password) {
            return response()->json(['error'=>'confirm password required'], 422);
        }
        if ($request->password != $request->confirm_password) {
            return response()->json(['error'=>'password not match confirm'], 422);
        }
        $reset = \DB::table('password_resets')
        ->where('email', $request->email)
        ->where('token', $request->token)
        ->first();
        if ($reset) {
            $ahora = \Carbon\Carbon::now();
            if ($ahora->diffInMinutes($reset->created_at) > 30) {
                return response()->json(['error'=>'token recover password expired'], 422);
            }
            $user = \App\User::where('email', $reset->email)->first();
            if( ! $user->email_verified_at) {
                $user->email_verified_at = $ahora;
            }
            $user->password = \Hash::make($request->password);
            $user->save();
            $token = $user->createToken('Laravel Personal Access Client')->accessToken;
            $ruser = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'email_verified_at' => $user->email_verified_at,
            ];
            return response()->json(['status'=>200,'token'=>$token,'user'=>$ruser], 200);
        }
        return response()->json(['error'=>'bad token or email'], 422);
    }
    public function loginToken (Request $request)
    {
        $res = AuthPac::loginByToken($request);
        if ($res['status']==200) {
            return response()->json($res, 200);
        }
        return response()->json(['data'=>$res], $res['status']);
    }



	private function newVerificationLink($request)
	{
		if (  $user = User::where('email',$request->email)->first() ){
            $user->url_back = $request->header('origin');
            event(new Registered($user));
		}
	}
    public function putUserSettings($user_id, Request $request)
    {
        $user = User::find($user_id);

     
        $metadata = (object) [];
        if($user->metadata){
            $metadata = json_decode($user->metadata);
        }
        if(isset($request->dark)){
            $metadata->dark = $request->dark;
        }
        if(isset($request->lang)){
            $user->lang = $request->lang;
        }
        $user->metadata = json_encode($metadata);
        $user->save();
        $u = [
            'id' => $user->id,
            'name' => $user->name,
            //'email' => $user->email,
            'lang' => $user->lang,
            'dark' => $request->dark,
            //'email_verified_at' => $user->email_verified_at,
        ];
        return response()->json(['r'=>$request->all(), 'user'=>$u], 200);

    }
}
