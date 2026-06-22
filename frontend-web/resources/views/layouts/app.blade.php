<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Event Ticketing</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="app-container">
        <aside class="sidebar">
            <!-- Brand Panel -->
            <div class="sidebar-panel">
                <a class="sidebar-brand" href="{{ route('events.index') }}">
                    <span class="logo-green">Ticket</span>Lab
                </a>
                <div class="nav-links">
                    <a href="{{ route('events.index') }}" class="{{ request()->routeIs('events.index') || request()->routeIs('events.show') ? 'active' : '' }}">
                        <svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor"><path d="M12.5 3.247a1 1 0 0 0-1 0L4 7.577V20h4.5v-6a1 1 0 0 1 1-1h5a1 1 0 0 1 1 1v6H20V7.577l-7.5-4.33zm-2-1.732a3 3 0 0 1 3 0l7.5 4.33A2 2 0 0 1 22 7.577V20a2 2 0 0 1-2 2h-4.5a2 2 0 0 1-2-2v-5h-3v5a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V7.577a2 2 0 0 1 1-1.732l7.5-4.33z"/></svg>
                        <span>Browse Events</span>
                    </a>
                    <a href="{{ route('bookings.index') }}" class="{{ request()->routeIs('bookings.index') || request()->routeIs('bookings.show') ? 'active' : '' }}">
                        <svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor"><path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2zm0 18a8 8 0 1 1 8-8 8 8 0 0 1-8 8zm1-13h-2v6h5v-2h-3z"/></svg>
                        <span>My Tickets</span>
                    </a>
                    @if(data_get(session('user'), 'role') === 'admin')
                        <a href="{{ route('bookings.admin') }}" class="{{ request()->routeIs('bookings.admin') ? 'active' : '' }}">
                            <svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor"><path d="M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10zm0-2a8 8 0 1 0 0-16 8 8 0 0 0 0 16zm-1-5h2v2h-2zm0-8h2v6h-2z"/></svg>
                            <span>All Bookings</span>
                        </a>
                    @endif
                </div>
            </div>

            <!-- User Panel -->
            <div class="sidebar-panel user-panel">
                <div>
                    <h3 style="font-size: 12px; text-transform: uppercase; color: var(--text-muted); letter-spacing: 1.5px; margin: 0 0 16px 0;">Library</h3>
                    <p style="font-size: 13px; color: var(--text-muted); line-height: 1.6; margin: 0;">Create and manage event bookings with ease.</p>
                </div>

                <div>
                    <div class="user-info">
                        <span class="user-badge">{{ strtoupper(substr(data_get(session('user'), 'name', 'U'), 0, 2)) }}</span>
                        <span class="username" title="{{ data_get(session('user'), 'name') }}">{{ data_get(session('user'), 'name') }}</span>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf 
                        <button class="btn btn-logout" type="submit">Logout</button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="main-content">
            @yield('content')
        </main>
    </div>
@stack('scripts')
@if(session('status'))
<script>
Swal.fire({ icon:'success', title:'Berhasil!', text:@json(session('status')), timer:3000, timerProgressBar:true, showConfirmButton:false, background:'#181818', color:'#fff', toast:true, position:'top-end' });
</script>
@endif
@if($errors->any())
<script>
Swal.fire({ icon:'error', title:'Gagal!', text:@json($errors->first()), timer:4000, timerProgressBar:true, showConfirmButton:false, background:'#181818', color:'#fff', toast:true, position:'top-end' });
</script>
@endif
</body>
</html>
