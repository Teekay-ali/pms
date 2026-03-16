<?php

namespace App\Exports;

use App\Models\Bill;
use App\Models\Invoice;
use App\Models\Payment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FinanceExport implements WithMultipleSheets
{
    public function __construct(private array $filters = []) {}

    public function sheets(): array
    {
        return [
            new InvoicesSheet($this->filters),
            new BillsSheet($this->filters),
            new PaymentsSheet($this->filters),
        ];
    }
}

class InvoicesSheet implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles
{
    public function __construct(private array $filters = []) {}

    public function collection()
    {
        return Invoice::with(['project', 'creator'])
            ->when($this->filters['from'] ?? null, fn($q, $d) => $q->whereDate('issue_date', '>=', $d))
            ->when($this->filters['to'] ?? null,   fn($q, $d) => $q->whereDate('issue_date', '<=', $d))
            ->orderBy('issue_date', 'desc')->get();
    }

    public function headings(): array
    {
        return ['Invoice #', 'Client', 'Project', 'Issue Date', 'Due Date', 'Total (₦)', 'Paid (₦)', 'Balance (₦)', 'Status'];
    }

    public function map($inv): array
    {
        return [
            $inv->invoice_number,
            $inv->client_name,
            $inv->project?->name ?? '—',
            $inv->issue_date?->format('d M Y'),
            $inv->due_date?->format('d M Y'),
            number_format($inv->total, 2),
            number_format($inv->amount_paid, 2),
            number_format($inv->total - $inv->amount_paid, 2),
            ucfirst($inv->status),
        ];
    }

    public function styles(Worksheet $sheet): array { return [1 => ['font' => ['bold' => true]]]; }
    public function title(): string { return 'Invoices'; }
}

class BillsSheet implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles
{
    public function __construct(private array $filters = []) {}

    public function collection()
    {
        return Bill::with(['project', 'vendor'])
            ->when($this->filters['from'] ?? null, fn($q, $d) => $q->whereDate('issue_date', '>=', $d))
            ->when($this->filters['to'] ?? null,   fn($q, $d) => $q->whereDate('issue_date', '<=', $d))
            ->orderBy('issue_date', 'desc')->get();
    }

    public function headings(): array
    {
        return ['Bill #', 'Description', 'Vendor', 'Project', 'Issue Date', 'Due Date', 'Amount (₦)', 'Paid (₦)', 'Balance (₦)', 'Status'];
    }

    public function map($bill): array
    {
        return [
            $bill->bill_number,
            $bill->description,
            $bill->vendor?->name ?? '—',
            $bill->project?->name ?? '—',
            $bill->issue_date?->format('d M Y'),
            $bill->due_date?->format('d M Y'),
            number_format($bill->amount, 2),
            number_format($bill->amount_paid, 2),
            number_format($bill->amount - $bill->amount_paid, 2),
            ucfirst($bill->status),
        ];
    }

    public function styles(Worksheet $sheet): array { return [1 => ['font' => ['bold' => true]]]; }
    public function title(): string { return 'Bills'; }
}

class PaymentsSheet implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles
{
    public function __construct(private array $filters = []) {}

    public function collection()
    {
        return Payment::with(['creator', 'payable'])
            ->when($this->filters['from'] ?? null, fn($q, $d) => $q->whereDate('payment_date', '>=', $d))
            ->when($this->filters['to'] ?? null,   fn($q, $d) => $q->whereDate('payment_date', '<=', $d))
            ->orderBy('payment_date', 'desc')->get();
    }

    public function headings(): array
    {
        return ['Type', 'Reference', 'Amount (₦)', 'Date', 'Method', 'Recorded By'];
    }

    public function map($p): array
    {
        return [
            class_basename($p->payable_type),
            $p->payable?->invoice_number ?? $p->payable?->bill_number ?? '—',
            number_format($p->amount, 2),
            $p->payment_date?->format('d M Y'),
            ucwords(str_replace('_', ' ', $p->method)),
            $p->creator?->name ?? '—',
        ];
    }

    public function styles(Worksheet $sheet): array { return [1 => ['font' => ['bold' => true]]]; }
    public function title(): string { return 'Payments'; }
}
