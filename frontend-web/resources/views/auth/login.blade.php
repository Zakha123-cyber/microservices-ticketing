@extends('layouts.guest')

@section('content')
<h1>Login</h1>
@if($errors->any()) <p>{{ $errors->first() }}</p> @endif
<form method="POST" action="{{ route('login.post') }}">
    @csrf
    <input name="email" type="email" placeholder="Email" required>
    <input name="password" type="password" placeholder="Password" required>
    <button type="submit">Login</button>
</form>
<a href="{{ route('register') }}">Register</a>
@endsection
