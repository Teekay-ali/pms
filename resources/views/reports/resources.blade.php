<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1e293b; }
        .header { background: #1e293b; color: white; padding: 20px 24px; margin-bottom: 20px; }
        .header h1 { font-size: 18px; font-weight: 700; }
        .header p { font-size: 10px; color: #94a3b8; margin-top: 4px; }
        .summary { display: flex; gap: 16px; padding: 0 24px 20px; }
        .stat { flex: 1; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; }
        .stat-value { font-size: 16px; font-weight: 700; color: #1e293b; }
        .stat-label { font-size: 9px; color: #94a3b8; margin-top: 2px; text-transform: uppercase; }
        table { width: calc(100% - 48px); margin: 0 24px; border-collapse: collapse; }
        th { background: #f1f5f9; padding: 8px 10px; text-align: left; font-size: 9px; font-weight: 700; text-transform: uppercase; color: #475569; border-bottom: 2px solid #e2e8f0; }
        td { padding: 8px 10px; border-bottom: 1px solid #f1f5f9; font-size: 10px; }
        .footer { margin-top: 20px; padding: 12px 24px; border-top: 1px solid #e2e8f0; font-size: 9px; color: #94a3b8; display: flex; justify-content: space-between; }
    </style>
</head>
<body>
<div class="header">
    <h1>Resource Utilization Report</h1>
    <p>Generated {{ now()->format('d M Y, H:i') }} · SitePro</p>
</div>

<div class="summary">
    <div class="stat">
        <div class="stat-value">{{ $resources->count() }}</div>
        <div class="stat-label">Total Resources</div>
    </div>
    <div class="stat">
        <div class="stat-value">₦{{ number_format($resources->sum(fn($r) => $r->quantity * $r->cost), 0) }}</div>
        <div class="stat-label">Total Value</div>
    </div>
    <div class="stat">
        <div class="stat-value">{{ $resources->groupBy('type')->count() }}</div>
        <div class="stat-label">Resource Types</div>
    </div>
    <div class="stat">
        <div class="stat-value">{{ $resources->groupBy('project_id')->count() }}</div>
        <div class="stat-label">Projects</div>
    </div>
</div>

<table>
    <thead>
    <tr><th>Name</th><th>Type</th><th>Project</th><th>Quantity</th><th>Unit</th><th>Unit Cost (₦)</th><th>Total Value (₦)</th></tr>
    </thead>
    <tbody>
    @foreach($resources as $r)
        <tr>
            <td>{{ $r->name }}</td>
            <td>{{ ucfirst($r->type) }}</td>
            <td>{{ $r->project?->name ?? '—' }}</td>
            <td>{{ $r->quantity }}</td>
            <td>{{ $r->unit }}</td>
            <td>{{ number_format($r->cost, 2) }}</td>
            <td>{{ number_format($r->quantity * $r->cost, 2) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<div class="footer">
    <span>SitePro — Resource Utilization Report</span>
    <span>{{ now()->format('d M Y') }}</span>
</div>
</body>
</html>
