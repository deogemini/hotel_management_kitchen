<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = $this->lodgeQuery(Supplier::query())->orderBy('name')->get();
        return view('suppliers.index', compact('suppliers'));
    }

    public function create() { return view('suppliers.create', ['supplier' => new Supplier()]); }

    public function store(Request $request)
    {
        $request->merge(['name' => mb_strtoupper(trim((string) $request->input('name')), 'UTF-8')]);
        $data = $this->validated($request);
        $data['lodge_id'] = auth()->user()?->lodge_id;
        $data['created_by'] = auth()->id();
        $supplier = Supplier::create($data);
        AuditService::log('supplier.create', $supplier, $supplier->getAttributes());
        return redirect()->route('suppliers.index')->with('success', 'Supplier added successfully.');
    }

    public function edit(Supplier $supplier) { return view('suppliers.edit', compact('supplier')); }

    public function update(Request $request, Supplier $supplier)
    {
        $request->merge(['name' => mb_strtoupper(trim((string) $request->input('name')), 'UTF-8')]);
        $data = $this->validated($request, $supplier);
        $supplier->update($data);
        AuditService::log('supplier.update', $supplier, $supplier->getAttributes());
        return redirect()->route('suppliers.index')->with('success', 'Supplier updated successfully.');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();
        return redirect()->route('suppliers.index')->with('success', 'Supplier deleted successfully.');
    }

    private function validated(Request $request, ?Supplier $supplier = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('suppliers', 'name')->where(fn ($q) => $q->where('lodge_id', auth()->user()?->lodge_id))->ignore($supplier?->id)],
            'contact_person' => ['nullable', 'string', 'max:255'], 'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'], 'address' => ['nullable', 'string', 'max:1000'], 'notes' => ['nullable', 'string', 'max:1000'],
        ]);
    }

    private function lodgeQuery($query)
    {
        if (! (auth()->user()?->hasRole('hotel_manager') ?? false)) $query->where('lodge_id', auth()->user()?->lodge_id);
        return $query;
    }
}
