@extends('layouts.app')

@section('content')
<section class="hero">
    <p class="eyebrow">Payment Status</p>
    <h1 id="payment-title">Processing Payment...</h1>
</section>

<section class="panel" id="payment-status">
    <p>Verifying your payment, please wait...</p>
</section>

<!-- Modal Sukses (Server-Side / Client-Side) -->
<div id="success-modal" class="modal-overlay" style="{{ ($booking['status'] ?? 'pending') === 'paid' ? 'display:flex;' : 'display:none;' }}">
    <div class="modal-box">
        <div class="modal-icon">&#10003;</div>
        <h2>Payment Successful!</h2>
        <p>Your booking has been confirmed. You can view your ticket details below.</p>
        <a class="btn" href="{{ route("bookings.show", $bookingId) }}">View My Ticket</a>
    </div>
</div>

<!-- Modal Gagal/Pending (Server-Side / Client-Side) -->
<div id="failed-modal" class="modal-overlay" style="{{ ($booking['status'] ?? 'pending') === 'failed' ? 'display:flex;' : 'display:none;' }}">
    <div class="modal-box">
        <div class="modal-icon fail">&#10007;</div>
        <h2>Payment Failed</h2>
        <p id="failed-msg">Your payment could not be processed. Please try again.</p>
        <a class="btn" href="{{ route("bookings.show", $bookingId) }}">Back to Booking</a>
    </div>
</div>

<!-- Modal Pending / Masih Proses -->
<div id="pending-modal" class="modal-overlay" style="{{ ($booking['status'] ?? 'pending') === 'pending' && !request()->has('transaction_status') ? 'display:flex;' : 'display:none;' }}">
    <div class="modal-box" style="box-shadow: 0 20px 60px rgba(0,0,0,0.15);">
        <div class="modal-icon" style="background: #f59e0b;">?</div>
        <h2>Payment Pending</h2>
        <p>We are waiting for payment confirmation from Midtrans. Please refresh or check again later.</p>
        <div style="display: flex; gap: 10px; justify-content: center;">
            <a class="btn btn-muted" href="{{ route("bookings.show", $bookingId) }}">Go to My Ticket</a>
            <button class="btn" onclick="window.location.reload()">Check Status Again</button>
        </div>
    </div>
</div>

<style>
.modal-overlay {
    position: fixed; inset: 0; z-index: 999;
    background: rgba(0,0,0,0.6);
    display: flex; align-items: center; justify-content: center;
}
.modal-box {
    background: #fff; border-radius: 12px; padding: 40px 32px;
    text-align: center; max-width: 400px; width: 90%;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
}
.modal-icon {
    width: 64px; height: 64px; border-radius: 50%;
    background: #22c55e; color: #fff; font-size: 32px;
    line-height: 64px; margin: 0 auto 16px;
}
.modal-icon.fail { background: #ef4444; }
.modal-box h2 { margin: 0 0 8px; color: #111; }
.modal-box p { color: #555; margin: 0 0 24px; }
</style>

@php($token = csrf_token())
<script>
(function() {
    // Sembunyikan text status jika modal sudah aktif secara server-side
    const currentStatus = "{{ $booking['status'] ?? 'pending' }}";
    const hasQueryStatus = {{ request()->has('transaction_status') ? 'true' : 'false' }};

    if (currentStatus === 'paid' || currentStatus === 'failed' || (currentStatus === 'pending' && !hasQueryStatus)) {
        document.getElementById('payment-status').style.display = 'none';
        document.getElementById('payment-title').textContent = 
            currentStatus === 'paid' ? 'Payment Successful' : 
            (currentStatus === 'failed' ? 'Payment Failed' : 'Payment Pending');
        return;
    }

    // Jika server-side pending tapi ada hash parameters (fallback)
    const hash = window.location.hash.substring(1);
    if (!hash) {
        return;
    }

    const params = new URLSearchParams(hash);
    const transactionStatus = params.get('transaction_status');
    const transactionId = params.get('transaction_id');

    if (!transactionStatus) return;

    // Sembunyikan modal pending bawaan dulu karena kita akan memproses hash param
    document.getElementById('pending-modal').style.display = 'none';
    document.getElementById('payment-status').style.display = 'block';

    const showModal = function(type, msg) {
        document.getElementById('payment-status').style.display = 'none';
        if (type === 'success') {
            document.getElementById('success-modal').style.display = 'flex';
            document.getElementById('payment-title').textContent = 'Payment Successful';
        } else if (type === 'pending') {
            document.getElementById('pending-modal').style.display = 'flex';
            document.getElementById('payment-title').textContent = 'Payment Pending';
        } else {
            if (msg) document.getElementById('failed-msg').textContent = msg;
            document.getElementById('failed-modal').style.display = 'flex';
            document.getElementById('payment-title').textContent = 'Payment Failed';
        }
    };

    fetch('{{ route("bookings.update-payment") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ $token }}',
        },
        body: JSON.stringify({
            booking_id: {{ $bookingId }},
            transaction_status: transactionStatus,
            transaction_id: transactionId,
        }),
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        if (data.success && data.data && data.data.status === 'paid') {
            showModal('success');
        } else if (data.data && data.data.status === 'pending') {
            showModal('pending');
        } else {
            showModal('fail', 'Payment ' + transactionStatus + '. Please try again.');
        }
    })
    .catch(function() {
        showModal('fail', 'Could not verify payment status. Please check your booking detail page.');
    });
})();
</script>
@endsection