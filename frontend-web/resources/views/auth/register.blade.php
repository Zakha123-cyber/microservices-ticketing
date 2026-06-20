@extends('layouts.guest')

@section('content')
<h1>Register</h1>
@if($errors->any()) <p>{{ $errors->first() }}</p> @endif
<form method="POST" action="{{ route('register.post') }}">
    @csrf
    <input name="name" placeholder="Name" required>
    <input name="email" type="email" placeholder="Email" required>
    <input name="password" type="password" placeholder="Password" required>
    <input name="password_confirmation" type="password" placeholder="Confirm Password" required>
    <button type="submit">Register</button>
</form>
<a href="{{ route('login') }}">Login</a>
@endsection
