@extends('layouts.app')

@section('content')
<section class="hero">
    <p class="eyebrow">Ticket Detail</p>
    <h1>Booking Detail</h1>
</section>

@php($item = $booking['data'] ?? null)

@if(!$item)
    <p>Booking not found.</p>
@else
    <section class="panel">
        <p><strong>Code:</strong> {{ $item['booking_code'] }}</p>
        <p><strong>Event:</strong> {{ $item['event_title'] }}</p>
        <p><strong>Quantity:</strong> {{ $item['quantity'] }}</p>
        <p class="price" style="color: var(--text-base); font-size: 20px; font-weight: 700; margin: 12px 0;">Rp {{ number_format($item['total_price'], 0, ',', '.') }}</p>
        <p style="display:flex; align-items:center; gap:8px;"><strong>Status:</strong> <span class="status-badge status-{{ $item['status'] }}">{{ $item['status'] }}</span></p>
    </section>
    @if(!empty($item['payment_url']) && $item['status'] === 'pending')
        <p><a class="btn" href="{{ $item['payment_url'] }}">Continue Payment / Simulate Paid</a></p>
    @endif
@endif
@endsection
