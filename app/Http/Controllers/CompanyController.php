<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function index()
    {
        $companies = Company::query()->when(! auth()->user()->hasRole('hotel_manager'), fn ($q) => $q->where('lodge_id', auth()->user()->lodge_id))->orderBy('name')->get();

        return view('companies.index', compact('companies'));
    }

    public function create()
    {
        return view('companies.form', ['company' => new Company]);
    }

    public function store(Request $request)
    {
        Company::create($this->validated($request) + ['lodge_id' => auth()->user()->lodge_id]);

        return redirect()->route('companies.index')->with('success', 'Company / organization registered.');
    }

    public function edit(Company $company)
    {
        $this->authorizeCompany($company);

        return view('companies.form', compact('company'));
    }

    public function update(Request $request, Company $company)
    {
        $this->authorizeCompany($company);
        $company->update($this->validated($request));

        return redirect()->route('companies.index')->with('success', 'Company / organization updated.');
    }

    private function authorizeCompany(Company $company): void
    {
        abort_unless(auth()->user()->hasRole('hotel_manager') || $company->lodge_id === auth()->user()->lodge_id, 403);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255', 'tin' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:50', 'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:2000',
        ]);
    }
}
