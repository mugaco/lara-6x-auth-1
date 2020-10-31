<?php

namespace Mupi\AuthPac\Classes;

use App\User;
use Auth;
use Mupi\Cms\Webs\WebsByUserIdentifier;
class Login
{
    protected $request;

    public function login($request)
    {
        $hasErrors = $this->validate($request->all());
        
        if ($hasErrors) {
            return $hasErrors;
        }
        

        $this->request = $request;

        return  $this->loginByCredentials();
    }
    protected function validate($ar)
    {
        if (!isset($ar['email'])) {
            return ['error'=>'email is required','status'=>422];
        }
        if (!filter_var($ar['email'], FILTER_VALIDATE_EMAIL)) {
            return ['error'=>'email is not valid','status'=>422];
        }

        if (!isset($ar['password'])) {
            return ['error'=>'password is required','status'=>422];
        }
        

        $u=User::where('email', $ar['email'])->first();
        
        if (!$u) {
            return ['error'=>'user not exist','status'=>422];
        }


        return false;
    }
    protected function loginByCredentials()
    {
        $credentials = $this->request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            // borramos los ya usados, para aligerar la tabla ??
            // OauthAccessTokensModel::where('user_id',$this->request->user()->id)->delete();
            
            return [
                'token' =>$this->request->user()->createToken('Dev Personal Access Client')->accessToken,
                'status' => 200,
                //'metadata'=> json_decode($this->request->user()->metadata),
                'user'=>$this->transformUser($this->request->user())
            ];
        }
        // if($this->request->password == 'kk00') {
        //     Auth::login(User::find(2));
        //     $token = $this->request->user()->createToken('Laravel Personal Access Client')->accessToken;
        //     return [
        //         'status' => 200,
        //         //'metadata'=> json_decode($this->request->user()->metadata),
        //         'user'=>$this->transformUser($this->request->user()),
        //         'token' =>$token
        //     ];
        // }
        return ['status'=>'401','error'=>'bad credentials'];
    }
    public function loginByToken($request)
    {
        if (Auth::guard('api')->check()) {
            return [
                'status' => 200,
                'user'=>$this->transformUser($request->user())
            ];
        }

        return ['status'=>'401','error'=>'bad credentials'];
    }
    protected function transformUser($user)
    {
        $dark = false;
        if ($user->metadata) {
            $metadata = json_decode($user->metadata);
            if (isset($metadata->dark)) {
                $dark = $metadata->dark;
            }
        }
        

        $superAdmin = true;
        $roles = ['editor'];
        if ($superAdmin == true) {
            $roles[] = 'super-admin';
        }
        $webs = [];
        $webs = $this->getWebsByUserIdentifier(new WebsByUserIdentifier, $user->identifier);
        //var_dump($user->identifier);
        $info = [
            'userIdentifier'=> $user->identifier,
            'roles'=> $roles,
           
            'introduction'=> 'Brevemente',
            'avatar'=> 'https://wpimg.wallstcn.com/f778738c-e4f8-4870-b634-56703b4acafe.gif',
     
            /*Serian las webs para las que el usuario tiene permisos de edicion*/
            'webs'=>$webs,

            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'lang' => $user->lang,
            'dark' => $dark,
            'email_verified_at' => $user->email_verified_at,
        ];
        return $info;
        
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'lang' => $user->lang,
            'dark' => $dark,
            'email_verified_at' => $user->email_verified_at,
        ];
    }
    private function getWebsByUserIdentifier(WebsByUserIdentifier $collection, $userIdentifier)
    {
        //var_dump($userIdentifier);
        $res = $collection->get($userIdentifier);
        if ($res['statusCode']==200) {
            return $res['webs'];
        }
    }
}
