<?php

declare(strict_types=1);
namespace App\Interfaces;

interface AuthInterface{

    public function user();
    public function login();
    public function register();
}