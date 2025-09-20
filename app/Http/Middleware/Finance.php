<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class Finance
{
    public function handle(Request $request, Closure $next)
    {
        $session = $request->session()->all();
        Log::info('Finance Middleware: Session Data', $session);

        if (Auth::check() && strtolower(Auth::user()->usertype) === 'finance') {
            Log::info('Finance Middleware: Akses diizinkan, User ID: ' . Auth::id() . ', usertype: ' . Auth::user()->usertype . ', URL: ' . $request->fullUrl());
            return $next($request);
        }

        Log::warning('Finance Middleware: Akses ditolak, User ID: ' . (Auth::id() ?? 'No ID') . ', usertype: ' . (Auth::user()->usertype ?? 'No usertype') . ', URL: ' . $request->fullUrl());
        return redirect('/dashboard')->with('error', 'Akses ditolak. Anda tidak memiliki izin untuk mengakses halaman ini.');
    }
}