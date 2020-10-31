<?php

namespace Mupi\AuthPac;

use Illuminate\Support\ServiceProvider;

class AuthPacServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app['router']->group(['prefix' => 'api/v1'], function ($router) {
          $router->post('login', 'Mupi\AuthPac\AuthPacController@login');
          $router->middleware('auth:api')->get('login-token', 'Mupi\AuthPac\AuthPacController@loginToken');
          $router->post('auth/register','Mupi\AuthPac\AuthPacController@register');
          $router->post('auth/recover-pass','Mupi\AuthPac\AuthPacController@recoverPass');
          $router->get('auth/verify-email/{id}','Mupi\AuthPac\AuthPacController@verifyEmail');
          $router->get('auth/reset-password/{email}/{token}','Mupi\AuthPac\AuthPacController@resetPasswordShowForm');
          $router->post('auth/reset-password','Mupi\AuthPac\AuthPacController@resetPassword');
          
          $router->put('user-settings/{user_id}','Mupi\AuthPac\AuthPacController@putUserSettings');

        });
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('authpac', function($app) {
            return new Service();
        });
    }
    public function provides() {
        return ['authpac'];
    }
}
