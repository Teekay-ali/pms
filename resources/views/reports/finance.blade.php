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
        .section-title { font-size: 12px; font-weight: 700; padding: 16px 24px 8px; color: #1e293b; border-top: 2px solid #e2e8f0; margin-top: 16px; }
        table { width: calc(100% - 48px); margin: 0 24px; border-collapse: collapse; }
        th { background: #f1f5f9; padding: 8px 10px; text-align: left; font-size: 9px; font-weight: 700; text-transform: uppercase; color: #475569; border-bottom: 2px solid #e2e8f0; }
        td { padding: 8px 10px; border-bottom: 1px solid #f1f5f9; font-size: 10px; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 20px; font-size: 9px; font-weight: 600; }
        .paid     { background: #dcfce7; color: #166534; }
        .pending  { background: #fef9c3; color: #854d0e; }
        .overdue  { background: #fee2e2; color: #991b1b; }
        .draft    { background: #f1f5f9; color: #475569; }
        .approved { background: #dbeafe; color: #1e40af; }
        .footer { margin-top: 20px; padding: 12px 24px; border-top: 1px solid #e2e8f0; font-size: 9px; color: #94a3b8; display: flex; justify-content: space-between; }
    </style>
</head>
<body>
<div class="header">
    <h1>Finance Report</h1>
    <p>Generated {{ now()->format('d M Y, H:i') }} · SitePro</p>
</div>

<div class="summary">
    <div class="stat">
        <div class="stat-value">₦{{ number_format($invoices->sum('total'), 0) }}</div>
        <div class="stat-label">Total Invoiced</div>
    </div>
    <div class="stat">
        <div class="stat-value">₦{{ number_format($invoices->sum('amount_paid'), 0) }}</div>
        <div class="stat-label">Collected</div>
    </div>
    <div class="stat">
        <div class="stat-value">₦{{ number_format($bills->sum('amount'), 0) }}</div>
        <div class="stat-label">Total Bills</div>
    </div>
    <div class="stat">
        <div class="stat-value">₦{{ number_format($bills->sum('amount_paid'), 0) }}</div>
        <div class="stat-label">Bills Paid</div>
    </div>
</div>

<div class="section-title">Invoices</div>
<table>
    <thead>
    <tr><th>Invoice #</th><th>Client</th><th>Project</th><th>Total (₦)</th><th>Paid (₦)</th><th>Balance (₦)</th><th>Status</th></tr>
    </thead>
    <tbody>
    @foreach($invoices as $inv)
        <tr>
            <td>{{ $inv->invoice_number }}</td>
            <td>{{ $inv->client_name }}</td>
            <td>{{ $inv->project?->name ?? '—' }}</td>
            <td>{{ number_format($inv->total, 2) }}</td>
            <td>{{ number_format($inv->amount_paid, 2) }}</td>
            <td>{{ number_format($inv->total - $inv->amount_paid, 2) }}</td>
            <td><span class="badge {{ $inv->status }}">{{ ucfirst($inv->status) }}</span></td>
        </tr>
    @endforeach
    </tbody>
</table>

<div class="section-title">Bills</div>
<table>
    <thead>
    <tr><th>Bill #</th><th>Description</th><th>Vendor</th><th>Amount (₦)</th><th>Paid (₦)</th><th>Balance (₦)</th><th>Status</th></tr>
    </thead>
    <tbody>
    @foreach($bills as $bill)
        <tr>
            <td>{{ $bill->bill_number }}</td>
            <td>{{ \Str::limit($bill->description, 30) }}</td>
            <td>{{ $bill->vendor?->name ?? '—' }}</td>
            <td>{{ number_format($bill->amount, 2) }}</td>
            <td>{{ number_format($bill->amount_paid, 2) }}</td>
            <td>{{ number_format($bill->amount - $bill->amount_paid, 2) }}</td>
            <td><span class="badge {{ $bill->status }}">{{ ucfirst($bill->status) }}</span></td>
        </tr>
    @endforeach
    </tbody>
</table>

<div class="footer">
    <span>SitePro — Finance Report</span>
    <span>{{ now()->format('d M Y') }}</span>
</div>
</body>
</html>
