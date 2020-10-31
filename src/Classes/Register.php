<?php

namespace Mupi\AuthPac\Classes;

use Illuminate\Auth\Events\Registered;

use App\User;

class Register
{
    public function run($request)
    {
        $hasErrors = $this->validate($request->all());
        
        if ($hasErrors) {
            return $hasErrors;
        }

        $user = $this->create($request->all());

        return ['status'=>200,'user'=>$user];
    }
    protected function validate($ar)
    {
        if (!isset($ar['email'])) {
            return ['error'=>'email is required','status'=>422];
        }
        if (!filter_var($ar['email'], FILTER_VALIDATE_EMAIL)) {
            return ['error'=>'email is not valid','status'=>422];
        }
        if (!isset($ar['name'])) {
            return ['error'=>'name is required','status'=>422];
        }
        if (!isset($ar['password'])) {
            return ['error'=>'password is required','status'=>422];
        }
        if (!isset($ar['password_confirmation'])) {
            return ['error'=>'password_confirmation is required','status'=>422];
        }
        
        if ($ar['password_confirmation'] != $ar['password']) {
            return ['error'=>'password_confirmation fail','status'=>422];
        }
        if (!$ar['agree']) {
            return ['error'=>'must agree','status'=>422];
        }
        $u=User::where('email', $ar['email'])->first();
        
        if ($u) {
            return ['error'=>'user exist','status'=>422];
        }


        return false;
    }
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => \Hash::make($data['password']),
            'lang' => $data['lang']
        ]);
    }
}
