<?php

namespace App\Http\Controllers;

use App\Helpers\BookingCodeGenerator;
use App\Models\Booking;
use App\Services\EventServiceClient;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Midtrans\Config;
use Midtrans\Notification;

class BookingController extends Controller
{
    public function store(Request $request, EventServiceClient $events, MidtransService $midtrans)
    {
        $validated = $request->validate([
            'event_id' => ['required', 'integer'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $availability = $events->checkAvailability($validated['event_id'], $validated['quantity']);
        $event = $availability['event'];
        $total = $event['price'] * $validated['quantity'];

        $booking = Booking::create([
            'booking_code' => BookingCodeGenerator::generate(),
            'user_id' => $request->attributes->get('user_id'),
            'event_id' => $event['id'],
            'event_title' => $event['title'],
            'quantity' => $validated['quantity'],
            'total_price' => $total,
            'status' => 'pending',
            'midtrans_order_id' => 'ORDER-' . time() . '-' . random_int(1000, 9999),
        ]);

        try {
            $paymentUrl = $midtrans->createPayment($booking);
        } catch (\Throwable $error) {
            report($error);
            $paymentUrl = url('/api/bookings/' . $booking->id . '/simulate-payment');
        }

        $booking->update(['payment_url' => $paymentUrl]);

        return response()->json(['success' => true, 'message' => 'Booking created successfully', 'data' => $booking], 201);
    }

    public function myBookings(Request $request)
    {
        $query = Booking::where('user_id', $request->attributes->get('user_id'));
        if ($request->query('status')) {
            $query->where('status', $request->query('status'));
        }

        $bookings = $query->latest()->paginate((int) $request->query('limit', 10));
        return response()->json(['success' => true, 'data' => $bookings]);
    }

    public function show(Request $request, Booking $booking)
    {
        $isOwner = $booking->user_id === $request->attributes->get('user_id');
        $isAdmin = $request->attributes->get('user_role') === 'admin';

        if (!$isOwner && !$isAdmin) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        return response()->json(['success' => true, 'data' => $booking]);
    }

    public function cancel(Request $request, Booking $booking)
    {
        $booking->update(['status' => 'cancelled']);
        return response()->json(['success' => true, 'message' => 'Booking cancelled successfully', 'data' => $booking]);
    }

    public function all(Request $request)
    {
        if ($request->attributes->get('user_role') !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Admin role is required'], 403);
        }

        $query = Booking::query();
        if ($request->query('status')) {
            $query->where('status', $request->query('status'));
        }
        if ($request->query('user_id')) {
            $query->where('user_id', $request->query('user_id'));
        }
        if ($request->query('event_ids')) {
            $eventIds = array_map('intval', explode(',', $request->query('event_ids')));
            $query->whereIn('event_id', $eventIds);
        }
        if ($request->query('event_id')) {
            $query->where('event_id', (int) $request->query('event_id'));
        }

        return response()->json(['success' => true, 'data' => $query->latest()->paginate((int) $request->query('limit', 10))]);
    }

    public function simulatePayment(Booking $booking, EventServiceClient $events)
    {
        if ($booking->status !== 'paid') {
            $booking->update([
                'status' => 'paid',
                'midtrans_transaction_id' => 'SIMULATED-' . time(),
                'paid_at' => now(),
            ]);

            $events->reduceQuota($booking->event_id, $booking->quantity);
        }

        return response()->json(['success' => true, 'message' => 'Simulated payment completed', 'data' => $booking->fresh()]);
    }

    public function midtransNotification(EventServiceClient $events)
    {
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = (bool) config('services.midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;

        try {
            // Coba pakai official SDK Notification (verifikasi via API Midtrans)
            $notif = new Notification();
            $transaction = $notif->transaction_status;
            $type = $notif->payment_type;
            $orderId = $notif->order_id;
            $fraud = $notif->fraud_status;
            $transactionId = $notif->transaction_id;
        } catch (\Throwable $e) {
            // Jika gagal (misal transaksi fiktif/mock dari simulator yang tidak ada di Midtrans API)
            // Fallback: Baca request body langsung & verifikasi signature key
            $payload = json_decode(file_get_contents('php://input'), true);

            if (!$payload) {
                return response()->json(['success' => false, 'message' => 'Empty payload'], 400);
            }

            // Verifikasi signature key
            $orderId = $payload['order_id'] ?? '';
            $statusCode = $payload['status_code'] ?? '';
            $grossAmount = $payload['gross_amount'] ?? '';
            $signatureKey = $payload['signature_key'] ?? '';
            $serverKey = Config::$serverKey;

            $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

            if (!hash_equals($expectedSignature, $signatureKey)) {
                return response()->json(['success' => false, 'message' => 'Invalid signature key'], 403);
            }

            $transaction = $payload['transaction_status'] ?? '';
            $type = $payload['payment_type'] ?? '';
            $fraud = $payload['fraud_status'] ?? '';
            $transactionId = $payload['transaction_id'] ?? '';
        }

        $booking = Booking::where('midtrans_order_id', $orderId)->first();

        if (!$booking) {
            return response()->json(['success' => false, 'message' => 'Order not found'], 404);
        }

        if ($booking->status === 'paid') {
            return response()->json(['success' => true, 'message' => 'Already processed']);
        }

        $status = 'pending';

        if ($transaction === 'capture') {
            if ($type === 'credit_card') {
                $status = $fraud === 'accept' ? 'paid' : ($fraud === 'challenge' ? 'pending' : 'failed');
            } else {
                $status = 'paid';
            }
        } elseif ($transaction === 'settlement') {
            $status = 'paid';
        } elseif (in_array($transaction, ['deny', 'cancel', 'expire'], true)) {
            $status = 'failed';
        }

        $booking->update([
            'status' => $status,
            'midtrans_transaction_id' => $transactionId,
            'paid_at' => $status === 'paid' ? now() : null,
        ]);

        if ($status === 'paid') {
            $events->reduceQuota($booking->event_id, $booking->quantity);
        }

        return response()->json(['success' => true, 'message' => 'Notification processed']);
    }

    public function paymentCallback(Request $request, Booking $booking, EventServiceClient $events)
    {
        if ($booking->status === 'paid') {
            return response()->json(['success' => true, 'message' => 'Already paid', 'data' => $booking->fresh()]);
        }

        $transactionStatus = $request->input('transaction_status');
        $fraudStatus = $request->input('fraud_status');
        $status = 'pending';

        if ($transactionStatus === 'capture') {
            $status = $fraudStatus === 'accept' ? 'paid' : ($fraudStatus === 'challenge' ? 'pending' : 'failed');
        } elseif (in_array($transactionStatus, ['settlement', 'success'], true)) {
            $status = 'paid';
        } elseif (in_array($transactionStatus, ['deny', 'cancel', 'expire'], true)) {
            $status = 'failed';
        }

        $booking->update([
            'status' => $status,
            'midtrans_transaction_id' => $request->input('transaction_id'),
            'paid_at' => $status === 'paid' ? now() : null,
        ]);

        if ($status === 'paid') {
            $events->reduceQuota($booking->event_id, $booking->quantity);
        }

        return response()->json(['success' => true, 'message' => 'Payment callback processed', 'data' => $booking->fresh()]);
    }
}
