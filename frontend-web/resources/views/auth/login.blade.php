@extends('layouts.guest')

@section('content')
<section class="auth-card">
    <p class="eyebrow">Welcome Back</p>
    <h1>Login</h1>
    <p class="muted">Masuk untuk melihat event dan membeli tiket.</p>
    @if($errors->any()) <p class="alert">{{ $errors->first() }}</p> @endif
    <form method="POST" action="{{ route('login.post') }}">
        @csrf
        <input name="email" type="email" placeholder="Email" required>
        <input name="password" type="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>
    <p>Belum punya akun? <a href="{{ route('register') }}">Register</a></p>
</section>
@endsection
