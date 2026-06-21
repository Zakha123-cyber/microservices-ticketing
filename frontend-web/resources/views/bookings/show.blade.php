@extends('layouts.app')

@section('content')
<section class="hero">
    <p class="eyebrow">Ticket Detail</p>
    <h1>My Ticket</h1>
</section>

@php($item = $booking['data'] ?? null)

@if(!$item)
    <p>Booking not found.</p>
@else
    <div style="max-width:480px; margin:0 auto;">
        <div class="ticket" style="background:var(--bg-card); border-radius:12px; overflow:hidden; box-shadow:var(--shadow-heavy);">
            {{-- Ticket Header --}}
            <div style="background:var(--brand); padding:20px 24px; position:relative;">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <span style="font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:2px; color:#000;">Ticket<span style="font-weight:400;">Lab</span></span>
                    <span style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:1px; color:#000; background:rgba(0,0,0,0.15); padding:4px 10px; border-radius:20px;">{{ ucfirst($item['status']) }}</span>
                </div>
            </div>

            <div style="padding:0 24px;">
                <div style="height:1px; background:repeating-linear-gradient(90deg, var(--line) 0, var(--line) 8px, transparent 8px, transparent 16px);"></div>
            </div>

            <div style="padding:24px; text-align:center;">
                <p style="margin:0 0 4px; font-size:11px; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px; font-weight:700;">Event</p>
                <h2 style="margin:0 0 16px; font-size:22px; font-weight:700; color:#fff;">{{ $item['event_title'] }}</h2>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; text-align:left;">
                    <div>
                        <p style="margin:0 0 2px; font-size:10px; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px; font-weight:700;">Quantity</p>
                        <p style="margin:0; font-size:16px; font-weight:700; color:#fff;">{{ $item['quantity'] }} ticket(s)</p>
                    </div>
                    <div>
                        <p style="margin:0 0 2px; font-size:10px; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px; font-weight:700;">Total</p>
                        <p style="margin:0; font-size:16px; font-weight:700; color:var(--brand);">Rp {{ number_format($item['total_price'], 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            <div style="padding:0 24px;">
                <div style="height:1px; background:repeating-linear-gradient(90deg, var(--line) 0, var(--line) 8px, transparent 8px, transparent 16px);"></div>
            </div>

            {{-- QR Code --}}
            <div style="padding:24px; text-align:center;">
                <p style="margin:0 0 12px; font-size:10px; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px; font-weight:700;">QR Code Tiket</p>
                <div id="qrcode" style="display:inline-block; background:#fff; padding:12px; border-radius:8px;"></div>
                <p style="margin:12px 0 0; font-family:monospace; font-size:12px; color:var(--text-muted); letter-spacing:1px;">{{ $item['booking_code'] }}</p>
            </div>

            {{-- Footer --}}
            <div style="padding:16px 24px; border-top:1px solid var(--line); display:flex; justify-content:space-between; align-items:center;">
                <span style="font-size:11px; color:var(--text-muted);">{{ \Illuminate\Support\Carbon::parse($item['created_at'])->format('d M Y H:i') }}</span>
                @if($item['used_at'])
                    <span style="font-size:11px; font-weight:700; color:var(--brand);">Used: {{ \Illuminate\Support\Carbon::parse($item['used_at'])->format('d M H:i') }}</span>
                @elseif($item['status'] === 'paid')
                    <span style="font-size:11px; font-weight:700; color:var(--text-announcement);">Belum digunakan</span>
                @endif
            </div>
        </div>

        @if(!empty($item['payment_url']) && $item['status'] === 'pending')
            <div style="margin-top:24px; text-align:center;">
                <a class="btn" href="{{ $item['payment_url'] }}" style="display:inline-block;">Continue Payment / Simulate Paid</a>
            </div>
        @endif
    </div>

    {{-- Map & Routing --}}
    @php($eventLoc = $event['location'] ?? '')
    @if($eventLoc)
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

        <div class="panel" id="map-panel" style="display:none; margin-top:32px;">
            <h3 style="margin:0 0 12px; font-size:16px; font-weight:700; color:#fff;">Lokasi Event</h3>
            <p style="font-size:13px; color:var(--text-muted); margin:0 0 12px;">{{ $eventLoc }}</p>
            <div id="booking-map" style="height:420px; border-radius:8px; border:1px solid var(--line); z-index:1;"></div>

            <div style="margin-top:12px; display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
                <button type="button" id="btn-get-route" style="border-radius:20px; padding:8px 20px; background:var(--brand); color:#000; border:none; cursor:pointer; font-weight:700; font-size:13px;">Petunjuk Arah dari Lokasi Saya</button>
                <div id="route-info" style="display:none; font-size:13px; color:var(--text-muted);">
                    Jarak: <strong id="route-distance" style="color:#fff;">-</strong> | Estimasi: <strong id="route-duration" style="color:#fff;">-</strong>
                </div>
            </div>
        </div>

        <script>
        document.addEventListener("DOMContentLoaded", function() {
            const address = "{{ $eventLoc }}";
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

                        map = L.map('booking-map').setView(latlng, 15);
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

            const btnGetRoute = document.getElementById('btn-get-route');
            let userMarker, routeLine;

            btnGetRoute.addEventListener('click', function(e) {
                e.preventDefault();
                if (!destLat || !destLon) { alert('Lokasi tujuan belum siap.'); return; }
                if (!navigator.geolocation) { alert('Browser Anda tidak mendukung Geolocation.'); return; }

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

                        userMarker = L.marker([userLat, userLon]).addTo(map).bindPopup("Lokasi Anda").openPopup();

                        const osrmUrl = `https://router.project-osrm.org/route/v1/driving/${userLon},${userLat};${destLon},${destLat}?overview=full&geometries=geojson`;

                        fetch(osrmUrl)
                            .then(res => res.json())
                            .then(routeData => {
                                if (routeData.routes && routeData.routes.length > 0) {
                                    const route = routeData.routes[0];
                                    routeLine = L.geoJSON(route.geometry, { style: { color: '#1ed760', weight: 5, opacity: 0.8 } }).addTo(map);

                                    const bounds = L.latLngBounds([[userLat, userLon], [destLat, destLon]]);
                                    map.fitBounds(bounds, { padding: [50, 50] });

                                    document.getElementById('route-distance').textContent = `${(route.distance / 1000).toFixed(1)} km`;
                                    document.getElementById('route-duration').textContent = `${Math.round(route.duration / 60)} menit`;
                                    document.getElementById('route-info').style.display = 'block';
                                } else {
                                    alert('Tidak dapat menemukan rute jalan ke lokasi.');
                                }
                            })
                            .catch(err => { console.error('Routing error:', err); alert('Gagal mendapatkan rute dari server pemetaan.'); });
                    },
                    function(error) {
                        btnGetRoute.textContent = 'Petunjuk Arah dari Lokasi Saya';
                        btnGetRoute.disabled = false;
                        alert('Gagal mendeteksi lokasi Anda. Harap aktifkan izin lokasi/GPS di browser.');
                    }
                );
            });
        });
        </script>
    @endif

    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <script>
        new QRCode(document.getElementById('qrcode'), {
            text: '{{ $item["booking_code"] }}',
            width: 140,
            height: 140,
            colorDark: '#000000',
            colorLight: '#ffffff',
            correctLevel: QRCode.CorrectLevel.H,
        });
    </script>
@endif
@endsection
