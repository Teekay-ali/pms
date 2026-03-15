<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\Invoice;
use App\Models\Payment;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{

    public function index()
    {
        $this->authorize('create', Payment::class); // reuse — anyone who can manage payments can view

        $payments = Payment::with(['creator', 'payable'])
            ->latest()
            ->paginate(20)
            ->through(fn($p) => [
                'id'             => $p->id,
                'amount'         => $p->amount,
                'method'         => $p->method,
                'payment_date'   => $p->payment_date,
                'reference'      => $p->reference,
                'notes'          => $p->notes,
                'payable_type'   => class_basename($p->payable_type),
                'payable_number' => $p->payable?->invoice_number ?? $p->payable?->bill_number ?? '—',
                'created_by'     => $p->creator?->name,
            ]);

        return Inertia::render('Finance/Payments/Index', [
            'payments' => $payments,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Payment::class);

        $validated = $request->validate([
            'payable_type'   => 'required|in:invoice,bill',
            'payable_id'     => 'required|integer',
            'amount'         => 'required|numeric|min:0.01',
            'payment_date'   => 'required|date',
            'method'         => 'required|in:cash,bank_transfer,cheque,card,other',
            'reference'      => 'nullable|string|max:255',
            'notes'          => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($validated) {
            $modelClass = $validated['payable_type'] === 'invoice' ? Invoice::class : Bill::class;
            $payable    = $modelClass::findOrFail($validated['payable_id']);

            // Cap payment at remaining balance
            $balance = $payable->balance;
            $amount  = min((float) $validated['amount'], $balance);

            Payment::create([
                'created_by'   => auth()->id(),
                'payable_type' => $modelClass,
                'payable_id'   => $payable->id,
                'amount'       => $amount,
                'payment_date' => $validated['payment_date'],
                'method'       => $validated['method'],
                'reference'    => $validated['reference'] ?? null,
                'notes'        => $validated['notes'] ?? null,
            ]);

            $payable->increment('amount_paid', $amount);
            $payable->updateStatus();
        });

        return redirect()->back()->with('success', 'Payment recorded.');
    }

    public function update(Request $request, Payment $payment)
    {
        $this->authorize('update', $payment);

        $validated = $request->validate([
            'amount'       => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'method'       => 'required|in:cash,bank_transfer,cheque,card,other',
            'reference'    => 'nullable|string|max:255',
            'notes'        => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($validated, $payment) {
            $payable    = $payment->payable;
            $difference = (float) $validated['amount'] - (float) $payment->amount;

            $payment->update($validated);
            $payable->increment('amount_paid', $difference);
            $payable->updateStatus();
        });

        return redirect()->back()->with('success', 'Payment updated.');
    }

    public function destroy(Payment $payment)
    {
        $this->authorize('delete', $payment);

        DB::transaction(function () use ($payment) {
            $payable = $payment->payable;
            $payable->decrement('amount_paid', $payment->amount);
            $payment->delete();
            $payable->updateStatus();
        });

        return redirect()->back()->with('success', 'Payment deleted.');
    }
}
