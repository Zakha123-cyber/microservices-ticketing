@extends('layouts.app')

@section('content')
<h1>Event Detail</h1>

@php($item = $event['data'] ?? null)

@if(!$item)
    <p>Event not found.</p>
@else
    @if($errors->any()) <p class="alert">{{ $errors->first() }}</p> @endif

    @if(!empty($item['image_url']))
        <img class="event-image" src="{{ $item['image_url'] }}" alt="{{ $item['title'] }}">
    @endif

    <p class="eyebrow">{{ $item['category_name'] ?? 'Uncategorized' }}</p>
    <h2>{{ $item['title'] }}</h2>
    <p>{{ $item['description'] }}</p>
    <section class="panel">
        <p><strong>Date:</strong> {{ \Illuminate\Support\Carbon::parse($item['date'])->format('d M Y H:i') }}</p>
        <p><strong>Location:</strong> {{ $item['location'] }}</p>
        <p class="price">Rp {{ number_format($item['price'], 0, ',', '.') }}</p>
        <p><strong>Available:</strong> {{ $item['available_tickets'] }}</p>
    </section>

    <!-- GIS / Map Integration -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <section class="panel" id="map-panel" style="display:none; margin-top: 18px;">
        <h3 style="margin-top:0; margin-bottom: 12px; font-size: 16px; font-weight: 600;">Peta Lokasi</h3>
        <div id="show-map" style="height: 250px; border-radius: 12px; border: 1px solid var(--line); z-index: 1;"></div>
        
        <!-- Fitur Buat Rute -->
        <div style="margin-top: 12px; display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
            <button type="button" id="btn-get-route" style="border-radius: 12px; padding: 8px 16px; background: var(--fg); color: #fff; border: none; cursor: pointer; font-weight: 500; font-size: 14px;">Petunjuk Arah dari Lokasi Saya</button>
            <div id="route-info" style="display: none; font-size: 14px; color: var(--muted);">
                Jarak: <strong id="route-distance" style="color:var(--fg);">-</strong> | Estimasi: <strong id="route-duration" style="color:var(--fg);">-</strong>
            </div>
        </div>
    </section>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const address = "{{ $item['location'] }}";
        if (!address) return;

        let map, destMarker, destLat, destLon;

        fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}&limit=1`)
            .then(res => res.json())
            .then(data => {
                if (data && data.length > 0) {
                    const result = data[0];
                    destLat = parseFloat(result.lat);
                    destLon = parseFloat(result.lon);
                    const latlng = [destLat, destLon];
                    
                    document.getElementById('map-panel').style.display = 'block';
                    
                    map = L.map('show-map').setView(latlng, 15);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                    }).addTo(map);
                    
                    destMarker = L.marker(latlng).addTo(map)
                        .bindPopup(`<b>{{ $item['title'] }}</b><br>${address}`)
                        .openPopup();
                    
                    setTimeout(() => {
                        map.invalidateSize();
                    }, 200);
                }
            })
            .catch(err => console.error('Map geocoding error:', err));

        // Event handler rute arah dari lokasi user
        const btnGetRoute = document.getElementById('btn-get-route');
        let userMarker, routeLine;

        btnGetRoute.addEventListener('click', function(e) {
            e.preventDefault();

            if (!destLat || !destLon) {
                alert('Lokasi tujuan belum siap.');
                return;
            }

            if (!navigator.geolocation) {
                alert('Browser Anda tidak mendukung Geolocation.');
                return;
            }

            btnGetRoute.textContent = 'Mencari lokasi...';
            btnGetRoute.disabled = true;

            navigator.geolocation.getCurrentPosition(
                function(position) {
                    const userLat = position.coords.latitude;
                    const userLon = position.coords.longitude;

                    btnGetRoute.textContent = 'Petunjuk Arah dari Lokasi Saya';
                    btnGetRoute.disabled = false;

                    // Hapus penanda lama jika ada
                    if (userMarker) map.removeLayer(userMarker);
                    if (routeLine) map.removeLayer(routeLine);

                    // Tambah marker lokasi user
                    userMarker = L.marker([userLat, userLon]).addTo(map)
                        .bindPopup("Lokasi Anda")
                        .openPopup();

                    // Panggil routing API OSRM
                    const osrmUrl = `https://router.project-osrm.org/route/v1/driving/${userLon},${userLat};${destLon},${destLat}?overview=full&geometries=geojson`;

                    fetch(osrmUrl)
                        .then(res => res.json())
                        .then(routeData => {
                            if (routeData.routes && routeData.routes.length > 0) {
                                const route = routeData.routes[0];

                                // Gambar garis rute
                                routeLine = L.geoJSON(route.geometry, {
                                    style: { color: '#3b82f6', weight: 6, opacity: 0.8 }
                                }).addTo(map);

                                // Fit peta ke batas koordinat user & tujuan
                                const bounds = L.latLngBounds([
                                    [userLat, userLon],
                                    [destLat, destLon]
                                ]);
                                map.fitBounds(bounds, { padding: [50, 50] });

                                // Tampilkan data jarak & waktu
                                const distanceKm = (route.distance / 1000).toFixed(1);
                                const durationMin = Math.round(route.duration / 60);

                                document.getElementById('route-distance').textContent = `${distanceKm} km`;
                                document.getElementById('route-duration').textContent = `${durationMin} menit`;
                                document.getElementById('route-info').style.display = 'block';
                            } else {
                                alert('Tidak dapat menemukan rute jalan ke lokasi.');
                            }
                        })
                        .catch(err => {
                            console.error('Routing error:', err);
                            alert('Gagal mendapatkan rute dari server pemetaan.');
                        });
                },
                function(error) {
                    btnGetRoute.textContent = 'Petunjuk Arah dari Lokasi Saya';
                    btnGetRoute.disabled = false;
                    console.error('Geolocation error:', error);
                    alert('Gagal mendeteksi lokasi Anda. Harap aktifkan izin lokasi/GPS di browser.');
                }
            );
        });
    });
    </script>

    <form class="panel" method="POST" action="{{ route('bookings.store') }}" style="margin-top: 18px;">
        @csrf
        <input type="hidden" name="event_id" value="{{ $item['id'] }}">
        <label for="quantity">Ticket Quantity</label>
        <input id="quantity" name="quantity" type="number" min="1" max="{{ $item['available_tickets'] }}" value="1" required>
        <button type="submit">Book & Pay</button>
    </form>

    @if(data_get(session('user'), 'role') === 'admin')
        <div class="panel" style="margin-top: 18px; display:flex; gap:10px; flex-wrap:wrap;">
            <a class="btn btn-muted" href="{{ route('events.edit', $item['id']) }}">Edit Event</a>
            <form method="POST" action="{{ route('events.destroy', $item['id']) }}" onsubmit="return confirm('Delete this event?')">
                @csrf
                @method('DELETE')
                <button type="submit">Delete Event</button>
            </form>
        </div>
    @endif
@endif
@endsection
