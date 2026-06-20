<?php

namespace App\Http\Controllers;

use App\Services\EventServiceClient;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index(Request $request, EventServiceClient $events)
    {
        return view('events.index', [
            'events' => $events->all($request->only('search', 'category_id', 'date_from', 'date_to', 'page'), session('token')),
            'filters' => $request->only('search', 'category_id', 'date_from', 'date_to'),
        ]);
    }

    public function show(int $id, EventServiceClient $events)
    {
        return view('events.show', ['event' => $events->find($id, session('token'))]);
    }

    public function create() { return view('events.create'); }

    public function store(Request $request, EventServiceClient $events)
    {
        $payload = $this->validatedPayload($request);
        if ($request->hasFile('image')) $payload['image'] = $request->file('image');

        $response = $events->create($payload, session('token'));
        if (!($response['success'] ?? false)) return $this->backWithApiErrors($response, 'Create event failed');

        return redirect()->route('events.index')->with('status', 'Event created successfully');
    }

    public function edit(int $id, EventServiceClient $events)
    {
        return view('events.edit', ['event' => $events->find($id, session('token'))]);
    }

    public function update(Request $request, int $id, EventServiceClient $events)
    {
        $payload = $this->validatedPayload($request);
        if ($request->hasFile('image')) $payload['image'] = $request->file('image');

        $response = $events->update($id, $payload, session('token'));
        if (!($response['success'] ?? false)) return $this->backWithApiErrors($response, 'Update event failed');

        return redirect()->route('events.show', $id)->with('status', 'Event updated successfully');
    }

    public function destroy(int $id, EventServiceClient $events)
    {
        $response = $events->deleteEvent($id, session('token'));
        if (!($response['success'] ?? false)) return back()->withErrors(['event' => $response['message'] ?? 'Delete event failed']);

        return redirect()->route('events.index')->with('status', 'Event deleted successfully');
    }

    private function validatedPayload(Request $request): array
    {
        return $request->validate([
            'category_id' => ['required', 'integer'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'date' => ['required', 'date'],
            'location' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'quota' => ['required', 'integer', 'min:1'],
            'image' => ['nullable', 'image', 'max:5120'],
        ]);
    }

    private function backWithApiErrors(array $response, string $fallback)
    {
        $errors = [];

        foreach (($response['errors'] ?? []) as $field => $messages) {
            foreach ((array) $messages as $message) {
                $errors[$field][] = ucfirst(str_replace('_', ' ', $field)) . ': ' . $message;
            }
        }

        if (empty($errors)) {
            $errors['event'][] = $response['message'] ?? $fallback;
        }

        return back()->withErrors($errors)->withInput();
    }
}
