@extends('layouts.app')

@section('content')
<section class="hero">
    <p class="eyebrow">Event Ticketing</p>
    <h1>Discover events worth showing up for.</h1>
    <p class="muted">Cari konser, seminar, festival, dan sport event lalu booking dengan payment sandbox Midtrans.</p>
    @if(data_get(session('user'), 'role') === 'admin')
        <a class="btn" href="{{ route('events.create') }}">Create Event</a>
    @endif
</section>

<form class="filter" method="GET" action="{{ route('events.index') }}">
    <input name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search events">
    <input name="date_from" type="date" value="{{ $filters['date_from'] ?? '' }}">
    <input name="date_to" type="date" value="{{ $filters['date_to'] ?? '' }}">
    <button type="submit">Filter</button>
    <a class="btn btn-muted" href="{{ route('events.index') }}">Reset</a>
</form>

@php($items = $events['data']['events'] ?? [])

@if(empty($items))
    <p>No events available.</p>
@else
    <div class="card-grid">
        @foreach($items as $event)
            <article class="card">
                @if(!empty($event['image_url']))
                    <img src="{{ $event['image_url'] }}" alt="{{ $event['title'] }}">
                @endif
                <div class="card-body">
                    <p class="eyebrow">{{ $event['category_name'] ?? 'Uncategorized' }}</p>
                    <h2>{{ $event['title'] }}</h2>
                    <div class="meta">
                        <span>{{ $event['location'] }}</span>
                        <span>{{ \Illuminate\Support\Carbon::parse($event['date'])->format('d M Y H:i') }}</span>
                        <span>Available: {{ $event['available_tickets'] }}</span>
                    </div>
                    <p class="price">Rp {{ number_format($event['price'], 0, ',', '.') }}</p>
                    <a class="btn" href="{{ route('events.show', $event['id']) }}">View Detail</a>
                    @if(data_get(session('user'), 'role') === 'admin')
                        <a class="btn btn-muted" href="{{ route('events.edit', $event['id']) }}">Edit</a>
                    @endif
                </div>
            </article>
        @endforeach
    </div>
@endif
@endsection
