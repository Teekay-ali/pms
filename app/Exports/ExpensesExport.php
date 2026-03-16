<?php

namespace App\Exports;

use App\Models\Expense;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExpensesExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function __construct(private array $filters = []) {}

    public function collection()
    {
        return Expense::with(['project', 'creator', 'approvedBy'])
            ->when($this->filters['project_id'] ?? null, fn($q, $id) => $q->where('project_id', $id))
            ->when($this->filters['status'] ?? null,     fn($q, $s)  => $q->where('status', $s))
            ->when($this->filters['from'] ?? null,       fn($q, $d)  => $q->whereDate('date', '>=', $d))
            ->when($this->filters['to'] ?? null,         fn($q, $d)  => $q->whereDate('date', '<=', $d))
            ->orderBy('date', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return ['Description', 'Project', 'Amount (₦)', 'Date', 'Status', 'Submitted By', 'Approved By'];
    }

    public function map($expense): array
    {
        return [
            $expense->description,
            $expense->project?->name ?? '—',
            number_format($expense->amount, 2),
            $expense->date?->format('d M Y') ?? '—',
            ucfirst($expense->status),
            $expense->creator?->name ?? '—',
            $expense->approvedBy?->name ?? '—',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }

    public function title(): string { return 'Expenses'; }
}
