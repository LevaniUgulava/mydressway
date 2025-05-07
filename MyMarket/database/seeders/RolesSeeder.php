<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = Role::create(['name' => 'admin']);
        $operator = Role::create(['name' => 'operator']);
        $editor = Role::create(['name' => 'editor']);
        $default = Role::create(['name' => 'default']);


        $adminPermission = Permission::create(['name' => 'manage everything']);
        $operatorPermission = Permission::create(['name' => 'manage posts visible']);
        $editorPermission = Permission::create(['name' => 'manage posts']);
        $defaultPermission = Permission::create(['name' => 'default']);

        $permission = [
            $adminPermission,
            $operatorPermission,
            $editorPermission,
            $defaultPermission
        ];

        $admin->syncPermissions($permission);
        $operator->givePermissionTo('manage posts visible');
        $editor->givePermissionTo('manage posts');
        $default->givePermissionTo('default');
    }
}
