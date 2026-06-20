<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Event Ticketing</title>
</head>
<body>
    <nav>
        <a href="{{ route('events.index') }}">Events</a>
        <a href="{{ route('bookings.index') }}">My Tickets</a>
        <form method="POST" action="{{ route('logout') }}" style="display:inline">@csrf <button type="submit">Logout</button></form>
    </nav>
    <main>@yield('content')</main>
</body>
</html>
