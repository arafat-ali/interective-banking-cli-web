<?php

declare(strict_types = 1);
namespace App\Middleware;
use App\SessionHandle\Session;

class Auth{

    public static function user():array|null{
        $auth = (new Session())->get("auth");
        if(isset($auth["user"])){
            return $auth["user"];
        }
        return null;
    }

    public static function status():bool{
        $auth = (new Session())->get('auth');
        if(isset($auth["user"])){
            return true;
        }
        return false;
    }

}