@extends('layouts.admin')

@section('content')
<section class="hero flex-between">
    <div>
        <p class="eyebrow">Admin Dashboard</p>
        <h1 style="margin-bottom:4px;">Overview</h1>
        <p class="muted" style="margin:0;">Monitor penjualan, transaksi, dan manajemen tiket.</p>
    </div>
    <a class="btn" href="{{ route('events.create') }}">+ Create Event</a>
</section>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Stats Cards -->
<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:16px; margin-bottom:32px;">
    <div class="stat-card" style="background:var(--bg-card); border-radius:8px; padding:20px;">
        <p class="stat-label" style="color:var(--text-muted); font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:1px; margin:0 0 8px;">Total Events</p>
        <p class="stat-value" style="font-size:32px; font-weight:700; color:#fff; margin:0;">{{ $stats['total_events'] }}</p>
    </div>
    <div class="stat-card" style="background:var(--bg-card); border-radius:8px; padding:20px;">
        <p class="stat-label" style="color:var(--text-muted); font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:1px; margin:0 0 8px;">Total Bookings</p>
        <p class="stat-value" style="font-size:32px; font-weight:700; color:#1ed760; margin:0;">{{ $stats['total_bookings'] }}</p>
    </div>
    <div class="stat-card" style="background:var(--bg-card); border-radius:8px; padding:20px;">
        <p class="stat-label" style="color:var(--text-muted); font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:1px; margin:0 0 8px;">Total Revenue</p>
        <p class="stat-value" style="font-size:28px; font-weight:700; color:#539df5; margin:0;">Rp {{ number_format($stats['total_revenue'], 0, ',', '.') }}</p>
    </div>
    <div class="stat-card" style="background:var(--bg-card); border-radius:8px; padding:20px;">
        <p class="stat-label" style="color:var(--text-muted); font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:1px; margin:0 0 8px;">Total Users</p>
        <p class="stat-value" style="font-size:32px; font-weight:700; color:#ffa42b; margin:0;">{{ $stats['total_users'] }}</p>
    </div>
</div>

<!-- Chart Row -->
<div style="display:grid; grid-template-columns:2fr 1fr; gap:24px; margin-bottom:32px;">
    <!-- Bookings Chart -->
    <div class="panel" style="padding:24px;">
        <h3 style="font-size:18px; font-weight:700; color:#fff; margin:0 0 20px;">Bookings per Day (14 Hari Terakhir)</h3>
        <div style="position:relative; height:200px;">
            <canvas id="bookingsChart"></canvas>
        </div>
    </div>

    <!-- Top Events -->
    <div class="panel" style="padding:24px;">
        <h3 style="font-size:18px; font-weight:700; color:#fff; margin:0 0 16px;">Top Events</h3>
        @if(empty($topEvents))
            <p style="color:var(--text-muted); font-size:14px;">No bookings yet.</p>
        @else
            <div style="display:grid; gap:12px;">
                @foreach($topEvents as $e)
                    <div style="display:flex; align-items:center; gap:12px;">
                        <div style="width:40px; height:40px; border-radius:4px; background:#333; overflow:hidden; flex-shrink:0;">
                            @if(!empty($e['image_url']))
                                <img src="{{ $e['image_url'] }}" style="width:100%; height:100%; object-fit:cover;">
                            @endif
                        </div>
                        <div style="flex:1; min-width:0;">
                            <p style="margin:0; font-size:14px; font-weight:700; color:#fff; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $e['title'] }}</p>
                            <p style="margin:0; font-size:12px; color:var(--text-muted);">{{ $e['booking_count'] }} bookings</p>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

<!-- Recent Transactions -->
<div class="panel">
    <h3 style="font-size:18px; font-weight:700; color:#fff; margin:0 0 16px;">Recent Transactions</h3>
    @if(empty($recentBookings))
        <p style="color:var(--text-muted); font-size:14px;">No transactions yet.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>Code</th>
                    <th>User ID</th>
                    <th>Event</th>
                    <th>Qty</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recentBookings as $b)
                    <tr>
                        <td style="font-family:monospace; font-size:12px;">{{ $b['booking_code'] }}</td>
                        <td>#{{ $b['user_id'] }}</td>
                        <td style="max-width:160px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $b['event_title'] }}</td>
                        <td>{{ $b['quantity'] }}</td>
                        <td>Rp {{ number_format($b['total_price'], 0, ',', '.') }}</td>
                        <td><span class="status-badge status-{{ $b['status'] }}">{{ $b['status'] }}</span></td>
                        <td>{{ \Illuminate\Support\Carbon::parse($b['created_at'])->format('d M H:i') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

<script>
(function() {
    var chartInstance = null;
    var canvas = document.getElementById('bookingsChart');

    function initChart() {
        if (!canvas) return;
        if (chartInstance) {
            chartInstance.destroy();
            chartInstance = null;
        }

        var ctx = canvas.getContext('2d');
        chartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: {!! $chartLabels !!},
                datasets: [{
                    label: 'Bookings',
                    data: {!! $chartData !!},
                    backgroundColor: 'rgba(30, 215, 96, 0.7)',
                    borderColor: '#1ed760',
                    borderWidth: 1,
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: {
                        ticks: { color: '#b3b3b3', font: { size: 11 } },
                        grid: { display: false }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: '#b3b3b3',
                            font: { size: 11 },
                            precision: 0,
                        },
                        grid: { color: 'rgba(255,255,255,0.05)' }
                    }
                }
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initChart);
    } else {
        initChart();
    }
})();
</script>
@endsection