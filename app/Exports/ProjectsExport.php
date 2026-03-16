<?php


namespace App\Exports;

use App\Models\Project;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProjectsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function __construct(private array $filters = [])
    {
    }

    public function collection()
    {
        return Project::with(['projectManager', 'expenses', 'tasks'])
            ->when($this->filters['status'] ?? null, fn($q, $s) => $q->where('status', $s))
            ->get();
    }

    public function headings(): array
    {
        return ['Project', 'Status', 'Manager', 'Start Date', 'End Date', 'Budget (₦)', 'Spent (₦)', 'Remaining (₦)', 'Budget Used %', 'Total Tasks', 'Completed Tasks', 'Progress %'];
    }

    public function map($project): array
    {
        $spent = $project->expenses->where('status', 'approved')->sum('amount');
        $remaining = $project->budget - $spent;
        $budgetPct = $project->budget > 0 ? round(($spent / $project->budget) * 100, 1) : 0;
        $total = $project->tasks->count();
        $completed = $project->tasks->where('status', 'completed')->count();
        $progress = $total > 0 ? round(($completed / $total) * 100, 1) : 0;

        return [
            $project->name,
            ucfirst($project->status),
            $project->projectManager?->name ?? '—',
            $project->start_date?->format('d M Y') ?? '—',
            $project->end_date?->format('d M Y') ?? '—',
            number_format($project->budget, 2),
            number_format($spent, 2),
            number_format($remaining, 2),
            $budgetPct . '%',
            $total,
            $completed,
            $progress . '%',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function title(): string
    {
        return 'Projects';
    }
}
