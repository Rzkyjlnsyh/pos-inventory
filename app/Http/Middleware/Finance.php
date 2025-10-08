<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class Finance
{
// app/Http/Middleware/Finance.php - UPDATE:
public function handle(Request $request, Closure $next)
{
    if (Auth::check() && 
        (strtolower(Auth::user()->usertype) === 'finance' || 
         strtolower(Auth::user()->role) === 'finance')) {
        return $next($request);
    }

    return redirect('/dashboard')->with('error', 'Akses ditolak.');
}
}