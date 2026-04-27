<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name', 'ThreadCore') }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&family=IBM+Plex+Mono:wght@500;600&display=swap');

        :root {
            color-scheme: light;
            --bg: #f6f7f9;
            --panel: #ffffff;
            --ink: #17202a;
            --muted: #667085;
            --line: #d9dee7;
            --accent: #2563eb;
            --accent-strong: #0f766e;
            --surface-warm: #f8faf7;
            --ok: #15803d;
            --fs-label: 12px;
            --fs-nav: 14px;
            --fs-body: 16px;
            --fs-copy: 17px;
            --fs-h1: clamp(22px, 3.2vw, 32px);
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            background: var(--bg);
            color: var(--ink);
            font-family: "IBM Plex Sans", "Segoe UI", ui-sans-serif, system-ui, sans-serif;
            font-size: var(--fs-body);
            line-height: 1.55;
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
            font-size: var(--fs-nav);
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
        .progress { background: #e5e7eb; border-radius: 999px; height: 10px; overflow: hidden; width: 100%; }
        .progress span { background: var(--accent); display: block; height: 100%; }
        .section-title { align-items: center; display: flex; justify-content: space-between; gap: 12px; margin: 24px 0 12px; }
        .section-title h2 { font-size: 18px; margin: 0; }
        .empty { color: var(--muted); padding: 18px; }
        code, pre { font-family: "Cascadia Mono", Consolas, monospace; }
        pre { background: #101828; border-radius: 8px; color: #f8fafc; overflow: auto; padding: 16px; }
        .error { color: #b42318; font-size: 14px; margin-top: 10px; }
        .landing {
            background:
                linear-gradient(140deg, rgba(37, 99, 235, 0.10), transparent 32%),
                radial-gradient(circle at 88% 12%, rgba(15, 118, 110, 0.16), transparent 30%),
                var(--surface-warm);
            min-height: 100vh;
        }
        .landing-nav {
            align-items: center;
            display: flex;
            justify-content: space-between;
            margin: 0 auto;
            max-width: 1120px;
            padding: 24px 20px;
        }
        .landing-wordmark {
            font-size: 20px;
            letter-spacing: 0;
        }
        .landing-hero {
            margin: 0 auto;
            max-width: 1120px;
            min-height: 48vh;
            padding: 48px 20px 34px;
        }
        .hero-main {
            align-items: center;
            display: grid;
            gap: 30px;
            grid-template-columns: minmax(0, 1.45fr) minmax(300px, 0.55fr);
        }
        .eyebrow {
            color: var(--accent-strong);
            font-size: var(--fs-label);
            font-weight: 700;
            margin: 0 0 16px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }
        .landing-hero h1 {
            font-size: var(--fs-h1);
            letter-spacing: 0;
            line-height: 1;
            margin: 0;
        }
        .hero-copy {
            color: var(--muted);
            font-size: var(--fs-copy);
            line-height: 1.6;
            margin: 24px 0 0;
            max-width: 600px;
        }
        .hero-actions {
            align-items: center;
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            margin-top: 28px;
        }
        .hero-console {
            background: rgba(255, 255, 255, 0.82);
            border: 1px solid rgba(23, 32, 42, 0.12);
            border-radius: 8px;
            box-shadow: 0 24px 70px rgba(23, 32, 42, 0.12);
            padding: 18px;
        }
        .console-head {
            align-items: center;
            border-bottom: 1px solid var(--line);
            display: flex;
            gap: 10px;
            padding-bottom: 12px;
        }
        .console-head span {
            background: var(--accent-strong);
            border-radius: 999px;
            height: 10px;
            width: 10px;
        }
        .hero-console ol {
            counter-reset: none;
            list-style: none;
            margin: 14px 0 0;
            padding: 0;
        }
        .hero-console li {
            align-items: center;
            border-bottom: 1px solid rgba(217, 222, 231, 0.72);
            display: grid;
            font-weight: 650;
            gap: 12px;
            grid-template-columns: 42px 1fr;
            padding: 12px 0;
        }
        .hero-console li:last-child { border-bottom: 0; }
        .hero-console li span {
            color: var(--accent-strong);
            font-family: "IBM Plex Mono", Consolas, monospace;
            font-size: 12px;
        }
        .landing-band {
            margin: 0 auto;
            max-width: 1120px;
            padding: 0 20px 30px;
        }
        .landing-band-grid {
            display: grid;
            gap: 16px;
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
        .landing-band article {
            background: rgba(255, 255, 255, 0.74);
            border: 1px solid rgba(217, 222, 231, 0.9);
            border-radius: 8px;
            min-height: 168px;
            padding: 24px;
        }
        .landing-band span {
            color: var(--accent);
            display: block;
            font-size: 11px;
            font-weight: 700;
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }
        .landing-band strong {
            display: block;
            font-size: 18px;
            margin-bottom: 10px;
            line-height: 1.25;
        }
        .landing-band p { color: var(--muted); line-height: 1.6; margin: 0; }
        .landing-footer {
            border-top: 1px solid rgba(217, 222, 231, 0.85);
            color: var(--muted);
            font-size: 13px;
            margin: 0 auto;
            max-width: 1120px;
            padding: 20px 20px 34px;
        }
        .landing-footer-row {
            align-items: center;
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            justify-content: space-between;
        }
        .landing-footer-links {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }
        .landing-footer a {
            color: var(--muted);
            text-decoration: none;
        }
        .landing-footer a:hover {
            color: var(--ink);
        }
        @media (max-width: 760px) {
            .landing-hero { padding-top: 42px; }
            .landing-nav { align-items: flex-start; flex-direction: column; }
            .hero-main { grid-template-columns: 1fr; }
            .hero-actions .button { width: 100%; }
            .landing-band-grid { grid-template-columns: 1fr; }
            .landing-footer-row { align-items: flex-start; flex-direction: column; }
            .two { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    {{ $slot }}
</body>
</html>
