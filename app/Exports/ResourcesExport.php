<?php

namespace App\Exports;

use App\Models\Resource;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ResourcesExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function __construct(private array $filters = []) {}

    public function collection()
    {
        return Resource::with('project')
            ->when($this->filters['project_id'] ?? null, fn($q, $id) => $q->where('project_id', $id))
            ->when($this->filters['type'] ?? null,       fn($q, $t)  => $q->where('type', $t))
            ->orderBy('project_id')
            ->get();
    }

    public function headings(): array
    {
        return ['Name', 'Type', 'Project', 'Quantity', 'Unit', 'Unit Cost (₦)', 'Total Value (₦)'];
    }

    public function map($r): array
    {
        return [
            $r->name,
            ucfirst($r->type),
            $r->project?->name ?? '—',
            $r->quantity,
            $r->unit,
            number_format($r->cost, 2),
            number_format($r->quantity * $r->cost, 2),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }

    public function title(): string { return 'Resources'; }
}
