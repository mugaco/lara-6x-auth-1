<?php

namespace Mupi\AuthPac\Classes;


Use App\User;
Use DB;
Use Carbon\Carbon;
use Illuminate\Support\Str;


class RecoverPass
{
    protected $request;

    public function run($request)
    {
        $this->request = $request;

		if(!isset($this->request->email))  {
            return ['status'=>422,'error'=>'email is required'];
        }
        if (!filter_var($this->request->email, FILTER_VALIDATE_EMAIL)) {
            return ['status'=>422,'error'=>'email invalid'];
        }
        $user = User::where('email', $this->request->email)->first();
        if(!$user) {
            return ['status'=>422,'error'=>'not exist'];
        }
        $token = Str::random(60);
        DB::table('password_resets')->insert([
            'email' => $this->request->email,
            'token' => $token,
            'created_at' => Carbon::now()
        ]);
      //Get the token just created above
        //$tokenData = DB::table('password_resets')->where('email', $this->request->email)->latest()->first();
        $user->url_back = $this->request->header('origin');
        $user->reset_password_token = $token;
        return ['status'=>200,'user'=>$user];
    }
}
