<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>404 - Not Found | SitePro</title>
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
        .card { text-align: center; max-width: 480px; width: 100%; }
        .badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 80px;
            height: 80px;
            background: rgba(99,102,241,0.1);
            border: 1px solid rgba(99,102,241,0.2);
            border-radius: 20px;
            margin-bottom: 1.5rem;
        }
        .badge svg { width: 36px; height: 36px; color: #818cf8; }
        .code {
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: #818cf8;
            margin-bottom: 0.75rem;
        }
        h1 { font-size: 1.75rem; font-weight: 700; color: #f8fafc; margin-bottom: 0.75rem; }
        p { color: #94a3b8; font-size: 0.95rem; line-height: 1.6; margin-bottom: 2rem; }
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
        .big-code {
            font-size: 6rem;
            font-weight: 800;
            color: rgba(99,102,241,0.15);
            line-height: 1;
            margin-bottom: 1rem;
            letter-spacing: -0.05em;
        }
    </style>
</head>
<body>
<div class="card">
    <div class="big-code">404</div>
    <p class="code">Page Not Found</p>
    <div class="divider"></div>
    <h1>Nothing here</h1>
    <p>The page you're looking for doesn't exist or has been moved. Double-check the URL or head back to the dashboard.</p>
    <a href="{{ url('/dashboard') }}" class="btn">
        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        Back to Dashboard
    </a>
</div>
</body>
</html>
