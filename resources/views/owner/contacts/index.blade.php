<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Customer & Supplier
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div x-data="{ tab: 'customers' }">
                        <div class="mb-4 border-b border-gray-200 dark:border-gray-700">
                            <nav class="flex space-x-4" aria-label="Tabs">
                                <button @click="tab='customers'" :class="tab==='customers' ? 'border-b-2 border-indigo-500 text-indigo-600' : 'text-gray-500'" class="px-3 py-2 text-sm font-medium">Customers</button>
                                <button @click="tab='suppliers'" :class="tab==='suppliers' ? 'border-b-2 border-indigo-500 text-indigo-600' : 'text-gray-500'" class="px-3 py-2 text-sm font-medium">Suppliers</button>
                            </nav>
                        </div>

                        <div x-show="tab==='customers'">
                            <form method="POST" action="{{ route('owner.contacts.customers.store') }}" class="space-y-4 mb-6">
                                @csrf
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <input name="name" required placeholder="Customer name" class="border rounded p-2 text-gray-900 w-full" />
                                    <input name="phone" placeholder="Phone" class="border rounded p-2 text-gray-900 w-full" />
                                    <input name="email" type="email" placeholder="Email" class="border rounded p-2 text-gray-900 w-full" />
                                    <input name="address" placeholder="Address" class="border rounded p-2 text-gray-900 w-full md:col-span-2" />
                                    <input name="notes" placeholder="Notes" class="border rounded p-2 text-gray-900 w-full md:col-span-3" />
                                </div>
                                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded">Add Customer</button>
                            </form>

                            <div class="overflow-x-auto">
                                <table class="min-w-full text-left text-sm">
                                    <thead>
                                        <tr class="border-b border-gray-200">
                                            <th class="px-3 py-2">Name</th>
                                            <th class="px-3 py-2">Phone</th>
                                            <th class="px-3 py-2">Email</th>
                                            <th class="px-3 py-2">Address</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($customers as $c)
                                        <tr class="border-b border-gray-100">
                                            <td class="px-3 py-2">{{ $c->name }}</td>
                                            <td class="px-3 py-2">{{ $c->phone }}</td>
                                            <td class="px-3 py-2">{{ $c->email }}</td>
                                            <td class="px-3 py-2">{{ $c->address }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-3">{{ $customers->withQueryString()->onEachSide(1)->links() }}</div>
                        </div>

                        <div x-show="tab==='suppliers'">
                            <form method="POST" action="{{ route('owner.contacts.suppliers.store') }}" class="space-y-4 mb-6">
                                @csrf
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <input name="name" required placeholder="Supplier name" class="border rounded p-2 text-gray-900 w-full" />
                                    <input name="contact_name" placeholder="Contact person" class="border rounded p-2 text-gray-900 w-full" />
                                    <input name="phone" placeholder="Phone" class="border rounded p-2 text-gray-900 w-full" />
                                    <input name="email" type="email" placeholder="Email" class="border rounded p-2 text-gray-900 w-full" />
                                    <input name="address" placeholder="Address" class="border rounded p-2 text-gray-900 w-full md:col-span-2" />
                                </div>
                                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded">Add Supplier</button>
                            </form>

                            <div class="overflow-x-auto">
                                <table class="min-w-full text-left text-sm">
                                    <thead>
                                        <tr class="border-b border-gray-200">
                                            <th class="px-3 py-2">Name</th>
                                            <th class="px-3 py-2">Contact</th>
                                            <th class="px-3 py-2">Phone</th>
                                            <th class="px-3 py-2">Email</th>
                                            <th class="px-3 py-2">Address</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($suppliers as $s)
                                        <tr class="border-b border-gray-100">
                                            <td class="px-3 py-2">{{ $s->name }}</td>
                                            <td class="px-3 py-2">{{ $s->contact_name }}</td>
                                            <td class="px-3 py-2">{{ $s->phone }}</td>
                                            <td class="px-3 py-2">{{ $s->email }}</td>
                                            <td class="px-3 py-2">{{ $s->address }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-3">{{ $suppliers->withQueryString()->onEachSide(1)->links() }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>