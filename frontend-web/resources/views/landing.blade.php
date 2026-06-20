<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>TicketLab - Discover Amazing Events</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        body {
            background: #000;
            font-family: "SpotifyMixUI", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }

        .landing-wrapper {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 24px;
        }

        /* Top Nav */
        .landing-nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 0;
        }

        .landing-brand {
            font-size: 24px;
            font-weight: 700;
        }

        .landing-brand .green {
            color: var(--brand);
        }

        .landing-brand .white {
            color: #fff;
        }

        .landing-nav .btn {
            padding: 10px 28px;
            font-size: 13px;
        }

        /* Hero */
        .hero-banner {
            background: linear-gradient(135deg, #1ed760 0%, #1a9e4b 100%);
            border-radius: 12px;
            padding: 80px 48px;
            margin: 0 0 48px 0;
            position: relative;
            overflow: hidden;
        }

        .hero-banner::before {
            content: "";
            position: absolute;
            top: -60%;
            right: -20%;
            width: 500px;
            height: 500px;
            border-radius: 50%;
            background: rgba(0, 0, 0, 0.08);
        }

        .hero-banner::after {
            content: "";
            position: absolute;
            bottom: -30%;
            left: 60%;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background: rgba(0, 0, 0, 0.06);
        }

        .hero-content {
            position: relative;
            z-index: 1;
            max-width: 600px;
        }

        .hero-content h1 {
            font-size: 52px;
            font-weight: 700;
            letter-spacing: -2px;
            line-height: 1.1;
            color: #000;
            margin: 0 0 16px 0;
        }

        .hero-content p {
            font-size: 18px;
            color: rgba(0, 0, 0, 0.7);
            margin: 0 0 28px 0;
            line-height: 1.5;
        }

        .hero-btn {
            display: inline-block;
            background: #000;
            color: #fff;
            border-radius: 9999px;
            padding: 14px 44px;
            font-size: 14px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            border: none;
            cursor: pointer;
            transition: transform 0.2s ease, opacity 0.2s ease;
            text-decoration: none;
        }

        .hero-btn:hover {
            transform: scale(1.05);
            color: #fff;
        }

        /* Section Title */
        .section-title {
            font-size: 24px;
            font-weight: 700;
            margin: 0 0 24px 0;
            color: #fff;
        }

        /* Card Grid (Reuse from Spotify CSS) */
        .card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 24px;
            margin-bottom: 48px;
        }

        .card {
            background-color: var(--bg-card);
            border-radius: 8px;
            padding: 16px;
            transition: background-color 0.3s ease, transform 0.2s ease;
            box-shadow: var(--shadow-medium);
            display: flex;
            flex-direction: column;
            text-decoration: none;
            color: inherit;
        }

        .card:hover {
            background-color: var(--bg-card-hover);
        }

        .card img {
            width: 100%;
            aspect-ratio: 1 / 1;
            object-fit: cover;
            border-radius: 6px;
            margin-bottom: 16px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.5);
        }

        .card-body {
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        .card-body .card-eyebrow {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: var(--brand);
            margin: 0 0 4px 0;
        }

        .card-body h3 {
            font-size: 16px;
            font-weight: 700;
            margin: 0 0 8px 0;
            color: #fff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .card-meta {
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 12px;
        }

        .card-meta span {
            display: block;
        }

        .card-price {
            font-size: 16px;
            font-weight: 700;
            color: #fff;
            margin: auto 0 14px 0;
        }

        .card .card-btn {
            width: 100%;
            padding: 8px 16px;
            border-radius: 9999px;
            background-color: var(--brand);
            color: #000;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            border: none;
            cursor: pointer;
            transition: transform 0.2s ease;
            text-align: center;
            text-decoration: none;
            display: inline-block;
        }

        .card .card-btn:hover {
            transform: scale(1.04);
            color: #000;
        }

        .card .card-btn-outline {
            background: transparent;
            color: #fff;
            border: 1px solid var(--text-muted);
        }

        .card .card-btn-outline:hover {
            background: rgba(255,255,255,0.1);
            border-color: #fff;
            color: #fff;
        }

        .no-events {
            color: var(--text-muted);
            font-size: 16px;
            text-align: center;
            padding: 60px 20px;
        }

        .footer {
            text-align: center;
            padding: 32px 0;
            border-top: 1px solid var(--line);
            color: var(--text-muted);
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="landing-wrapper">
        <!-- Top Navigation -->
        <nav class="landing-nav">
            <a href="{{ route('landing') }}" class="landing-brand" style="text-decoration: none;">
                <span class="green">Ticket</span><span class="white">Lab</span>
            </a>
            <div>
                @if(session('token'))
                    <a href="{{ route('events.index') }}" class="btn" style="text-decoration: none;">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="btn" style="text-decoration: none;">Login</a>
                @endif
            </div>
        </nav>

        <!-- Hero Banner -->
        <section class="hero-banner">
            <div class="hero-content">
                <h1>Discover the best events near you.</h1>
                <p>Browse concerts, seminars, festivals, and sport events. Book your tickets instantly with one click.</p>
                @if(session('token'))
                    <a href="{{ route('events.index') }}" class="hero-btn">Browse Events</a>
                @else
                    <a href="{{ route('login') }}" class="hero-btn">Get Started</a>
                @endif
            </div>
        </section>

        <!-- Events Grid -->
        <h2 class="section-title">Trending Events</h2>

        @if(empty($events))
            <div class="no-events">
                <p>No events available yet. Check back again later!</p>
            </div>
        @else
            <div class="card-grid">
                @foreach($events as $event)
                    <article class="card">
                        @if(!empty($event['image_url']))
                            <img src="{{ $event['image_url'] }}" alt="{{ $event['title'] }}">
                        @else
                            <div style="width:100%;aspect-ratio:1;border-radius:6px;margin-bottom:16px;background:#333;"></div>
                        @endif
                        <div class="card-body">
                            <p class="card-eyebrow">{{ $event['category_name'] ?? 'Event' }}</p>
                            <h3>{{ $event['title'] }}</h3>
                            <div class="card-meta">
                                <span>&#128205; {{ $event['location'] }}</span>
                                <span>&#128197; {{ \Illuminate\Support\Carbon::parse($event['date'])->format('d M Y H:i') }}</span>
                            </div>
                            <p class="card-price">Rp {{ number_format($event['price'], 0, ',', '.') }}</p>
                            @if(session('token'))
                                <a href="{{ route('events.show', $event['id']) }}" class="card-btn">View Detail</a>
                            @else
                                <a href="{{ route('login') }}" class="card-btn card-btn-outline">Login to Book</a>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        @endif

        <footer class="footer">
            &copy; {{ date('Y') }} TicketLab. All rights reserved. Built with Laravel &amp; Express.
        </footer>
    </div>
</body>
</html>
