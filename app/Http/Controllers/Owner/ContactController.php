<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function index(Request $request): View
    {
        $customers = Customer::query()
            ->when($request->get('q'), fn($q) => $q->where('name', 'like', '%'.$request->get('q').'%'))
            ->orderBy('name')
            ->paginate(10, pageName: 'customers_page');

        $suppliers = Supplier::query()
            ->when($request->get('q'), fn($q) => $q->where('name', 'like', '%'.$request->get('q').'%'))
            ->orderBy('name')
            ->paginate(10, pageName: 'suppliers_page');

        return view('owner.contacts.index', compact('customers', 'suppliers'));
    }

    public function storeCustomer(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        Customer::create($validated);

        return redirect()->back()->with('success', 'Customer created');
    }

    public function storeSupplier(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
        ]);

        Supplier::create($validated);

        return redirect()->back()->with('success', 'Supplier created');
    }
}