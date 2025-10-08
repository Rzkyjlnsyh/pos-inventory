<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Owner\PurchaseReturnController as BaseController;
use App\Models\PurchaseReturn;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class PurchaseReturnController extends BaseController
{
    public function index(Request $request): View
    {
        if (!in_array(auth()->user()->usertype, ['admin', 'owner'])) {
            abort(403, 'Akses ditolak untuk admin');
        }

        $returns = PurchaseReturn::with(['purchaseOrder', 'supplier', 'creator'])
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('admin.purchases.returns.index', compact('returns'));
    }

    public function create(PurchaseOrder $purchase): View
    {
        if (!in_array(auth()->user()->usertype, ['admin', 'owner'])) {
            abort(403, 'Akses ditolak untuk admin');
        }

        $purchase->load(['items.product', 'supplier']);
        return view('admin.purchases.returns.create', compact('purchase'));
    }

    public function store(Request $request, PurchaseOrder $purchase): RedirectResponse
    {
        if (!in_array(auth()->user()->usertype, ['admin', 'owner'])) {
            abort(403, 'Akses ditolak untuk admin');
        }

        return parent::store($request, $purchase);
    }

    public function show(PurchaseReturn $purchaseReturn): View
    {
        if (!in_array(auth()->user()->usertype, ['admin', 'owner'])) {
            abort(403, 'Akses ditolak untuk admin');
        }

        $purchaseReturn->load(['purchaseOrder', 'supplier', 'items.product', 'creator']);
        return view('admin.purchases.returns.show', compact('purchaseReturn'));
    }

    // ADMIN TIDAK BISA CONFIRM - Hanya bisa lihat
    public function confirm(PurchaseReturn $purchaseReturn): RedirectResponse
    {
        return back()->with('error', 'Admin tidak memiliki akses untuk konfirmasi retur.');
    }

    public function cancel(PurchaseReturn $purchaseReturn): RedirectResponse
    {
        if (!in_array(auth()->user()->usertype, ['admin', 'owner'])) {
            return back()->withErrors(['status' => 'Unauthorized']);
        }

        // Hanya bisa cancel yang statusnya masih pending
        if ($purchaseReturn->status !== 'pending') {
            return back()->with('error', 'Hanya retur pending yang bisa dibatalkan.');
        }

        return parent::cancel($purchaseReturn);
    }
}