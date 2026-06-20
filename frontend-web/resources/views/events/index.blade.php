@extends('layouts.app')

@section('content')
<h1>Events</h1>
<pre>{{ json_encode($events, JSON_PRETTY_PRINT) }}</pre>
@endsection
