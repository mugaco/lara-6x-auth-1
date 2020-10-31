<?php
namespace Mupi\AuthPac;

class Facade extends \Illuminate\Support\Facades\Facade {

    protected static function getFacadeAccessor() {
        return 'authpac';
    }
}
