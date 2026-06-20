@extends('layouts.app')

@section('content')
<h1>Event Detail</h1>
<pre>{{ json_encode($event, JSON_PRETTY_PRINT) }}</pre>
@endsection
