@extends('layouts.app')

@section('content')
@php($item = $event['data'] ?? null)

<section class="hero">
    @if($item)
        <p class="eyebrow">{{ $item['category_name'] ?? 'Uncategorized' }}</p>
        <h1 style="margin-bottom:4px;">{{ $item['title'] }}</h1>
    @endif
</section>

@if(!$item)
    <p>Event not found.</p>
@else
    @if($errors->any())
        <div class="panel" style="background:rgba(243,114,127,0.1); border:1px solid var(--text-negative); padding:12px 16px; border-radius:8px; margin-bottom:16px;">
            <p style="color:var(--text-negative); margin:0; font-size:14px;">{{ $errors->first() }}</p>
        </div>
    @endif

    @if(!empty($item['image_url']))
        <div style="width:100%; height:240px; border-radius:12px; overflow:hidden; margin-bottom:24px;">
            <img src="{{ $item['image_url'] }}" alt="{{ $item['title'] }}" style="width:100%; height:100%; object-fit:cover;">
        </div>
    @endif

    <p style="font-size:15px; line-height:1.7; color:var(--text-muted); margin-bottom:24px;">{{ $item['description'] }}</p>

    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:16px; margin-bottom:24px;">
        <div class="panel" style="padding:16px;">
            <p style="margin:0 0 4px; font-size:10px; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px; font-weight:700;">Date</p>
            <p style="margin:0; font-size:15px; font-weight:700; color:#fff;">{{ \Illuminate\Support\Carbon::parse($item['date'])->format('d M Y H:i') }}</p>
        </div>
        <div class="panel" style="padding:16px;">
            <p style="margin:0 0 4px; font-size:10px; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px; font-weight:700;">Location</p>
            <p style="margin:0; font-size:15px; font-weight:700; color:#fff;">{{ $item['location'] }}</p>
        </div>
        <div class="panel" style="padding:16px;">
            <p style="margin:0 0 4px; font-size:10px; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px; font-weight:700;">Price</p>
            <p style="margin:0; font-size:18px; font-weight:700; color:var(--brand);">Rp {{ number_format($item['price'], 0, ',', '.') }}</p>
        </div>
        <div class="panel" style="padding:16px;">
            <p style="margin:0 0 4px; font-size:10px; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px; font-weight:700;">Available</p>
            <p style="margin:0; font-size:15px; font-weight:700; color:#fff;">{{ $item['available_tickets'] }} / {{ $item['quota'] }}</p>
        </div>
    </div>

    {{-- Map & Routing --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <div class="panel" id="map-panel" style="display:none;">
        <h3 style="margin:0 0 12px; font-size:16px; font-weight:700; color:#fff;">Peta Lokasi</h3>
        <div id="show-map" style="height:420px; border-radius:8px; border:1px solid var(--line); z-index:1;"></div>

        <div style="margin-top:12px; display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
            <button type="button" id="btn-get-route" style="border-radius:20px; padding:8px 20px; background:var(--brand); color:#000; border:none; cursor:pointer; font-weight:700; font-size:13px;">Petunjuk Arah dari Lokasi Saya</button>
            <div id="route-info" style="display:none; font-size:13px; color:var(--text-muted);">
                Jarak: <strong id="route-distance" style="color:#fff;">-</strong> | Estimasi: <strong id="route-duration" style="color:#fff;">-</strong>
            </div>
        </div>
    </div>

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
                        .bindPopup(`<b>${address}</b>`)
                        .openPopup();

                    setTimeout(() => { map.invalidateSize(); }, 200);
                }
            })
            .catch(err => console.error('Map geocoding error:', err));

        // Routing
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

                    if (userMarker) map.removeLayer(userMarker);
                    if (routeLine) map.removeLayer(routeLine);

                    userMarker = L.marker([userLat, userLon]).addTo(map)
                        .bindPopup("Lokasi Anda")
                        .openPopup();

                    const osrmUrl = `https://router.project-osrm.org/route/v1/driving/${userLon},${userLat};${destLon},${destLat}?overview=full&geometries=geojson`;

                    fetch(osrmUrl)
                        .then(res => res.json())
                        .then(routeData => {
                            if (routeData.routes && routeData.routes.length > 0) {
                                const route = routeData.routes[0];

                                routeLine = L.geoJSON(route.geometry, {
                                    style: { color: '#1ed760', weight: 5, opacity: 0.8 }
                                }).addTo(map);

                                const bounds = L.latLngBounds([
                                    [userLat, userLon],
                                    [destLat, destLon]
                                ]);
                                map.fitBounds(bounds, { padding: [50, 50] });

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

    {{-- Booking Form --}}
    <form method="POST" action="{{ route('bookings.store') }}" style="margin-top:24px;">
        @csrf
        <input type="hidden" name="event_id" value="{{ $item['id'] }}">
        <div style="display:flex; gap:12px; align-items:flex-end; flex-wrap:wrap;">
            <div style="flex:1; min-width:120px;">
                <label for="quantity" style="display:block; font-size:12px; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px; margin-bottom:6px;">Ticket Quantity</label>
                <input id="quantity" name="quantity" type="number" min="1" max="{{ $item['available_tickets'] }}" value="1" required>
            </div>
            <button type="submit" class="btn" style="height:40px;">Book & Pay</button>
        </div>
    </form>

    @if(data_get(session('user'), 'role') === 'admin')
        <div style="margin-top:24px; display:flex; gap:10px; flex-wrap:wrap;">
            <a class="btn btn-muted" href="{{ route('events.edit', $item['id']) }}">Edit Event</a>
            <form method="POST" action="{{ route('events.destroy', $item['id']) }}" class="delete-form">
                @csrf
                @method('DELETE')
                <button type="button" class="btn delete-btn" style="background:var(--text-negative); color:#fff;">Delete Event</button>
            </form>
        </div>
    @endif
@endif
@endsection

@push('scripts')
<script>
document.querySelectorAll('.delete-btn').forEach(function(btn) {
    btn.addEventListener('click', function(e) {
        var form = this.closest('.delete-form');
        Swal.fire({
            title: 'Hapus event?',
            text: 'Data tidak bisa dikembalikan setelah dihapus.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e91429',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal',
            background: '#181818',
            color: '#fff'
        }).then(function(result) {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
});
</script>
@endpush
