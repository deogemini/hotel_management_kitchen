<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Guest;
use App\Models\Invoice;
use App\Models\InvoiceSetting;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class InvoiceController extends Controller
{
    private function authorizeLodge($record): void
    {
        abort_unless(auth()->user()->hasRole('hotel_manager') || $record->lodge_id === auth()->user()->lodge_id, 403);
    }

    public function create(Guest $guest)
    {
        $this->authorizeLodge($guest);
        $companies = Company::visibleTo(auth()->user())->orderBy('name')->get();

        return view('invoices.create', compact('guest', 'companies'));
    }

    private function invoiceData(Request $request): array
    {
        $data = $request->validate([
            'billing_type' => 'required|in:guest,company',
            'company_id' => 'required_if:billing_type,company|nullable|integer',
            'bill_to.name' => 'required_if:billing_type,guest|nullable|string|max:255',
            'bill_to.tin' => 'nullable|string|max:100',
            'bill_to.phone' => 'nullable|string|max:50',
            'bill_to.email' => 'nullable|email|max:255',
            'bill_to.address' => 'nullable|string|max:2000',
            'issued_at' => 'required|date_format:Y-m-d',
            'due_date' => 'nullable|date_format:Y-m-d|after_or_equal:issued_at',
            'notes' => 'nullable|string|max:2000',
            'items' => 'required|array|min:1|max:100',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|integer|min:1|max:1000000',
            'items.*.unit_price' => 'required|numeric|min:0.01|max:999999999.99|decimal:0,2',
        ]);
        $company = null;
        if ($data['billing_type'] === 'company') {
            $company = Company::visibleTo(auth()->user())->find($data['company_id']);
            if (! $company) {
                throw ValidationException::withMessages(['company_id' => 'Choose an available registered company from the list.']);
            }
        }
        $items = collect($data['items'])->map(function ($item) {
            $cents = (int) round((float) $item['unit_price'] * 100);

            return ['item_type' => 'other', 'description' => $item['description'], 'quantity' => $item['quantity'],
                'unit_price' => $cents / 100, 'total_price' => ($cents * $item['quantity']) / 100];
        });
        $total = round($items->sum('total_price'), 2);
        if ($total > 9999999999.99) {
            throw ValidationException::withMessages(['items' => 'Invoice total exceeds the maximum allowed amount.']);
        }

        return [$data, $company, $items, $total];
    }

    public function store(Request $request, Guest $guest)
    {
        $this->authorizeLodge($guest);
        [$data, $company, $items, $total] = $this->invoiceData($request);
        $invoice = DB::transaction(function () use ($data, $guest, $company, $items, $total) {
            $invoice = Invoice::create([
                'invoice_number' => 'INV-'.now()->format('Ymd').'-'.Str::upper((string) Str::ulid()),
                'guest_id' => $guest->id, 'lodge_id' => $guest->lodge_id,
                'company_id' => $company?->id,
                'bill_to' => $company ? $company->only(['name', 'tin', 'phone', 'email', 'address']) : $data['bill_to'],
                'issuer_details' => InvoiceSetting::details(),
                'issued_at' => $data['issued_at'], 'due_date' => $data['due_date'] ?? null,
                'notes' => $data['notes'] ?? null, 'issued_by' => auth()->id(),
                'subtotal' => $total, 'paid_amount' => 0, 'balance_amount' => $total, 'status' => 'Unpaid',
            ]);
            $invoice->items()->createMany($items->all());
            AuditService::log('invoice.create', $invoice, ['invoice_number' => $invoice->invoice_number, 'total' => $total]);

            return $invoice;
        });

        return redirect()->route('invoices.show', $invoice)->with('success', 'Invoice saved. You can confirm payment now or reopen it later from Payments.');
    }

    public function show(Invoice $invoice)
    {
        $this->authorizeLodge($invoice);
        $invoice->load('guest', 'items', 'payments');

        return view('invoices.show', compact('invoice'));
    }

    private function authorizeOwner(): void
    {
        abort_unless(strtolower((string) auth()->user()?->effectiveRoleName()) === 'owner', 403);
    }

    public function edit(Invoice $invoice)
    {
        $this->authorizeOwner();
        $invoice->load('guest', 'items');
        $guest = $invoice->guest;
        $companies = Company::visibleTo(auth()->user())->orderBy('name')->get();

        return view('invoices.create', compact('invoice', 'guest', 'companies'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        $this->authorizeOwner();
        [$data, $company, $items, $total] = $this->invoiceData($request);
        DB::transaction(function () use ($invoice, $data, $company, $items, $total) {
            $invoice = Invoice::whereKey($invoice->id)->lockForUpdate()->firstOrFail();
            if ($total < (float) $invoice->paid_amount) {
                throw ValidationException::withMessages(['items' => 'The invoice total cannot be less than the amount already paid.']);
            }
            $before = $invoice->toArray();
            $before['items'] = $invoice->items->toArray();
            $billTo = $company
                ? ($invoice->company_id === $company->id && $invoice->bill_to ? $invoice->bill_to : $company->only(['name', 'tin', 'phone', 'email', 'address']))
                : $data['bill_to'];
            $invoice->update([
                'company_id' => $company?->id, 'bill_to' => $billTo,
                'issued_at' => $data['issued_at'], 'due_date' => $data['due_date'] ?? null,
                'notes' => $data['notes'] ?? null, 'subtotal' => $total,
                'balance_amount' => round($total - (float) $invoice->paid_amount, 2),
                'status' => $invoice->status === 'Cancelled' ? 'Cancelled' : ($invoice->paid_amount >= $total ? 'Paid' : ($invoice->paid_amount > 0 ? 'Partial' : 'Unpaid')),
            ]);
            $invoice->items()->delete();
            $invoice->items()->createMany($items->all());
            AuditService::log('invoice.update', $invoice, ['from' => $before, 'to' => $invoice->fresh('items')->toArray()]);
        });

        return redirect()->route('invoices.show', $invoice)->with('success', 'Invoice updated.');
    }

    public function destroy(Invoice $invoice)
    {
        $this->authorizeOwner();
        $guestId = $invoice->guest_id;
        DB::transaction(function () use ($invoice) {
            $invoice = Invoice::whereKey($invoice->id)->lockForUpdate()->firstOrFail();
            if ($invoice->paid_amount > 0 || $invoice->payments()->exists()) {
                throw ValidationException::withMessages(['invoice' => 'This invoice has recorded payments. Remove incorrect payments from Payments before deleting the invoice.']);
            }
            AuditService::log('invoice.delete', $invoice, $invoice->load('items')->toArray());
            $invoice->delete();
        });

        return redirect()->route('guests.show', $guestId)->with('success', 'Invoice deleted.');
    }

    public function print(Invoice $invoice)
    {
        $this->authorizeLodge($invoice);
        $invoice->load('guest', 'booking.room', 'items', 'payments');
        $issuer = $invoice->issuer_details ?? InvoiceSetting::details();
        $billTo = $invoice->bill_to ?? ['name' => $invoice->guest?->full_name, 'phone' => $invoice->guest?->phone_number,
            'email' => $invoice->guest?->email, 'address' => $invoice->guest?->address];
        $amountInWords = \App\Support\AmountInWords::format($invoice->subtotal, $issuer['currency']);

        return view('invoices.print', compact('invoice', 'issuer', 'billTo', 'amountInWords'));
    }
}
