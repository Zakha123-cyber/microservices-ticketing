<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin — Event Ticketing</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <div class="app-container">
        <aside class="sidebar">
            <div class="sidebar-panel">
                <a class="sidebar-brand" href="{{ route('dashboard.admin') }}">
                    <span class="logo-green">Ticket</span>Lab
                    <span style="font-size:10px; color:var(--brand); font-weight:700; text-transform:uppercase; letter-spacing:1px; margin-left:auto;">Admin</span>
                </a>
                <div class="nav-links">
                    <a href="{{ route('dashboard.admin') }}" class="{{ request()->routeIs('dashboard.admin') ? 'active' : '' }}">
                        <svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-13h2v6h-2zm0 8h2v2h-2z"/></svg>
                        <span>Dashboard</span>
                    </a>
                    <a href="{{ route('admin.events') }}" class="{{ request()->routeIs('admin.events') ? 'active' : '' }}">
                        <svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14zM7 10h2v7H7zm4-3h2v10h-2zm4 6h2v4h-2z"/></svg>
                        <span>Manajemen Event</span>
                    </a>
                    <a href="{{ route('admin.tickets') }}" class="{{ request()->routeIs('admin.tickets') ? 'active' : '' }}">
                        <svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor"><path d="M4 6H2v14c0 1.1.9 2 2 2h14v-2H4V6zm16-4H8c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H8V4h12v12zM10 9h8v2h-8zm0 3h4v2h-4zm0-6h8v2h-8z"/></svg>
                        <span>Manajemen Tiket</span>
                    </a>
                    <a href="{{ route('admin.transactions') }}" class="{{ request()->routeIs('admin.transactions') ? 'active' : '' }}">
                        <svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor"><path d="M11.8 10.9c-2.27-.59-3-1.2-3-2.15 0-1.09 1.01-1.85 2.7-1.85 1.78 0 2.44.85 2.5 2.1h2.21c-.07-1.72-1.12-3.3-3.21-3.81V3h-3v2.16c-1.94.42-3.5 1.68-3.5 3.61 0 2.31 1.91 3.46 4.7 4.13 2.5.6 3 1.48 3 2.41 0 .69-.49 1.79-2.7 1.79-2.06 0-2.87-.92-2.98-2.1h-2.2c.12 2.19 1.76 3.42 3.68 3.83V21h3v-2.15c1.95-.37 3.5-1.5 3.5-3.55 0-2.84-2.43-3.81-4.7-4.4z"/></svg>
                        <span>Manajemen Transaksi</span>
                    </a>
                    <a href="{{ route('admin.verify') }}" class="{{ request()->routeIs('admin.verify') ? 'active' : '' }}">
                        <svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/></svg>
                        <span>Verifikasi Tiket</span>
                    </a>
                </div>
            </div>

            <div class="sidebar-panel user-panel">
                <div>
                    <h3 style="font-size: 12px; text-transform: uppercase; color: var(--text-muted); letter-spacing: 1.5px; margin: 0 0 16px 0;">Administrator</h3>
                    <p style="font-size: 13px; color: var(--text-muted); line-height: 1.6; margin: 0;">Manajemen penuh event, tiket, dan transaksi.</p>
                </div>
                <div>
                    <div class="user-info">
                        <span class="user-badge">{{ strtoupper(substr(data_get(session('user'), 'name', 'A'), 0, 2)) }}</span>
                        <span class="username" title="{{ data_get(session('user'), 'name') }}">{{ data_get(session('user'), 'name') }}</span>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="btn btn-logout" type="submit">Logout</button>
                    </form>
                </div>
            </div>
        </aside>

        <main class="main-content">
            @yield('content')
        </main>
    </div>
</body>
</html>
