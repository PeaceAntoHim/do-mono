<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions for users
        $userPermissions = [
            'user.create',
            'user.read',
            'user.edit',
            'user.delete',
        ];

        // Create permissions for roles
        $rolePermissions = [
            'role.create',
            'role.read',
            'role.edit',
            'role.delete',
        ];

        // Create permissions for permissions
        $permissionPermissions = [
            'permission.create',
            'permission.read',
            'permission.edit',
            'permission.delete',
        ];
        
        // Create permissions for photos
        $photoPermissions = [
            'photo.create',
            'photo.read',
            'photo.edit',
            'photo.delete',
        ];

        // Create all permissions
        $allPermissions = array_merge(
            $userPermissions, 
            $rolePermissions, 
            $permissionPermissions, 
            $photoPermissions,
        );
        
        foreach ($allPermissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions
        
        // Super Admin role - gets all permissions
        $superAdminRole = Role::create(['name' => 'super-admin']);
        $superAdminRole->givePermissionTo(Permission::all());
        
        
        // OPS role - can access, upload, and delete photos
        $opsRole = Role::create(['name' => 'ops']);
        $opsRole->givePermissionTo([
            'photo.read',
            'photo.create',
            'photo.delete',
        ]);
        
        // MKT role - can only upload to MKT folder
        $mktRole = Role::create(['name' => 'mkt']);
        $mktRole->givePermissionTo([
            'photo.create',
            'photo.read',
            'photo.delete',
        ]);
        
        // SLS role - can only view MKT folder
        $slsRole = Role::create(['name' => 'sls']);
        $slsRole->givePermissionTo([
            'photo.read',
        ]);
        
    }
}