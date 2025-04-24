<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TransaksiTunai;

class TransaksiTunaiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'subtotal' => 'required|numeric',
            'total_cost_price' => 'required|numeric',
            'name_user' => 'required|string',
            'payment_method' => 'required|string|in:tunai',
            'timestamp' => 'required'
        ]);

        // Buat transaksi baru
        TransaksiTunai::create([
            'subtotal' => $validated['subtotal'],
            'total_cost_price' => $validated['total_cost_price'],
            'name_user' => $validated['name_user'],
            'payment_method' => $validated['payment_method'],
            'timestamp' => $validated['timestamp']
        ]);

        return redirect()->back()->with('success', 'Transaction has been saved successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
