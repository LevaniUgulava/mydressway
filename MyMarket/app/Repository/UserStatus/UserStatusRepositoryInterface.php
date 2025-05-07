<?php

namespace App\Repository\UserStatus;

use Illuminate\Http\Request;

interface UserStatusRepositoryInterface
{
    public function display();
    public function create(array $data);
    public function delete($id);
    public function StatuswithUser($id);
}
