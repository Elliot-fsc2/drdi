<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/x-icon">
    <title>Under Maintenance | DRDI NCST</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Calistoga&family=JetBrains+Mono:wght@400;500&display=swap"
        rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="h-full antialiased" style="background: #F8FAFC">
    {{-- Ambient background glows --}}
    <div class="fixed inset-0 pointer-events-none overflow-hidden" aria-hidden="true">
        <div class="absolute -top-32 -right-32 w-[500px] h-[500px] rounded-full"
            style="background: radial-gradient(circle, rgba(0,82,255,0.07), transparent 70%); filter: blur(60px)"></div>
        <div class="absolute bottom-1/3 -left-24 w-[400px] h-[400px] rounded-full"
            style="background: radial-gradient(circle, rgba(77,124,255,0.05), transparent 70%); filter: blur(80px)">
        </div>
    </div>

    <div class="relative flex min-h-full flex-col items-center justify-center px-4 py-16 text-center">
        {{-- Logo --}}
        <img src="{{ asset('images/logo.png') }}" alt="DRDI NCST" class="mb-6 h-16 w-16 rounded-2xl object-cover shadow-lg"
            style="box-shadow: 0 4px 16px rgba(0,82,255,0.15)">

        {{-- Status pill --}}
        <div class="inline-flex items-center gap-2 rounded-full border px-4 py-1.5 mb-6"
            style="border-color: rgba(0,82,255,0.2); background: rgba(0,82,255,0.05)">
            <span class="w-1.5 h-1.5 rounded-full animate-pulse" style="background: #0052FF"></span>
            <span
                style="font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.14em; color: #0052FF; text-transform: uppercase">Scheduled
                Maintenance</span>
        </div>

        {{-- Heading --}}
        <h1 class="leading-tight max-w-2xl"
            style="font-family: 'Calistoga', Georgia, serif; font-size: clamp(1.85rem, 4vw, 2.75rem); letter-spacing: -0.015em; color: #0F172A">
            We'll be right back<span
                style="background: linear-gradient(to right, #0052FF, #4D7CFF); -webkit-background-clip: text; background-clip: text; color: transparent">.</span>
        </h1>

        <p class="mt-3 max-w-md text-sm leading-relaxed" style="color: #64748B">
            The system is currently under maintenance. Please check back shortly —
            we're making improvements to serve you better.
        </p>

        {{-- Footer --}}
        <p class="mt-12 text-xs" style="font-family: 'JetBrains Mono', monospace; letter-spacing: 0.1em; color: #94A3B8; text-transform: uppercase">
            DRDI &bull; Research Department NCST
        </p>
    </div>
</body>

</html>
