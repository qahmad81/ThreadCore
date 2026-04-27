<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name', 'ThreadCore') }}</title>
    <style>
        :root {
            color-scheme: light;
            --bg: #f6f7f9;
            --panel: #ffffff;
            --ink: #17202a;
            --muted: #667085;
            --line: #d9dee7;
            --accent: #2563eb;
            --ok: #15803d;
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            background: var(--bg);
            color: var(--ink);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        a { color: inherit; }
        .shell { max-width: 1120px; margin: 0 auto; padding: 32px 20px; }
        .topbar { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 28px; }
        .brand { display: flex; flex-direction: column; gap: 3px; }
        .brand strong { font-size: 20px; letter-spacing: 0; }
        .brand span, .muted { color: var(--muted); font-size: 14px; }
        .panel { background: var(--panel); border: 1px solid var(--line); border-radius: 8px; }
        .panel-pad { padding: 22px; }
        .button {
            appearance: none;
            border: 1px solid var(--line);
            background: #fff;
            border-radius: 6px;
            color: var(--ink);
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font: inherit;
            min-height: 38px;
            padding: 0 14px;
            text-decoration: none;
        }
        .button.primary { background: var(--accent); border-color: var(--accent); color: #fff; }
        .button.danger { color: #b42318; }
        .grid { display: grid; gap: 16px; }
        .two { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .provider { padding: 18px; }
        .provider-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 14px; margin-bottom: 14px; }
        .provider h2 { font-size: 18px; margin: 0 0 4px; }
        .badge { border: 1px solid var(--line); border-radius: 999px; color: var(--muted); display: inline-flex; font-size: 12px; padding: 4px 9px; }
        .badge.ok { border-color: #bbf7d0; color: var(--ok); background: #f0fdf4; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border-top: 1px solid var(--line); padding: 10px 0; text-align: left; vertical-align: top; }
        th { color: var(--muted); font-size: 12px; font-weight: 600; text-transform: uppercase; }
        .login-wrap { max-width: 420px; margin: 8vh auto; padding: 0 20px; }
        label { display: block; font-size: 14px; font-weight: 600; margin: 14px 0 6px; }
        input[type="email"], input[type="password"] {
            border: 1px solid var(--line);
            border-radius: 6px;
            font: inherit;
            min-height: 42px;
            padding: 0 12px;
            width: 100%;
        }
        input[type="text"], input[type="url"], input[type="number"], textarea, select {
            border: 1px solid var(--line);
            border-radius: 6px;
            font: inherit;
            min-height: 42px;
            padding: 8px 12px;
            width: 100%;
        }
        textarea { min-height: 110px; resize: vertical; }
        .nav {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin: 18px 0 0;
        }
        .nav a {
            border: 1px solid var(--line);
            border-radius: 999px;
            color: var(--muted);
            font-size: 13px;
            padding: 7px 11px;
            text-decoration: none;
        }
        .actions { display: flex; flex-wrap: wrap; gap: 8px; }
        .alert { border-radius: 8px; margin-bottom: 16px; padding: 12px 14px; }
        .alert.ok { background: #f0fdf4; color: #166534; }
        .alert.error { background: #fef2f2; color: #b42318; }
        .stat { padding: 18px; }
        .stat strong { display: block; font-size: 28px; }
        code, pre { font-family: "Cascadia Mono", Consolas, monospace; }
        pre { background: #101828; border-radius: 8px; color: #f8fafc; overflow: auto; padding: 16px; }
        .error { color: #b42318; font-size: 14px; margin-top: 10px; }
        .landing { min-height: 100vh; }
        .landing-nav {
            align-items: center;
            display: flex;
            justify-content: space-between;
            margin: 0 auto;
            max-width: 1120px;
            padding: 24px 20px;
        }
        .landing-hero {
            margin: 0 auto;
            max-width: 1120px;
            min-height: 58vh;
            padding: 72px 20px 56px;
        }
        .eyebrow {
            color: var(--accent);
            font-size: 14px;
            font-weight: 700;
            margin: 0 0 18px;
        }
        .landing-hero h1 {
            font-size: clamp(42px, 7vw, 84px);
            letter-spacing: 0;
            line-height: 1;
            margin: 0;
            max-width: 820px;
        }
        .hero-copy {
            color: var(--muted);
            font-size: 20px;
            line-height: 1.55;
            margin: 26px 0 0;
            max-width: 680px;
        }
        .hero-actions {
            align-items: center;
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            margin-top: 32px;
        }
        .landing-band {
            background: var(--panel);
            border-top: 1px solid var(--line);
            display: grid;
            gap: 1px;
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
        .landing-band article {
            min-height: 190px;
            padding: 28px;
        }
        .landing-band span {
            color: var(--accent);
            display: block;
            font-size: 12px;
            font-weight: 700;
            margin-bottom: 14px;
            text-transform: uppercase;
        }
        .landing-band strong { display: block; font-size: 20px; margin-bottom: 10px; }
        .landing-band p { color: var(--muted); line-height: 1.55; margin: 0; }
        @media (max-width: 760px) {
            .landing-hero { padding-top: 42px; }
            .landing-band { grid-template-columns: 1fr; }
            .two { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    {{ $slot }}
</body>
</html>
