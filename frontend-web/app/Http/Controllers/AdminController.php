<?php

namespace App\Http\Controllers;

use App\Services\BookingServiceClient;
use App\Services\EventServiceClient;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    private function getAdminId(): int
    {
        return (int) data_get(session('user'), 'id', 0);
    }

    public function eventManagement(EventServiceClient $events, BookingServiceClient $bookings)
    {
        $adminId = $this->getAdminId();

        $response = $events->all(['created_by' => $adminId, 'limit' => 100], session('token'));
        $eventsData = $response['data']['events'] ?? [];

        $eventIds = array_column($eventsData, 'id');

        $bookingCounts = [];
        $paidCounts = [];
        if (!empty($eventIds)) {
            $bookingResponse = $bookings->all(session('token'), ['event_ids' => implode(',', $eventIds), 'limit' => 10000]);
            $allBookings = $bookingResponse['data']['data'] ?? [];
            foreach ($allBookings as $b) {
                $eid = $b['event_id'] ?? 0;
                if ($eid) {
                    $bookingCounts[$eid] = ($bookingCounts[$eid] ?? 0) + 1;
                    if (($b['status'] ?? '') === 'paid') {
                        $paidCounts[$eid] = ($paidCounts[$eid] ?? 0) + 1;
                    }
                }
            }
        }

        foreach ($eventsData as &$e) {
            $e['total_bookings'] = $bookingCounts[$e['id']] ?? 0;
            $e['paid_bookings'] = $paidCounts[$e['id']] ?? 0;
        }

        return view('admin.events', ['events' => $eventsData]);
    }

    public function ticketManagement(EventServiceClient $events, BookingServiceClient $bookings)
    {
        $adminId = $this->getAdminId();

        $response = $events->all(['created_by' => $adminId, 'limit' => 100], session('token'));
        $eventsData = $response['data']['events'] ?? [];

        $eventIds = array_column($eventsData, 'id');

        $allBookings = [];
        $eventTicketCounts = [];
        $summaryByEvent = [];

        if (!empty($eventIds)) {
            $bookingResponse = $bookings->all(session('token'), ['event_ids' => implode(',', $eventIds), 'limit' => 10000]);
            $allBookings = $bookingResponse['data']['data'] ?? [];

            foreach ($allBookings as $b) {
                $eid = $b['event_id'] ?? 0;
                if ($eid) {
                    $eventTicketCounts[$eid] = ($eventTicketCounts[$eid] ?? 0) + ($b['quantity'] ?? 1);
                }
            }
        }

        $eventMap = [];
        foreach ($eventsData as $e) {
            $eventMap[$e['id']] = $e;
        }

        foreach ($eventTicketCounts as $eid => $count) {
            $summaryByEvent[] = [
                'event' => $eventMap[$eid] ?? null,
                'total_tickets_sold' => $count,
            ];
        }

        usort($summaryByEvent, fn($a, $b) => $b['total_tickets_sold'] - $a['total_tickets_sold']);

        $recentBookings = array_slice($allBookings, 0, 20);

        return view('admin.tickets', [
            'summaryByEvent' => $summaryByEvent,
            'recentBookings' => $recentBookings,
            'totalTicketsSold' => array_sum($eventTicketCounts),
        ]);
    }

    public function transactionManagement(Request $request, EventServiceClient $events, BookingServiceClient $bookings)
    {
        $adminId = $this->getAdminId();

        $eventResponse = $events->all(['created_by' => $adminId, 'limit' => 100], session('token'));
        $eventsData = $eventResponse['data']['events'] ?? [];
        $eventIds = array_column($eventsData, 'id');

        $queryParams = [
            'limit' => 20,
            'page' => (int) $request->query('page', 1),
        ];

        if (!empty($eventIds)) {
            $queryParams['event_ids'] = implode(',', $eventIds);
        }

        if ($request->query('status')) {
            $queryParams['status'] = $request->query('status');
        }
        if ($request->query('event_id')) {
            $queryParams['event_id'] = (int) $request->query('event_id');
        }

        $bookingResponse = $bookings->all(session('token'), $queryParams);
        $pagination = $bookingResponse['data'] ?? [];

        $allBookingsData = $pagination['data'] ?? [];

        // Ambil semua booking untuk aggregasi total
        $allBookingsForAgg = $allBookingsData;
        if (!empty($eventIds)) {
            $aggResponse = $bookings->all(session('token'), ['event_ids' => implode(',', $eventIds), 'limit' => 10000]);
            $allBookingsForAgg = $aggResponse['data']['data'] ?? [];
        }

        $totalTransactions = count($allBookingsForAgg);
        $totalRevenue = 0;
        $perEventTotals = [];

        foreach ($allBookingsForAgg as $b) {
            if (($b['status'] ?? '') === 'paid') {
                $totalRevenue += (int) ($b['total_price'] ?? 0);
            }
            $eid = $b['event_id'] ?? 0;
            if ($eid) {
                $perEventTotals[$eid] = ($perEventTotals[$eid] ?? 0) + 1;
            }
        }

        $perEventLabels = [];
        $eventMap = [];
        foreach ($eventsData as $e) {
            $eventMap[$e['id']] = $e['title'];
        }
        foreach ($perEventTotals as $eid => $count) {
            $perEventLabels[] = [
                'event_title' => $eventMap[$eid] ?? "Event #$eid",
                'count' => $count,
            ];
        }

        return view('admin.transactions', [
            'bookings' => $pagination,
            'filters' => $request->only('status', 'event_id', 'page'),
            'events' => $eventsData,
            'totalTransactions' => $totalTransactions,
            'totalRevenue' => $totalRevenue,
            'perEventLabels' => $perEventLabels,
        ]);
    }
}
