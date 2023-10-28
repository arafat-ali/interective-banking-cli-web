<?php

declare(strict_types=1);

namespace App\Database;

interface Operation{

    public function get();

    public function all();

    public function create();

    public function update();


}

