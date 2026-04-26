<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Offline — Zenner Tasks</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: #000;
            color: #fff;
            font-family: 'Figtree', system-ui, -apple-system, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            text-align: center;
            padding: 2rem;
        }
        img { width: 80px; height: auto; margin-bottom: 1.5rem; opacity: 0.9; }
        h1 { font-size: 1.5rem; font-weight: 700; margin-bottom: 0.5rem; letter-spacing: -0.02em; }
        p { color: #9ca3af; font-size: 0.95rem; max-width: 320px; line-height: 1.6; margin-bottom: 2rem; }
        a {
            display: inline-block;
            background: #fff;
            color: #000;
            font-weight: 600;
            font-size: 0.875rem;
            padding: 0.6rem 1.5rem;
            border-radius: 0.5rem;
            text-decoration: none;
            transition: opacity 0.2s;
        }
        a:hover { opacity: 0.85; }
    </style>
</head>
<body>
    <img src="/images/site-logo-2.png" alt="Zenner Tasks">
    <h1>You're offline</h1>
    <p>No internet connection detected. Please check your network and try again.</p>
    <a href="javascript:window.location.reload()">Try again</a>
</body>
</html>
