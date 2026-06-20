@extends('layouts.admin')

@section('content')
<section class="hero flex-between">
    <div>
        <p class="eyebrow">Admin</p>
        <h1 style="margin-bottom:4px;">Manajemen Transaksi</h1>
        <p class="muted" style="margin:0;">Riwayat transaksi tiket dari event milikmu.</p>
    </div>
    @php($currentPage = (int) ($bookings['current_page'] ?? 1))
    @php($lastPage = (int) ($bookings['last_page'] ?? 1))
    @php($totalItems = (int) ($bookings['total'] ?? 0))
</section>

<!-- Stats -->
<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:16px; margin-bottom:32px;">
    <div class="stat-card" style="background:var(--bg-card); border-radius:8px; padding:20px;">
        <p class="stat-label" style="color:var(--text-muted); font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:1px; margin:0 0 8px;">Total Transaksi</p>
        <p class="stat-value" style="font-size:32px; font-weight:700; color:#fff; margin:0;">{{ $totalTransactions }}</p>
    </div>
    <div class="stat-card" style="background:var(--bg-card); border-radius:8px; padding:20px;">
        <p class="stat-label" style="color:var(--text-muted); font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:1px; margin:0 0 8px;">Total Revenue</p>
        <p class="stat-value" style="font-size:28px; font-weight:700; color:#539df5; margin:0;">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
    </div>
</div>

<!-- Per Event Transaction Count -->
@if(!empty($perEventLabels))
    <div class="panel" style="margin-bottom:24px;">
        <h3 style="font-size:16px; font-weight:700; color:#fff; margin:0 0 16px;">Transaksi per Event</h3>
        <div style="display:grid; gap:10px;">
            @foreach($perEventLabels as $item)
                <div style="display:flex; align-items:center; gap:12px;">
                    <span style="flex:1; font-size:14px; color:#fff; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $item['event_title'] }}</span>
                    <span style="font-size:14px; font-weight:700; color:var(--brand);">{{ $item['count'] }} transaksi</span>
                </div>
            @endforeach
        </div>
    </div>
@endif

<!-- Filters -->
<form class="filter" method="GET" action="{{ route('admin.transactions') }}" style="margin-bottom:16px;">
    <select name="status">
        <option value="">All status</option>
        @foreach(['pending', 'paid', 'cancelled', 'failed'] as $status)
            <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ ucfirst($status) }}</option>
        @endforeach
    </select>
    <select name="event_id">
        <option value="">All events</option>
        @foreach($events as $event)
            <option value="{{ $event['id'] }}" @selected(($filters['event_id'] ?? '') == $event['id'])>{{ $event['title'] }}</option>
        @endforeach
    </select>
    <button type="submit">Filter</button>
    <a class="btn btn-muted" href="{{ route('admin.transactions') }}">Reset</a>
</form>

<!-- Transaction Table -->
@php($items = $bookings['data'] ?? [])
@if(empty($items))
    <div class="panel" style="padding:48px; text-align:center;">
        <p style="color:var(--text-muted);">Belum ada transaksi.</p>
    </div>
@else
    <div class="panel" style="padding:0; overflow:hidden;">
        <table>
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>User ID</th>
                    <th>Event</th>
                    <th>Qty</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Tanggal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $b)
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
    </div>

    <!-- Pagination -->
    @if($lastPage > 1)
        <div style="display:flex; align-items:center; justify-content:center; gap:8px; margin-top:24px;">
            @if($currentPage > 1)
                <a class="btn btn-muted" href="{{ route('admin.transactions', ['page' => $currentPage - 1, 'status' => $filters['status'] ?? '', 'event_id' => $filters['event_id'] ?? '']) }}">Previous</a>
            @endif

            @php($start = max(1, $currentPage - 2))
            @php($end = min($lastPage, $currentPage + 2))

            @if($start > 1)
                <a class="btn btn-muted" href="{{ route('admin.transactions', ['page' => 1, 'status' => $filters['status'] ?? '', 'event_id' => $filters['event_id'] ?? '']) }}">1</a>
                @if($start > 2)
                    <span style="color:var(--text-muted); padding:0 4px;">...</span>
                @endif
            @endif

            @for($p = $start; $p <= $end; $p++)
                <a class="btn {{ $p === $currentPage ? '' : 'btn-muted' }}" href="{{ route('admin.transactions', ['page' => $p, 'status' => $filters['status'] ?? '', 'event_id' => $filters['event_id'] ?? '']) }}" style="{{ $p === $currentPage ? 'background:var(--brand); color:#000;' : '' }}">{{ $p }}</a>
            @endfor

            @if($end < $lastPage)
                @if($end < $lastPage - 1)
                    <span style="color:var(--text-muted); padding:0 4px;">...</span>
                @endif
                <a class="btn btn-muted" href="{{ route('admin.transactions', ['page' => $lastPage, 'status' => $filters['status'] ?? '', 'event_id' => $filters['event_id'] ?? '']) }}">{{ $lastPage }}</a>
            @endif

            @if($currentPage < $lastPage)
                <a class="btn btn-muted" href="{{ route('admin.transactions', ['page' => $currentPage + 1, 'status' => $filters['status'] ?? '', 'event_id' => $filters['event_id'] ?? '']) }}">Next</a>
            @endif
        </div>

        <p style="text-align:center; color:var(--text-muted); font-size:13px; margin-top:12px;">
            Page {{ $currentPage }} of {{ $lastPage }} ({{ $totalItems }} total transactions)
        </p>
    @endif
@endif
@endsection
