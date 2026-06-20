@csrf
@if(($method ?? 'POST') !== 'POST')
    @method($method)
@endif

@if($errors->any())
    <div class="alert" style="display:grid; gap:6px;">
        <strong>Please fix these fields:</strong>
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="panel" style="display: grid; gap: 14px;">
    <input name="title" value="{{ old('title', $item['title'] ?? '') }}" placeholder="Event title" required>
    <select name="category_id" required>
        @foreach([1 => 'Music', 2 => 'Sport', 3 => 'Seminar', 4 => 'Festival'] as $id => $name)
            <option value="{{ $id }}" @selected((string) old('category_id', $item['category_id'] ?? '') === (string) $id)>{{ $name }}</option>
        @endforeach
    </select>
    <textarea name="description" placeholder="Description" rows="5" style="border:1px solid var(--line); border-radius:18px; padding:14px;">{{ old('description', $item['description'] ?? '') }}</textarea>
    <input name="date" type="datetime-local" value="{{ old('date', isset($item['date']) ? \Illuminate\Support\Carbon::parse($item['date'])->format('Y-m-d\TH:i') : '') }}" required>
    
    <!-- GIS / Map Integration -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    
    <div style="display: grid; gap: 8px;">
        <label style="font-weight: 600; margin-bottom: 2px;">Lokasi Event</label>
        <div style="display: flex; gap: 8px;">
            <input type="text" id="location-search" placeholder="Cari alamat di peta..." style="flex: 1; border: 1px solid var(--line); border-radius: 12px; padding: 12px;">
            <button type="button" id="btn-search-location" style="border-radius: 12px; padding: 0 18px; background: var(--fg); color: #fff; border: none; cursor: pointer; font-weight: 500;">Cari</button>
        </div>
        <div id="map" style="height: 300px; border-radius: 12px; border: 1px solid var(--line); z-index: 1;"></div>
        <p style="font-size: 12px; color: var(--muted); margin: 0;">* Klik pada peta atau drag penanda untuk memindahkan lokasi.</p>
        <input id="location-input" name="location" value="{{ old('location', $item['location'] ?? '') }}" placeholder="Alamat detail lokasi" required style="border: 1px solid var(--line); border-radius: 12px; padding: 12px;">
    </div>

    <input name="price" type="number" min="0" value="{{ old('price', $item['price'] ?? '') }}" placeholder="Price" required>
    <input name="quota" type="number" min="1" value="{{ old('quota', $item['quota'] ?? '') }}" placeholder="Quota" required>
    <label>
        Event image (jpg/png/jpeg, max 5MB)
        <input name="image" type="file" accept="image/jpeg,image/jpg,image/png">
    </label>
    <button type="submit">{{ $submitLabel ?? 'Save Event' }}</button>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const searchInput = document.getElementById('location-search');
    const locationInput = document.getElementById('location-input');
    const btnSearch = document.getElementById('btn-search-location');

    const defaultCenter = [-6.2088, 106.8456]; // Jakarta
    
    // Inisialisasi Map
    const map = L.map('map').setView(defaultCenter, 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    let marker = L.marker(defaultCenter, { draggable: true }).addTo(map);

    // Fungsi geocode (cari alamat -> koordinat & arahkan map)
    function geocode(query, isInit = false) {
        fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=1`)
            .then(res => res.json())
            .then(data => {
                if (data && data.length > 0) {
                    const result = data[0];
                    const latlng = [parseFloat(result.lat), parseFloat(result.lon)];
                    map.setView(latlng, 15);
                    marker.setLatLng(latlng);
                    if (!isInit) {
                        locationInput.value = result.display_name;
                    }
                }
            })
            .catch(err => console.error('Geocoding error:', err));
    }

    // Fungsi reverse geocode (koordinat -> alamat)
    function reverseGeocode(lat, lon) {
        fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}`)
            .then(res => res.json())
            .then(data => {
                if (data && data.display_name) {
                    locationInput.value = data.display_name;
                    searchInput.value = data.display_name;
                }
            })
            .catch(err => console.error('Reverse geocoding error:', err));
    }

    // Jika sudah ada data lokasi (Edit Mode), arahkan peta ke lokasi tersebut
    const existingLocation = locationInput.value;
    if (existingLocation) {
        geocode(existingLocation, true);
    }

    // Klik di Peta
    map.on('click', function(e) {
        const latlng = e.latlng;
        marker.setLatLng(latlng);
        map.panTo(latlng);
        reverseGeocode(latlng.lat, latlng.lng);
    });

    // Drag marker
    marker.on('dragend', function() {
        const latlng = marker.getLatLng();
        map.panTo(latlng);
        reverseGeocode(latlng.lat, latlng.lng);
    });

    // Cari lokasi manual
    btnSearch.addEventListener('click', function(e) {
        e.preventDefault();
        const query = searchInput.value;
        if (query) {
            geocode(query);
        }
    });

    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            btnSearch.click();
        }
    });
});
</script>
