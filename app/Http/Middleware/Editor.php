<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class Editor
{
// app/Http/Middleware/Finance.php - UPDATE:
public function handle(Request $request, Closure $next)
{
    if (Auth::check() && 
        (strtolower(Auth::user()->usertype) === 'editor' || 
         strtolower(Auth::user()->role) === 'editor')) {
        return $next($request);
    }

    return redirect('/dashboard')->with('error', 'Akses ditolak.');
}
}