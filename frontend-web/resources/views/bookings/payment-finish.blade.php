@extends('layouts.app')

@section('content')
<section class="hero">
    <p class="eyebrow">Payment</p>
    <h1>Processing Payment...</h1>
</section>

<section class="panel" id="payment-status">
    <p>Verifying your payment, please wait...</p>
</section>

@php($token = csrf_token())
<script>
(function() {
    const hash = window.location.hash.substring(1);
    if (!hash) {
        document.getElementById('payment-status').innerHTML = '<p>No payment data received.</p>' +
            '<p><a class="btn" href="{{ route("bookings.show", $bookingId) }}">Back to Booking</a></p>';
        return;
    }

    const params = new URLSearchParams(hash);
    const orderId = params.get('order_id');
    const transactionStatus = params.get('transaction_status');
    const transactionId = params.get('transaction_id');

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
            document.getElementById('payment-status').innerHTML =
                '<p style="color: #22c55e; font-weight: 700;">Payment Successful!</p>' +
                '<p>Your booking has been confirmed.</p>' +
                '<p><a class="btn" href="{{ route("bookings.show", $bookingId) }}">View My Ticket</a></p>';
        } else {
            document.getElementById('payment-status').innerHTML =
                '<p style="color: #ef4444; font-weight: 700;">Payment ' +
                (transactionStatus ? transactionStatus.charAt(0).toUpperCase() + transactionStatus.slice(1) : 'Failed') +
                '</p>' +
                '<p>Please try again or contact support.</p>' +
                '<p><a class="btn" href="{{ route("bookings.show", $bookingId) }}">Back to Booking</a></p>';
        }
    })
    .catch(function() {
        document.getElementById('payment-status').innerHTML =
            '<p style="color: #ef4444; font-weight: 700;">Something went wrong</p>' +
            '<p><a class="btn" href="{{ route("bookings.show", $bookingId) }}">Back to Booking</a></p>';
    });
})();
</script>
@endsection