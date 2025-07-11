<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Buyer extends Model
{
    protected $table = 'users';
    protected static function booted()
    {
        static::deleting(function ($user) {
            // Delete associated socialite users when the user is deleted
            $user->socialiteUsers()->delete();
        });
    }
}
