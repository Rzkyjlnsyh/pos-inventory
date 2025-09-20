<?php

namespace App\Http\Controllers\KepalaToko;
use App\Http\Controllers\Owner\PurchaseOrderController as BaseController;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
class PurchaseOrderController extends BaseController
{
    public function updateWorkflowStatus(Request $request, PurchaseOrder $purchase): RedirectResponse
    {
        $validated = $request->validate(['new_status' => 'required|string']);
        if (!in_array($validated['new_status'], ['kain_diterima', 'printing', 'jahit', 'selesai']) && auth()->user()->role !== 'owner') {
            return back()->withErrors(['status' => 'Kepala Toko hanya bisa update ke kain_diterima, printing, jahit, atau selesai.']);
        }
        return parent::updateWorkflowStatus($request, $purchase);
    }
}