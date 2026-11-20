<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::withCount(['products', 'purchaseOrders']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('contact_person', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%")
                  ->orWhere('phone', 'ilike', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $suppliers = $query->orderBy('name', 'asc')->paginate(10)->withQueryString();

        $activeSuppliersCount = Supplier::where('status', 'active')->count();

        return view('inventory.suppliers.index', compact('suppliers', 'activeSuppliersCount'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255|unique:inventory.suppliers,name',
            'contact_person' => 'nullable|string|max:255',
            'phone'          => 'nullable|string|max:50',
            'email'          => 'nullable|email|max:255',
            'address'        => 'nullable|string|max:500',
            'status'         => ['required', Rule::in(['active', 'inactive'])],
        ]);

        Supplier::create($validated);

        return redirect()->route('inventory.suppliers.index')->with('success', "Supplier <strong>{$request->name}</strong> was successfully registered.");
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name'           => ['required', 'string', 'max:255', Rule::unique('inventory.suppliers', 'name')->ignore($supplier->id)],
            'contact_person' => 'nullable|string|max:255',
            'phone'          => 'nullable|string|max:50',
            'email'          => 'nullable|email|max:255',
            'address'        => 'nullable|string|max:500',
            'status'         => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $supplier->update($validated);

        return redirect()->route('inventory.suppliers.index')->with('success', "Supplier <strong>{$supplier->name}</strong> was updated.");
    }

    public function destroy(Supplier $supplier)
    {
        // Toggle active status safely
        $newStatus = $supplier->status === 'active' ? 'inactive' : 'active';
        $supplier->update(['status' => $newStatus]);

        $msg = $newStatus === 'active' ? 'activated' : 'deactivated';
        return redirect()->back()->with('info', "Supplier <strong>{$supplier->name}</strong> has been {$msg}.");
    }
}
