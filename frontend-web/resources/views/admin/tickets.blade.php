@extends('layouts.admin')

@section('content')
<section class="hero">
    <p class="eyebrow">Admin</p>
    <h1 style="margin-bottom:4px;">Manajemen Tiket</h1>
    <p class="muted" style="margin:0;">Total tiket terjual dari setiap event milikmu.</p>
</section>

<!-- Stat Summary -->
<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:16px; margin-bottom:32px;">
    <div class="stat-card" style="background:var(--bg-card); border-radius:8px; padding:20px;">
        <p class="stat-label" style="color:var(--text-muted); font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:1px; margin:0 0 8px;">Total Tiket Terjual</p>
        <p class="stat-value" style="font-size:32px; font-weight:700; color:var(--brand); margin:0;">{{ $totalTicketsSold }}</p>
    </div>
</div>

<!-- Per Event Summary -->
@if(empty($summaryByEvent))
    <div class="panel" style="padding:48px; text-align:center;">
        <p style="color:var(--text-muted);">Belum ada tiket terjual.</p>
    </div>
@else
    <div class="panel" style="padding:0; overflow:hidden;">
        <table>
            <thead>
                <tr>
                    <th>Event</th>
                    <th>Kategori</th>
                    <th>Harga</th>
                    <th>Kuota</th>
                    <th>Tiket Terjual</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($summaryByEvent as $item)
                    @php($e = $item['event'])
                    <tr>
                        <td style="font-weight:700; color:#fff;">{{ $e['title'] ?? 'Unknown' }}</td>
                        <td>{{ $e['category_name'] ?? '-' }}</td>
                        <td>Rp {{ number_format($e['price'] ?? 0, 0, ',', '.') }}</td>
                        <td>{{ $e['available_tickets'] ?? 0 }}/{{ $e['quota'] ?? 0 }}</td>
                        <td><span style="color:var(--brand); font-weight:700;">{{ $item['total_tickets_sold'] }}</span></td>
                        <td><a class="btn btn-muted" href="{{ route('events.show', $e['id']) }}">Detail</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

<!-- Recent Ticket History -->
<h3 style="font-size:18px; font-weight:700; color:#fff; margin:32px 0 16px;">Riwayat Tiket Terjual</h3>

@if(empty($recentBookings))
    <div class="panel" style="padding:24px; text-align:center;">
        <p style="color:var(--text-muted);">Belum ada riwayat.</p>
    </div>
@else
    <div class="panel" style="padding:0; overflow:hidden;">
        <table>
            <thead>
                <tr>
                    <th>Kode Booking</th>
                    <th>Event</th>
                    <th>User ID</th>
                    <th>Qty</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Tanggal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recentBookings as $b)
                    <tr>
                        <td style="font-family:monospace; font-size:12px;">{{ $b['booking_code'] }}</td>
                        <td style="max-width:160px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $b['event_title'] }}</td>
                        <td>#{{ $b['user_id'] }}</td>
                        <td>{{ $b['quantity'] }}</td>
                        <td>Rp {{ number_format($b['total_price'], 0, ',', '.') }}</td>
                        <td><span class="status-badge status-{{ $b['status'] }}">{{ $b['status'] }}</span></td>
                        <td>{{ \Illuminate\Support\Carbon::parse($b['created_at'])->format('d M H:i') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
@endsection
