<?php

declare(strict_types=1);

function view(string $fileName, array $data=[]){
    extract($data);
    require_once "App/Views/{$fileName}.php";
}