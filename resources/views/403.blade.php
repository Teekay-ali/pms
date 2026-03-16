<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 - Forbidden | SitePro</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            min-height: 100vh;
            background: #0f172a;
            color: #f8fafc;
            font-family: 'Figtree', ui-sans-serif, system-ui, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        .card {
            text-align: center;
            max-width: 480px;
            width: 100%;
        }
        .badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 80px;
            height: 80px;
            background: rgba(239,68,68,0.1);
            border: 1px solid rgba(239,68,68,0.2);
            border-radius: 20px;
            margin-bottom: 1.5rem;
        }
        .badge svg { width: 36px; height: 36px; color: #f87171; }
        .code {
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: #f87171;
            margin-bottom: 0.75rem;
        }
        h1 {
            font-size: 1.75rem;
            font-weight: 700;
            color: #f8fafc;
            margin-bottom: 0.75rem;
        }
        p {
            color: #94a3b8;
            font-size: 0.95rem;
            line-height: 1.6;
            margin-bottom: 2rem;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 1.25rem;
            background: #4f46e5;
            color: #fff;
            text-decoration: none;
            border-radius: 10px;
            font-size: 0.875rem;
            font-weight: 600;
            transition: background 0.15s;
        }
        .btn:hover { background: #4338ca; }
        .divider {
            width: 40px;
            height: 3px;
            background: linear-gradient(90deg, #4f46e5, #818cf8);
            border-radius: 2px;
            margin: 0 auto 1.5rem;
        }
    </style>
</head>
<body>
<div class="card">
    <div class="badge">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
        </svg>
    </div>
    <p class="code">Error 403</p>
    <div class="divider"></div>
    <h1>Access Denied</h1>
    <p>You don't have permission to view this page. If you believe this is a mistake, contact your administrator.</p>
    <a href="{{ url('/dashboard') }}" class="btn">
        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        Back to Dashboard
    </a>
</div>
</body>
</html>
