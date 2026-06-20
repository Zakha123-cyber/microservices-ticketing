<?php

namespace App\Http\Controllers;

use App\Services\BookingServiceClient;
use App\Services\EventServiceClient;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function userDashboard()
    {
        return view('dashboard.user');
    }

    public function adminDashboard(EventServiceClient $events, BookingServiceClient $bookings)
    {
        if (data_get(session('user'), 'role') !== 'admin') {
            abort(403);
        }

        $adminId = (int) data_get(session('user'), 'id', 0);
        $token = session('token');

        // Fetch events created by this admin
        $allEvents = $events->all(['created_by' => $adminId], $token);
        $eventsData = $allEvents['data']['events'] ?? [];
        $adminEventIds = array_column($eventsData, 'id');

        // Fetch bookings for admin's events only
        $bookingsData = [];
        if (!empty($adminEventIds)) {
            $allBookings = $bookings->all($token, ['event_ids' => implode(',', $adminEventIds), 'limit' => 1000]);
            $bookingsData = $allBookings['data']['data'] ?? [];
        }

        // Stats
        $totalEvents = count($eventsData);
        $totalBookings = count($bookingsData);
        $totalRevenue = 0;
        $uniqueUserIds = [];

        foreach ($bookingsData as $b) {
            if (($b['status'] ?? '') === 'paid') {
                $totalRevenue += (int) ($b['total_price'] ?? 0);
            }
            $uid = $b['user_id'] ?? 0;
            if ($uid) {
                $uniqueUserIds[$uid] = true;
            }
        }

        // Recent bookings (last 10)
        $recentBookings = array_slice($bookingsData, 0, 10);

        // Top events by booking count
        $eventBookingCount = [];
        foreach ($bookingsData as $b) {
            $eid = $b['event_id'] ?? 0;
            if ($eid) {
                $eventBookingCount[$eid] = ($eventBookingCount[$eid] ?? 0) + 1;
            }
        }
        arsort($eventBookingCount);
        $topEventIds = array_keys(array_slice($eventBookingCount, 0, 5));
        $topEvents = [];
        foreach ($eventsData as $e) {
            if (in_array($e['id'], $topEventIds)) {
                $e['booking_count'] = $eventBookingCount[$e['id']];
                $topEvents[] = $e;
            }
        }

        // Chart: bookings per day (last 14 days)
        $chartDays = 14;
        $chartDateLabels = [];
        $chartCounts = [];
        $bookingCountByDate = [];

        foreach ($bookingsData as $b) {
            $date = substr($b['created_at'] ?? '', 0, 10);
            if ($date) {
                $bookingCountByDate[$date] = ($bookingCountByDate[$date] ?? 0) + 1;
            }
        }

        for ($i = $chartDays - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $chartDateLabels[] = now()->subDays($i)->format('d M');
            $chartCounts[] = $bookingCountByDate[$date] ?? 0;
        }

        return view('dashboard.admin', [
            'stats' => [
                'total_events' => $totalEvents,
                'total_bookings' => $totalBookings,
                'total_revenue' => $totalRevenue,
                'total_users' => count($uniqueUserIds),
            ],
            'recentBookings' => $recentBookings,
            'topEvents' => $topEvents,
            'chartLabels' => json_encode($chartDateLabels),
            'chartData' => json_encode($chartCounts),
        ]);
    }
}
