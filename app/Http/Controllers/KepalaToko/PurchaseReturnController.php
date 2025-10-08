<?php

namespace App\Http\Controllers\KepalaToko;

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
        if (!in_array(auth()->user()->usertype, ['kepala_toko', 'owner'])) {
            abort(403, 'Akses ditolak untuk kepala toko');
        }

        $returns = PurchaseReturn::with(['purchaseOrder', 'supplier', 'creator'])
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('kepala-toko.purchases.returns.index', compact('returns'));
    }

    public function create(PurchaseOrder $purchase): View
    {
        if (!in_array(auth()->user()->usertype, ['kepala_toko', 'owner'])) {
            abort(403, 'Akses ditolak untuk kepala toko');
        }

        $purchase->load(['items.product', 'supplier']);
        return view('kepala-toko.purchases.returns.create', compact('purchase'));
    }

    public function store(Request $request, PurchaseOrder $purchase): RedirectResponse
    {
        if (!in_array(auth()->user()->usertype, ['kepala_toko', 'owner'])) {
            abort(403, 'Akses ditolak untuk kepala toko');
        }

        return parent::store($request, $purchase);
    }

    public function show(PurchaseReturn $purchaseReturn): View
    {
        if (!in_array(auth()->user()->usertype, ['kepala_toko', 'owner'])) {
            abort(403, 'Akses ditolak untuk kepala toko');
        }

        $purchaseReturn->load(['purchaseOrder', 'supplier', 'items.product', 'creator']);
        return view('kepala-toko.purchases.returns.show', compact('purchaseReturn'));
    }

    // KEPALA TOKO BISA CONFIRM - Sama seperti owner
    public function confirm(PurchaseReturn $purchaseReturn): RedirectResponse
    {
        if (!in_array(auth()->user()->usertype, ['kepala_toko', 'owner'])) {
            return back()->withErrors(['status' => 'Unauthorized']);
        }

        return parent::confirm($purchaseReturn);
    }

    public function cancel(PurchaseReturn $purchaseReturn): RedirectResponse
    {
        if (!in_array(auth()->user()->usertype, ['kepala_toko', 'owner'])) {
            return back()->withErrors(['status' => 'Unauthorized']);
        }

        return parent::cancel($purchaseReturn);
    }
}