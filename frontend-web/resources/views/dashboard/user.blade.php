@extends('layouts.app')

@section('content')
<section class="hero">
    <p class="eyebrow">Dashboard</p>
    <h1>Welcome back, {{ data_get(session('user'), 'name') }}!</h1>
    <p class="muted">Manage your events and bookings here.</p>
</section>

<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:16px;">
    <a href="{{ route('events.index') }}" class="panel" style="text-decoration:none; display:block;">
        <p style="font-size:14px; font-weight:700; color:var(--text-muted); margin:0 0 8px;">Browse Events</p>
        <p style="font-size:18px; font-weight:700; color:#fff; margin:0;">Discover &rsaquo;</p>
    </a>
    <a href="{{ route('bookings.index') }}" class="panel" style="text-decoration:none; display:block;">
        <p style="font-size:14px; font-weight:700; color:var(--text-muted); margin:0 0 8px;">My Tickets</p>
        <p style="font-size:18px; font-weight:700; color:#fff; margin:0;">View bookings &rsaquo;</p>
    </a>
    @if(data_get(session('user'), 'role') === 'admin')
        <a href="{{ route('dashboard.admin') }}" class="panel" style="text-decoration:none; display:block;">
            <p style="font-size:14px; font-weight:700; color:var(--text-muted); margin:0 0 8px;">Admin Panel</p>
            <p style="font-size:18px; font-weight:700; color:#1ed760; margin:0;">Dashboard &rsaquo;</p>
        </a>
    @endif
</div>
@endsection