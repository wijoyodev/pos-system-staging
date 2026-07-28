<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\History;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('tier')) {
            $tier = $request->tier;
            if ($tier === 'gold') {
                $query->where('total_points', '>=', 500);
            } elseif ($tier === 'silver') {
                $query->where('total_points', '>=', 100)->where('total_points', '<', 500);
            } elseif ($tier === 'bronze') {
                $query->where('total_points', '<', 100);
            }
        }

        $customers = $query->orderBy('created_at', 'desc')->get();

        return view('pages.customer', [
            'title' => 'Customer Management',
            'customers' => $customers,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20', 'unique:customers,phone'],
            'email' => ['nullable', 'email', 'unique:customers,email'],
        ]);

        Customer::create([
            'code' => Customer::generateCode(),
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
        ]);

        // If request is AJAX (from POS quick register), return JSON
        if ($request->wantsJson() || $request->ajax()) {
            $customer = Customer::where('phone', $request->phone)->first();

            return response()->json([
                'success' => true,
                'customer' => [
                    'id' => $customer->id,
                    'code' => $customer->code,
                    'name' => $customer->name,
                    'phone' => $customer->phone,
                    'available_points' => $customer->available_points,
                    'tier' => $customer->tier,
                ],
            ]);
        }

        return redirect('/customers')->with('success', 'Pelanggan berhasil ditambahkan.');
    }

    public function show(Customer $customer)
    {
        $histories = History::with('items.product')
            ->where('customer_id', $customer->id)
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('pages.customer-detail', [
            'title' => 'Detail Pelanggan',
            'customer' => $customer,
            'histories' => $histories,
        ]);
    }

    public function update(Request $request, Customer $customer)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20', Rule::unique('customers', 'phone')->ignore($customer->id)],
            'email' => ['nullable', 'email', Rule::unique('customers', 'email')->ignore($customer->id)],
        ]);

        $customer->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
        ]);

        return redirect('/customers')->with('success', 'Data pelanggan berhasil diperbarui.');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();

        return redirect('/customers')->with('success', 'Pelanggan berhasil dihapus.');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'exists:customers,id']);
        $count = Customer::whereIn('id', $request->ids)->delete();

        return redirect('/customers')->with('success', $count.' pelanggan berhasil dihapus.');
    }

    /**
     * JSON search endpoint for POS autocomplete.
     */
    public function search(Request $request)
    {
        $q = $request->get('q', '');
        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $customers = Customer::where('name', 'like', "%{$q}%")
            ->orWhere('phone', 'like', "%{$q}%")
            ->orWhere('code', 'like', "%{$q}%")
            ->limit(10)
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'code' => $c->code,
                'name' => $c->name,
                'phone' => $c->phone,
                'available_points' => $c->available_points,
                'tier' => $c->tier,
            ]);

        return response()->json($customers);
    }
}
