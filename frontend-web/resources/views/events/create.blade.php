@extends('layouts.app')

@section('content')
<section class="hero">
    <p class="eyebrow">Admin</p>
    <h1>Create Event</h1>
    <p class="muted">Tambah event baru beserta poster/gambar event.</p>
</section>

<form method="POST" action="{{ route('events.store') }}" enctype="multipart/form-data">
    @include('events._form', ['submitLabel' => 'Create Event'])
</form>
@endsection
