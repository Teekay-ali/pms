<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1e293b; background: #fff; }
        .header { background: #1e293b; color: white; padding: 20px 24px; margin-bottom: 20px; }
        .header h1 { font-size: 18px; font-weight: 700; }
        .header p { font-size: 10px; color: #94a3b8; margin-top: 4px; }
        .meta { padding: 0 24px 16px; display: flex; gap: 24px; font-size: 10px; color: #64748b; }
        table { width: 100%; border-collapse: collapse; margin: 0 24px; width: calc(100% - 48px); }
        th { background: #f1f5f9; padding: 8px 10px; text-align: left; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #475569; border-bottom: 2px solid #e2e8f0; }
        td { padding: 8px 10px; border-bottom: 1px solid #f1f5f9; font-size: 10px; }
        tr:hover td { background: #f8fafc; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 20px; font-size: 9px; font-weight: 600; }
        .active    { background: #dcfce7; color: #166534; }
        .planning  { background: #f1f5f9; color: #475569; }
        .on_hold   { background: #fef9c3; color: #854d0e; }
        .completed { background: #dbeafe; color: #1e40af; }
        .cancelled { background: #fee2e2; color: #991b1b; }
        .footer { margin-top: 20px; padding: 12px 24px; border-top: 1px solid #e2e8f0; font-size: 9px; color: #94a3b8; display: flex; justify-content: space-between; }
        .summary { display: flex; gap: 16px; padding: 0 24px 20px; }
        .stat { flex: 1; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; }
        .stat-value { font-size: 16px; font-weight: 700; color: #1e293b; }
        .stat-label { font-size: 9px; color: #94a3b8; margin-top: 2px; text-transform: uppercase; letter-spacing: 0.05em; }
    </style>
</head>
<body>
<div class="header">
    <h1>Project Summary Report</h1>
    <p>Generated {{ now()->format('d M Y, H:i') }} · SitePro</p>
</div>

<div class="summary">
    <div class="stat">
        <div class="stat-value">{{ $projects->count() }}</div>
        <div class="stat-label">Total Projects</div>
    </div>
    <div class="stat">
        <div class="stat-value">{{ $projects->where('status','active')->count() }}</div>
        <div class="stat-label">Active</div>
    </div>
    <div class="stat">
        <div class="stat-value">₦{{ number_format($projects->sum('budget'), 0) }}</div>
        <div class="stat-label">Total Budget</div>
    </div>
    <div class="stat">
        <div class="stat-value">₦{{ number_format($totalSpent, 0) }}</div>
        <div class="stat-label">Total Spent</div>
    </div>
</div>

<table>
    <thead>
    <tr>
        <th>Project</th>
        <th>Status</th>
        <th>Manager</th>
        <th>Budget (₦)</th>
        <th>Spent (₦)</th>
        <th>Used %</th>
        <th>Progress</th>
    </tr>
    </thead>
    <tbody>
    @foreach($projects as $project)
        @php
            $spent     = $project->expenses->where('status','approved')->sum('amount');
            $budgetPct = $project->budget > 0 ? round(($spent / $project->budget) * 100, 1) : 0;
            $total     = $project->tasks->count();
            $completed = $project->tasks->where('status','completed')->count();
            $progress  = $total > 0 ? round(($completed / $total) * 100, 1) : 0;
        @endphp
        <tr>
            <td><strong>{{ $project->name }}</strong></td>
            <td><span class="badge {{ $project->status }}">{{ ucfirst($project->status) }}</span></td>
            <td>{{ $project->projectManager?->name ?? '—' }}</td>
            <td>{{ number_format($project->budget, 0) }}</td>
            <td>{{ number_format($spent, 0) }}</td>
            <td>{{ $budgetPct }}%</td>
            <td>{{ $progress }}%</td>
        </tr>
    @endforeach
    </tbody>
</table>

<div class="footer">
    <span>SitePro — Project Summary Report</span>
    <span>{{ now()->format('d M Y') }}</span>
</div>
</body>
</html>
