<?php

namespace App\Repository\Roles;

use App\Models\User;
use Spatie\Permission\Models\Role;

class RolesRepository implements RolesRepositoryInterface
{
    public function display()
    {
        $roles = Role::all();
        $rolesWithUsers = [];

        foreach ($roles as $role) {
            $users = $role->users;
            $rolesWithUsers[$role->name] = $users;
        }
        return  $rolesWithUsers;
    }
}
