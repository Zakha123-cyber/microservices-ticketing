@extends('layouts.admin')

@section('content')
<section class="hero">
    <p class="eyebrow">Admin</p>
    <h1>All Bookings</h1>
    <p class="muted">Monitor semua booking dan status payment user.</p>
</section>

<form class="filter" method="GET" action="{{ route('bookings.admin') }}">
    <select name="status">
        <option value="">All status</option>
        @foreach(['pending', 'paid', 'cancelled', 'failed'] as $status)
            <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ ucfirst($status) }}</option>
        @endforeach
    </select>
    <input name="user_id" type="number" min="1" value="{{ $filters['user_id'] ?? '' }}" placeholder="User ID">
    <button type="submit">Filter</button>
    <a class="btn btn-muted" href="{{ route('bookings.admin') }}">Reset</a>
</form>

@php($items = $bookings['data']['data'] ?? [])

@if(empty($items))
    <p>No bookings found.</p>
@else
    <table>
        <thead>
            <tr>
                <th>Code</th>
                <th>User</th>
                <th>Event</th>
                <th>Qty</th>
                <th>Total</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $booking)
                <tr>
                    <td>{{ $booking['booking_code'] }}</td>
                    <td>#{{ $booking['user_id'] }}</td>
                    <td>{{ $booking['event_title'] }}</td>
                    <td>{{ $booking['quantity'] }}</td>
                    <td>Rp {{ number_format($booking['total_price'], 0, ',', '.') }}</td>
                    <td><span class="status-badge status-{{ $booking['status'] }}">{{ $booking['status'] }}</span></td>
                    <td><a class="btn btn-muted" href="{{ route('bookings.show', $booking['id']) }}">Detail</a></td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
@endsection
