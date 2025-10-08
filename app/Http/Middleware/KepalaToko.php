<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class KepalaToko
{
// app/Http/Middleware/Finance.php - UPDATE:
public function handle(Request $request, Closure $next)
{
    if (Auth::check() && 
        (strtolower(Auth::user()->usertype) === 'kepala_toko' || 
         strtolower(Auth::user()->role) === 'kepala_toko')) {
        return $next($request);
    }

    return redirect('/dashboard')->with('error', 'Akses ditolak.');
}
}