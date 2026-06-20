<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Event Ticketing</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <div class="shell">
        <nav class="topbar">
            <a class="brand" href="{{ route('events.index') }}">TicketLab</a>
            <div class="nav">
                <a href="{{ route('events.index') }}">Events</a>
                <a href="{{ route('bookings.index') }}">My Tickets</a>
                @if(data_get(session('user'), 'role') === 'admin')
                    <a href="{{ route('bookings.admin') }}">All Bookings</a>
                @endif
                <form method="POST" action="{{ route('logout') }}">@csrf <button class="btn-muted" type="submit">Logout</button></form>
            </div>
        </nav>
        <main>@yield('content')</main>
    </div>
</body>
</html>
