<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfMustChangePassword
{
    public function handle(Request $request, Closure $next)
    {
        // Jika user tidak terautentikasi, lanjutkan request
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();
        
        // Jika user null, lanjutkan request (prevent assertion error)
        if (!$user) {
            return $next($request);
        }

        // Cek apakah user harus ganti password dan bukan di route yang dikecualikan
        if (
            $user->must_change_password &&
            !$request->is('update-password') &&
            !$request->is('logout') &&
            !$request->is('admin/oauth/callback/*') &&
            !$request->is('admin/oauth/*') // Tambahkan pengecualian untuk semua route OAuth
        ) {
            return redirect('/update-password');
        }

        return $next($request);
    }
}