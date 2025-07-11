<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\User;

class PermissionHelper
{
    /**
     * Check if the current user has permission to perform an action on a resource.
     *
     * @param string $resourceClass The Filament resource class
     * @param string $action The action (create, read, update, delete)
     * @return bool
     */
    public static function can(string $resourceClass, string $action): bool
    {
        /** @var User|null $user */
        $user = Auth::user();
        
        if (!$user) {
            return false;
        }
        
        // Super admin bypass
        if ($user->hasRole('super-admin')) {
            return true;
        }
        
        // Extract the model name from the resource class
        $resourceName = static::getResourceName($resourceClass);
        
        // Format the permission name: 'resource.action' (e.g., 'user.create')
        $permission = Str::lower($resourceName) . '.' . $action;
        
        return $user->hasPermissionTo($permission);
    }
    
    /**
     * Get the resource name from a Filament resource class.
     *
     * @param string $resourceClass
     * @return string
     */
    protected static function getResourceName(string $resourceClass): string
    {
        // Extract the class name without namespace
        $className = class_basename($resourceClass);
        
        // Remove "Resource" suffix
        return Str::replace('Resource', '', $className);
    }
}
