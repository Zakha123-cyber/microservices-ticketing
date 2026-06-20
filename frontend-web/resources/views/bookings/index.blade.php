@extends('layouts.app')

@section('content')
<h1>My Tickets</h1>
<pre>{{ json_encode($bookings, JSON_PRETTY_PRINT) }}</pre>
@endsection
