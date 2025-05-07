<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Repository\Roles\RolesRepositoryInterface;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RolesController extends Controller
{
    public $rolesRepository;
    public function __construct(RolesRepositoryInterface $rolesRepository)
    {
        $this->rolesRepository = $rolesRepository;
    }
    public function display()
    {
        $rolesWithUsers = $this->rolesRepository->display();
        return response()->json($rolesWithUsers);
    }

    public function update(User $user, Role $role)
    {
        $user->syncRoles($role);
        return response()->json(['message' => 'Role assigned successfully.']);
    }
    public function remove(User $user, Role $role)
    {
        $user->removeRole($role);
        return redirect()->back();
    }
}
