<?php

namespace App\Http\Controllers;

use App\Services\BookingServiceClient;
use App\Services\EventServiceClient;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function paymentFinish(int $id, Request $request, BookingServiceClient $bookings)
    {
        $booking = $bookings->find($id, session('token'));
        $data = $booking['data'] ?? [];

        // Jika status di DB masih pending, tapi ada query parameters dari Midtrans, update via server-side
        if (($data['status'] ?? 'pending') === 'pending' && $request->has('transaction_status')) {
            $updateResponse = $bookings->updatePayment($id, [
                'transaction_status' => $request->query('transaction_status'),
                'transaction_id' => $request->query('transaction_id'),
            ], session('token'));

            if ($updateResponse['success'] ?? false) {
                // Refresh data booking
                $booking = $bookings->find($id, session('token'));
                $data = $booking['data'] ?? [];
            }
        }

        return view('bookings.payment-finish', [
            'booking' => $data,
            'bookingId' => $id,
        ]);
    }

    public function updatePayment(Request $request, BookingServiceClient $bookings)
    {
        $validated = $request->validate([
            'booking_id' => ['required', 'integer'],
            'transaction_status' => ['required', 'string'],
            'transaction_id' => ['nullable', 'string'],
        ]);

        $response = $bookings->updatePayment(
            $validated['booking_id'],
            $request->only('transaction_status', 'transaction_id'),
            session('token'),
        );

        return response()->json($response);
    }

    public function index(BookingServiceClient $bookings)
    {
        return view('bookings.index', ['bookings' => $bookings->myBookings(session('token'))]);
    }

    public function admin(Request $request, BookingServiceClient $bookings)
    {
        if (data_get(session('user'), 'role') !== 'admin') {
            abort(403);
        }

        return view('bookings.admin', [
            'bookings' => $bookings->all(session('token'), $request->only('status', 'user_id')),
            'filters' => $request->only('status', 'user_id'),
        ]);
    }

    public function show(int $id)
    {
        $booking = app(BookingServiceClient::class)->find($id, session('token'));
        $bookingData = $booking['data'] ?? [];
        $eventId = $bookingData['event_id'] ?? null;

        $event = [];
        if ($eventId) {
            $event = app(EventServiceClient::class)->find($eventId, session('token'));
        }

        return view('bookings.show', [
            'booking' => $booking,
            'event' => $event['data'] ?? [],
        ]);
    }

    public function store(Request $request, BookingServiceClient $bookings)
    {
        $validated = $request->validate([
            'event_id' => ['required', 'integer'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $response = $bookings->create($validated, session('token'));

        if (!($response['success'] ?? false)) {
            return back()->withErrors(['booking' => $response['message'] ?? 'Booking failed']);
        }

        $paymentUrl = $response['data']['payment_url'] ?? null;
        if ($paymentUrl) {
            return redirect()->away($paymentUrl);
        }

        return redirect()->route('bookings.index')->with('status', 'Booking created successfully');
    }
}
