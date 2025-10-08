<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\SalesOrder;
use App\Models\SalesOrderLog;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Carbon\Carbon;

class SalesOrderController extends Controller
{
    private function logAction(SalesOrder $salesOrder, string $action, string $description): void
    {
        SalesOrderLog::create([
            'sales_order_id' => $salesOrder->id,
            'user_id' => Auth::id(),
            'action' => $action,
            'description' => $description,
            'created_at' => now(),
        ]);
    }

    public function index(Request $request): View
    {
        $q = $request->get('q');
        $status = $request->get('status');
        $payment_status = $request->get('payment_status');

        $salesOrders = SalesOrder::with(['customer', 'creator', 'approver'])
            ->when($q, fn($query) =>
                $query->where('so_number', 'like', "%$q%")
                    ->orWhereHas('customer', fn($qq) => $qq->where('name', 'like', "%$q%"))
            )
            ->when($status, fn($query) => $query->where('status', $status))
            ->when($payment_status && $payment_status !== 'all', fn($query) => $query->where('payment_status', $payment_status))
            ->orderByDesc('id')
            ->paginate(15);

        return view('finance.sales.index', compact('salesOrders', 'q', 'status', 'payment_status'));
    }

    public function show(SalesOrder $salesOrder): View
    {
        $salesOrder->load(['customer', 'items', 'creator', 'approver', 'payments.creator', 'logs.user']);
        $payment = $salesOrder->payments->first() ?? new Payment();
        return view('finance.sales.show', compact('salesOrder', 'payment'));
    }

    public function approve(Request $request, SalesOrder $salesOrder): RedirectResponse
    {
        if ($salesOrder->status !== 'pending') {
            return back()->withErrors(['status' => 'Hanya pending yang bisa di-approve.']);
        }

        try {
            $salesOrder->update(['approved_by' => Auth::id(), 'approved_at' => Carbon::now()]);
            $this->logAction($salesOrder, 'approved', 'Sales order di-approve oleh ' . Auth::user()->name);
            return back()->with('success', 'Sales order di-approve.');
        } catch (\Exception $e) {
            \Log::error('Error approving sales order: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Terjadi kesalahan saat approve: ' . $e->getMessage()]);
        }
    }
}