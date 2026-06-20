<?php

namespace App\Http\Controllers;

use App\Helpers\BookingCodeGenerator;
use App\Models\Booking;
use App\Services\EventServiceClient;
use App\Services\MidtransService;
use Illuminate\Http\Request;

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

        $booking->update(['payment_url' => $midtrans->createPayment($booking)]);

        return response()->json(['success' => true, 'message' => 'Booking created successfully', 'data' => $booking], 201);
    }

    public function myBookings(Request $request)
    {
        $bookings = Booking::where('user_id', $request->attributes->get('user_id'))->latest()->paginate((int) $request->query('limit', 10));
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

        return response()->json(['success' => true, 'data' => Booking::latest()->paginate((int) $request->query('limit', 10))]);
    }

    public function midtransNotification(Request $request, EventServiceClient $events)
    {
        $booking = Booking::where('midtrans_order_id', $request->input('order_id'))->firstOrFail();
        $status = in_array($request->input('transaction_status'), ['settlement', 'capture'], true) ? 'paid' : 'failed';

        $booking->update([
            'status' => $status,
            'midtrans_transaction_id' => $request->input('transaction_id'),
            'paid_at' => $status === 'paid' ? now() : null,
        ]);

        if ($status === 'paid') {
            $events->reduceQuota($booking->event_id, $booking->quantity);
        }

        return response()->json(['success' => true, 'message' => 'Notification processed']);
    }
}
