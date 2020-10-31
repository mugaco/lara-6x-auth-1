<?php
namespace Mupi\AuthPac;
use App\User;
use Auth;
use DB;
use Carbon\Carbon;
use Mupi\AuthPac\OauthAccessTokensModel;
use Illuminate\Http\Request;

use Mupi\AuthPac\Classes\Register;
use Mupi\AuthPac\Classes\Login;
use Mupi\AuthPac\Classes\RecoverPass;

class Service
{
	protected $user;
	protected $request;
	// function __construct(Request $request)
	// {
	// 	$this->request = $request;
	// }
	public function login(Request $request)
	{
		return  ( new Login )->login($request);
	}
	public function loginByToken(Request $request)
	{
		return  ( new Login )->loginByToken($request);
	}
	public function register(Request $request)
  	{
	  	return  ( new Register )->run($request);
	}

	public function recoverPass(Request $request)
	{
		return  ( new RecoverPass )->run($request);
  	}
	private function getUserIP()
	{
		// Get real visitor IP behind CloudFlare network
		if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
							$_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
							$_SERVER['HTTP_CLIENT_IP'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
		}
		$client  = @$_SERVER['HTTP_CLIENT_IP'];
		$forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
		$remote  = $_SERVER['REMOTE_ADDR'];

		if(filter_var($client, FILTER_VALIDATE_IP))
		{
				$ip = $client;
		}
		elseif(filter_var($forward, FILTER_VALIDATE_IP))
		{
				$ip = $forward;
		}
		else
		{
				$ip = $remote;
		}

		return $ip;
	}


	protected function create(array $data)
	{
			return User::create([
					'name' => $data['name'],
					'email' => $data['email'],
					'password' => \Hash::make($data['password']),
			]);
	}
	public function xxxxxxxxrecoverPass()
	{
		if(!isset($this->request->email))  {
			  return response()->json(['status'=>422,'error'=>'email required'],422);
		}
		if (!filter_var($this->request->email, FILTER_VALIDATE_EMAIL)) {
     return response()->json(['status'=>422,'error'=>'email invalid'],422);
		}
		$user = User::where('email', $this->request->email)->first();
		if(!$user) {
			return response()->json(['status'=>422,'error'=>'not exist'],422);
		}
		DB::table('password_resets')->insert([
		    'email' => $this->request->email,
		    'token' => Str::random(60),
		    'created_at' => Carbon::now()
		]);
		//Get the token just created above
		$tokenData = DB::table('password_resets')->where('email', $this->request->email)->latest()->first();
		$user->url_back = $this->request->url_back;
		$user->reset_password_token = $tokenData->token;
		event(new PasswordReset($user));
		return response()->json(['r'=>$this->request->all(),'status'=>200,'user'=>$user,'toc'=>$tokenData],200);

	}
}
