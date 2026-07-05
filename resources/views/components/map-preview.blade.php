@php
    // Support two usages:
    // - Filament context: the helper `$getRecord()` is available and will be used.
    // - Plain Blade include: pass `['latitude' => ..., 'longitude' => ...]` when including.
    $lat = $latitude ?? (isset($getRecord) ? $getRecord()->latitude : -7.95);
    $lng = $longitude ?? (isset($getRecord) ? $getRecord()->longitude : 112.61);
@endphp

<div class="filament-map-preview" wire:ignore style="height: 150px; border-radius: 8px; overflow: hidden;"
    data-lat="{{ $lat }}" data-lng="{{ $lng }}"></div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    document.addEventListener('livewire:load', function() {
        const container = document.querySelector('.filament-map-preview');
        if (!container) return;
        const lat = parseFloat(container.dataset.lat);
        const lng = parseFloat(container.dataset.lng);
        const map = L.map(container).setView([lat, lng], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);
        L.marker([lat, lng]).addTo(map);
        setTimeout(() => map.invalidateSize(), 100);
    });
</script>
