@extends('layouts.admin')

@section('content')
<section class="hero">
    <p class="eyebrow">Admin</p>
    <h1>Verifikasi Tiket</h1>
    <p class="muted">Scan QR code tiket untuk verifikasi dan ubah status tiket menjadi sudah digunakan.</p>
</section>

<div class="panel" style="padding:48px; text-align:center;">
    <svg viewBox="0 0 24 24" width="64" height="64" fill="var(--text-muted)" style="margin-bottom:16px;">
        <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/>
    </svg>
    <h3 style="font-size:20px; font-weight:700; color:#fff; margin:0 0 8px;">Fitur Verifikasi QR</h3>
    <p style="color:var(--text-muted); font-size:14px; max-width:400px; margin:0 auto 24px;">
        Fitur ini akan datang segera. Nantinya admin dapat memindai QR code tiket untuk memverifikasi
        kehadiran dan mengubah status tiket menjadi "used".
    </p>
    <div style="width:200px; height:200px; background:#242424; border-radius:8px; margin:0 auto; display:flex; align-items:center; justify-content:center;">
        <span style="color:var(--text-muted); font-size:13px;">QR Scanner</span>
    </div>
</div>
@endsection
