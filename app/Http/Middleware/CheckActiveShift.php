<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Shift;

class CheckActiveShift
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Jika mencoba akses penjualan/create sales, tapi tidak ada shift aktif
        if ($request->is('*/sales*') || $request->is('*/penjualan*')) {
            $activeShift = Shift::whereNull('end_time')->first();
            
            if (!$activeShift) {
                return redirect()->route('owner.shift.dashboard')
                    ->with('error', 'Tidak bisa melakukan penjualan. Mulai shift terlebih dahulu.');
            }
        }

        return $next($request);
    }
}