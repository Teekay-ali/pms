<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class InvoiceController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Invoice::class);

        $invoices = Invoice::with(['project', 'creator'])
            ->latest()
            ->paginate(15);

        $projects = Project::select('id', 'name')->orderBy('name')->get();

        return Inertia::render('Finance/Invoices/Index', [
            'invoices' => $invoices,
            'projects' => $projects,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Invoice::class);

        $validated = $request->validate([
            'project_id'     => 'required|exists:projects,id',
            'client_name'    => 'required|string|max:255',
            'client_email'   => 'nullable|email|max:255',
            'client_address' => 'nullable|string|max:500',
            'issue_date'     => 'required|date',
            'due_date'       => 'required|date|after_or_equal:issue_date',
            'tax_rate'       => 'nullable|numeric|min:0|max:100',
            'notes'          => 'nullable|string|max:2000',
            'items'          => 'required|array|min:1',
            'items.*.description' => 'required|string|max:500',
            'items.*.quantity'    => 'required|numeric|min:0.01',
            'items.*.unit_price'  => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($validated, $request) {
            $invoice = Invoice::create([
                'project_id'     => $validated['project_id'],
                'created_by'     => auth()->id(),
                'invoice_number' => $this->nextNumber(),
                'client_name'    => $validated['client_name'],
                'client_email'   => $validated['client_email'] ?? null,
                'client_address' => $validated['client_address'] ?? null,
                'issue_date'     => $validated['issue_date'],
                'due_date'       => $validated['due_date'],
                'tax_rate'       => $validated['tax_rate'] ?? 0,
                'notes'          => $validated['notes'] ?? null,
                'subtotal'       => 0,
                'tax_amount'     => 0,
                'total'          => 0,
            ]);

            foreach ($validated['items'] as $item) {
                $total = round($item['quantity'] * $item['unit_price'], 2);
                $invoice->items()->create([
                    'description' => $item['description'],
                    'quantity'    => $item['quantity'],
                    'unit_price'  => $item['unit_price'],
                    'total'       => $total,
                ]);
            }

            $invoice->recalculate();
        });

        return redirect()->back()->with('success', 'Invoice created.');
    }

    public function update(Request $request, Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        $validated = $request->validate([
            'project_id'     => 'required|exists:projects,id',
            'client_name'    => 'required|string|max:255',
            'client_email'   => 'nullable|email|max:255',
            'client_address' => 'nullable|string|max:500',
            'issue_date'     => 'required|date',
            'due_date'       => 'required|date|after_or_equal:issue_date',
            'tax_rate'       => 'nullable|numeric|min:0|max:100',
            'notes'          => 'nullable|string|max:2000',
            'items'          => 'required|array|min:1',
            'items.*.description' => 'required|string|max:500',
            'items.*.quantity'    => 'required|numeric|min:0.01',
            'items.*.unit_price'  => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($validated, $invoice) {
            $invoice->update([
                'project_id'     => $validated['project_id'],
                'client_name'    => $validated['client_name'],
                'client_email'   => $validated['client_email'] ?? null,
                'client_address' => $validated['client_address'] ?? null,
                'issue_date'     => $validated['issue_date'],
                'due_date'       => $validated['due_date'],
                'tax_rate'       => $validated['tax_rate'] ?? 0,
                'notes'          => $validated['notes'] ?? null,
            ]);

            $invoice->items()->delete();
            foreach ($validated['items'] as $item) {
                $invoice->items()->create([
                    'description' => $item['description'],
                    'quantity'    => $item['quantity'],
                    'unit_price'  => $item['unit_price'],
                    'total'       => round($item['quantity'] * $item['unit_price'], 2),
                ]);
            }

            $invoice->recalculate();
        });

        return redirect()->back()->with('success', 'Invoice updated.');
    }

    public function markSent(Invoice $invoice)
    {
        $this->authorize('update', $invoice);
        $invoice->update(['status' => 'sent']);
        return redirect()->back()->with('success', 'Invoice marked as sent.');
    }

    public function destroy(Invoice $invoice)
    {
        $this->authorize('delete', $invoice);
        $invoice->delete();
        return redirect()->back()->with('success', 'Invoice deleted.');
    }

    private function nextNumber(): string
    {
        $last = Invoice::withTrashed()->max('id') ?? 0;
        return 'INV-' . str_pad($last + 1, 5, '0', STR_PAD_LEFT);
    }
}
