<?php

namespace App\Http\Controllers;

use App\Services\BookingServiceClient;

class BookingController extends Controller
{
    public function index(BookingServiceClient $bookings)
    {
        return view('bookings.index', ['bookings' => $bookings->myBookings(session('token'))]);
    }

    public function show(int $id)
    {
        return view('bookings.show', ['id' => $id]);
    }
}
