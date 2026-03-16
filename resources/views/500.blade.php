<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>500 - Server Error | SitePro</title>
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
            background: rgba(245,158,11,0.1);
            border: 1px solid rgba(245,158,11,0.2);
            border-radius: 20px;
            margin-bottom: 1.5rem;
        }
        .badge svg { width: 36px; height: 36px; color: #fbbf24; }
        .code {
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: #fbbf24;
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
            background: linear-gradient(90deg, #f59e0b, #fbbf24);
            border-radius: 2px;
            margin: 0 auto 1.5rem;
        }
    </style>
</head>
<body>
<div class="card">
    <div class="badge">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437l1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008z" />
        </svg>
    </div>
    <p class="code">Error 500</p>
    <div class="divider"></div>
    <h1>Something went wrong</h1>
    <p>We are experiencing a server issue. This has been logged and we're looking into it. Please try again in a moment.</p>
    <a href="{{ url('/dashboard') }}" class="btn">
        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        Back to Dashboard
    </a>
</div>
</body>
</html>
