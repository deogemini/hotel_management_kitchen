<?php

namespace App\Http\Controllers;

use App\Models\Invoice;

class InvoiceController extends Controller
{
    public function print(Invoice $invoice)
    {
        $invoice->load('guest', 'booking.room', 'items', 'payments');

        return view('invoices.print', compact('invoice'));
    }
}
