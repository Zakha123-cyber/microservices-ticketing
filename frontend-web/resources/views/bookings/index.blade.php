@extends('layouts.app')

@section('content')
<section class="hero">
    <p class="eyebrow">Booking History</p>
    <h1>My Tickets</h1>
    <p class="muted">Pantau status booking dan lanjutkan payment jika masih pending.</p>
</section>

@if(session('status'))
    <p>{{ session('status') }}</p>
@endif

@php($items = $bookings['data']['data'] ?? [])

@if(empty($items))
    <p>No bookings yet.</p>
@else
    <table>
        <thead>
            <tr>
                <th>Code</th>
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
