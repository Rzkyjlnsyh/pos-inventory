<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Owner\PurchaseOrderController as BaseController;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class PurchaseOrderController extends BaseController
{
    public function approve(Request $request, PurchaseOrder $purchase): RedirectResponse
    {
        if (!in_array(auth()->user()->role, ['finance', 'owner'])) {
            return back()->withErrors(['status' => 'Unauthorized']);
        }
        return parent::approve($request, $purchase);
    }

    public function payment(Request $request, PurchaseOrder $purchase): RedirectResponse
    {
        if (!in_array(auth()->user()->role, ['finance', 'owner'])) {
            return back()->withErrors(['status' => 'Unauthorized']);
        }
        return parent::payment($request, $purchase);
    }

    public function updateWorkflowStatus(Request $request, PurchaseOrder $purchase): RedirectResponse
    {
        $validated = $request->validate(['new_status' => 'required|string']);
        if ($validated['new_status'] !== 'selesai' && auth()->user()->role !== 'owner') {
            return back()->withErrors(['status' => 'Finance hanya bisa update ke selesai.']);
        }
        return parent::updateWorkflowStatus($request, $purchase);
    }
}