<?php

namespace App\Http\Controllers;

use App\Services\EventServiceClient;

class LandingPageController extends Controller
{
    public function __invoke(EventServiceClient $events)
    {
        $data = $events->all([], null);
        $eventsList = $data['data']['events'] ?? [];

        return view('landing', ['events' => $eventsList]);
    }
}
