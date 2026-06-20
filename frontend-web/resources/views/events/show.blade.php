@extends('layouts.app')

@section('content')
<h1>Event Detail</h1>

@php($item = $event['data'] ?? null)

@if(!$item)
    <p>Event not found.</p>
@else
    @if($errors->any()) <p class="alert">{{ $errors->first() }}</p> @endif

    @if(!empty($item['image_url']))
        <img class="event-image" src="{{ $item['image_url'] }}" alt="{{ $item['title'] }}">
    @endif

    <p class="eyebrow">{{ $item['category_name'] ?? 'Uncategorized' }}</p>
    <h2>{{ $item['title'] }}</h2>
    <p>{{ $item['description'] }}</p>
    <section class="panel">
        <p><strong>Date:</strong> {{ \Illuminate\Support\Carbon::parse($item['date'])->format('d M Y H:i') }}</p>
        <p><strong>Location:</strong> {{ $item['location'] }}</p>
        <p class="price">Rp {{ number_format($item['price'], 0, ',', '.') }}</p>
        <p><strong>Available:</strong> {{ $item['available_tickets'] }}</p>
    </section>

    <form class="panel" method="POST" action="{{ route('bookings.store') }}" style="margin-top: 18px;">
        @csrf
        <input type="hidden" name="event_id" value="{{ $item['id'] }}">
        <label for="quantity">Ticket Quantity</label>
        <input id="quantity" name="quantity" type="number" min="1" max="{{ $item['available_tickets'] }}" value="1" required>
        <button type="submit">Book & Pay</button>
    </form>

    @if(data_get(session('user'), 'role') === 'admin')
        <div class="panel" style="margin-top: 18px; display:flex; gap:10px; flex-wrap:wrap;">
            <a class="btn btn-muted" href="{{ route('events.edit', $item['id']) }}">Edit Event</a>
            <form method="POST" action="{{ route('events.destroy', $item['id']) }}" onsubmit="return confirm('Delete this event?')">
                @csrf
                @method('DELETE')
                <button type="submit">Delete Event</button>
            </form>
        </div>
    @endif
@endif
@endsection
