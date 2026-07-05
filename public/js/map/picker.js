// public/js/map/picker.js
// Copy dari resources/js/map/picker.js agar bisa di-load langsung
window.initFilamentMapPicker = function () {
    console.log('[Leaflet] initFilamentMapPicker called');
    var containers = document.querySelectorAll('.filament-map-picker');
    console.log('[Leaflet] Found', containers.length, 'map containers');
    containers.forEach(function (container) {
        if (!container) return;
        if (container.dataset._leaflet_inited === '1') return;
        console.log('[Leaflet] Initializing map container:', container);
        if (!container.id) {
            container.id = 'map-' + Math.random().toString(36).substr(2, 9);
        }
        var mapId = container.id;
        var lat = parseFloat(container.dataset.lat) || -7.95;
        var lng = parseFloat(container.dataset.lng) || 112.61;
        var disabled = container.dataset.disabled === 'true';
        window.leafletMaps = window.leafletMaps || {};
        if (window.leafletMaps[mapId]) {
            try {
                window.leafletMaps[mapId].remove();
            } catch (e) {}
            delete window.leafletMaps[mapId];
        }
        var map = L.map(mapId).setView([lat, lng], 13);
        var tileUrl = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
        L.tileLayer(tileUrl, {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 19,
        }).addTo(map);
        var marker = L.marker([lat, lng], { draggable: !disabled }).addTo(map);

        container.dataset._leaflet_inited = '1';
        window.leafletMaps[mapId] = map;
        map._leaflet_marker = marker;
        if (!disabled) {
            marker.on('dragend', function (e) {
                var pos = marker.getLatLng();
                var latInput = document.querySelector('input[id="form.latitude"]');
                var lngInput = document.querySelector('input[id="form.longitude"]');
                if (latInput) {
                    latInput.value = pos.lat;
                    latInput.setAttribute('value', pos.lat);
                    latInput.dispatchEvent(new Event('input', { bubbles: true }));
                }
                if (lngInput) {
                    lngInput.value = pos.lng;
                    lngInput.setAttribute('value', pos.lng);
                    lngInput.dispatchEvent(new Event('input', { bubbles: true }));
                }
                container.dataset._leaflet_last_lat = pos.lat;
                container.dataset._leaflet_last_lng = pos.lng;
            });
            map.on('click', function (e) {
                var newLat = e.latlng.lat;
                var newLng = e.latlng.lng;
                marker.setLatLng([newLat, newLng]);
                var latInput = document.querySelector('input[id="form.latitude"]');
                var lngInput = document.querySelector('input[id="form.longitude"]');
                if (latInput) {
                    latInput.value = newLat;
                    latInput.setAttribute('value', newLat);
                    latInput.dispatchEvent(new Event('input', { bubbles: true }));
                }
                if (lngInput) {
                    lngInput.value = newLng;
                    lngInput.setAttribute('value', newLng);
                    lngInput.dispatchEvent(new Event('input', { bubbles: true }));
                }
                container.dataset._leaflet_last_lat = newLat;
                container.dataset._leaflet_last_lng = newLng;
            });
        }
        setTimeout(function () {
            map.invalidateSize();
        }, 50);
        setTimeout(function () {
            map.invalidateSize();
        }, 250);
        setTimeout(function () {
            map.invalidateSize();
        }, 800);
        if (window.ResizeObserver) {
            try {
                var ro = new ResizeObserver(function () {
                    try {
                        map.invalidateSize();
                    } catch (e) {}
                });
                ro.observe(container);
            } catch (e) {}
        }
        window.addEventListener('resize', function () {
            try {
                map.invalidateSize();
            } catch (e) {}
        });
        document.addEventListener('visibilitychange', function () {
            if (document.visibilityState === 'visible') {
                setTimeout(function () {
                    try {
                        map.invalidateSize();
                    } catch (e) {}
                }, 120);
            }
        });
    });
};
// Fallback: DOMContentLoaded
window.addEventListener('DOMContentLoaded', function () {
    setTimeout(window.initFilamentMapPicker, 80);
});

// MutationObserver untuk memantau penambahan .filament-map-picker ke DOM
try {
    var mo = new MutationObserver(function (mutations) {
        mutations.forEach(function (m) {
            if (m.addedNodes && m.addedNodes.length) {
                m.addedNodes.forEach(function (node) {
                    if (!node.querySelectorAll) return;
                    var found = node.querySelectorAll('.filament-map-picker');
                    if (node.classList && node.classList.contains('filament-map-picker')) {
                        found = Array.prototype.slice.call(found || []);
                        found.unshift(node);
                    }
                    Array.prototype.forEach.call(found || [], function (el) {
                        if (!el.dataset._leaflet_inited) {
                            window.initFilamentMapPicker();
                        }
                    });
                });
            }
        });
    });
    mo.observe(document.body, { childList: true, subtree: true });
} catch (e) {}
