<?php

namespace App\Http\Controllers;

use App\Services\EventServiceClient;

class EventController extends Controller
{
    public function index(EventServiceClient $events)
    {
        return view('events.index', ['events' => $events->all([], session('token'))]);
    }

    public function show(int $id, EventServiceClient $events)
    {
        return view('events.show', ['event' => $events->find($id, session('token'))]);
    }

    public function create() { return view('events.create'); }
    public function edit(int $id) { return view('events.edit', ['id' => $id]); }
}
