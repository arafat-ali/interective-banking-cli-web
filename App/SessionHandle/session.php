<?php

declare(strict_types=1);
namespace App\SessionHandle;
use App\Models\Customer\Customer;
use App\Models\Admin\Admin;

class Session{

    public function __construct(){
        if(session_status() !== PHP_SESSION_ACTIVE) session_start();
    }

    public function set(string $key, string|Customer|Admin|array|null $value){
        $_SESSION[$key] = $value;
    }

    public function get(string $key) : string|array|null{
        if(!isset($_SESSION[$key])){
            return null;
        }
        return $_SESSION[$key];
    }

    public function unset(string $key):void{
        unset($_SESSION[$key]);
    }

    public function destroy(){
        session_destroy();
    }

}

