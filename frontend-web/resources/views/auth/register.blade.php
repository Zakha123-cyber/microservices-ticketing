@extends('layouts.guest')

@section('content')
<section class="auth-card">
    <p class="eyebrow">Create Account</p>
    <h1>Register</h1>
    <p class="muted">Buat akun untuk mulai booking event favorit.</p>
    @if($errors->any()) <p class="alert">{{ $errors->first() }}</p> @endif
    <form method="POST" action="{{ route('register.post') }}">
        @csrf
        <input name="name" placeholder="Name" required>
        <input name="email" type="email" placeholder="Email" required>
        <input name="password" type="password" placeholder="Password" required>
        <input name="password_confirmation" type="password" placeholder="Confirm Password" required>
        <button type="submit">Register</button>
    </form>
    <p>Sudah punya akun? <a href="{{ route('login') }}">Login</a></p>
</section>
@endsection
