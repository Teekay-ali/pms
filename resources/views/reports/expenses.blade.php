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
        .badge { display: inline-block; padding: 2px 8px; border-radius: 20px; font-size: 9px; font-weight: 600; }
        .pending  { background: #fef9c3; color: #854d0e; }
        .approved { background: #dcfce7; color: #166534; }
        .rejected { background: #fee2e2; color: #991b1b; }
        .footer { margin-top: 20px; padding: 12px 24px; border-top: 1px solid #e2e8f0; font-size: 9px; color: #94a3b8; display: flex; justify-content: space-between; }
    </style>
</head>
<body>
<div class="header">
    <h1>Expense Report</h1>
    <p>Generated {{ now()->format('d M Y, H:i') }} · SitePro{{ isset($filters['from']) ? ' · From ' . $filters['from'] : '' }}{{ isset($filters['to']) ? ' to ' . $filters['to'] : '' }}</p>
</div>

<div class="summary">
    <div class="stat">
        <div class="stat-value">{{ $expenses->count() }}</div>
        <div class="stat-label">Total Entries</div>
    </div>
    <div class="stat">
        <div class="stat-value">₦{{ number_format($expenses->sum('amount'), 0) }}</div>
        <div class="stat-label">Total Amount</div>
    </div>
    <div class="stat">
        <div class="stat-value">₦{{ number_format($expenses->where('status','approved')->sum('amount'), 0) }}</div>
        <div class="stat-label">Approved</div>
    </div>
    <div class="stat">
        <div class="stat-value">₦{{ number_format($expenses->where('status','pending')->sum('amount'), 0) }}</div>
        <div class="stat-label">Pending</div>
    </div>
</div>

<table>
    <thead>
    <tr>
        <th>Description</th>
        <th>Project</th>
        <th>Amount (₦)</th>
        <th>Date</th>
        <th>Status</th>
        <th>Submitted By</th>
    </tr>
    </thead>
    <tbody>
    @foreach($expenses as $expense)
        <tr>
            <td>{{ $expense->description }}</td>
            <td>{{ $expense->project?->name ?? '—' }}</td>
            <td>{{ number_format($expense->amount, 2) }}</td>
            <td>{{ $expense->date?->format('d M Y') }}</td>
            <td><span class="badge {{ $expense->status }}">{{ ucfirst($expense->status) }}</span></td>
            <td>{{ $expense->creator?->name ?? '—' }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<div class="footer">
    <span>SitePro — Expense Report</span>
    <span>{{ now()->format('d M Y') }}</span>
</div>
</body>
</html>
