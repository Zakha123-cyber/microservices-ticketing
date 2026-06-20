@extends('layouts.app')

@section('content')
@php($item = $event['data'] ?? null)

<section class="hero">
    <p class="eyebrow">Admin</p>
    <h1>Edit Event</h1>
</section>

@if(!$item)
    <p>Event not found.</p>
@else
    <form method="POST" action="{{ route('events.update', $item['id']) }}" enctype="multipart/form-data">
        @include('events._form', ['item' => $item, 'method' => 'PUT', 'submitLabel' => 'Update Event'])
    </form>
@endif
@endsection
