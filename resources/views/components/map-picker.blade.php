<div class="filament-map-picker-root">
            <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
            <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder@2.4.0/dist/Control.Geocoder.css" />
            <style>
                .filament-map-picker {
                    position: relative !important;
                }

                .leaflet-control-geocoder {
                    z-index: 9999 !important;
                    position: absolute !important;
                    top: 10px !important;
                    left: 10px !important;
                }

                /* Force geocoder form visible and hide small icon so the input shows by default */
                .leaflet-control-geocoder .leaflet-control-geocoder-form {
                    display: block !important;
                }

                .leaflet-control-geocoder .leaflet-control-geocoder-icon {
                    display: none !important;
                }

                .leaflet-control-geocoder-expanded .leaflet-control-geocoder-form {
                    display: block !important;
                }
            </style>
            <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
            <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder@2.4.0/dist/Control.Geocoder.css" />
            <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
            <script src="https://unpkg.com/leaflet-control-geocoder@2.4.0/dist/Control.Geocoder.js" defer></script>
            <script src="https://unpkg.com/@turf/turf@6.5.0/turf.min.js"></script>

            <style>
                .leaflet-control-container {
                    z-index: 10050 !important;
                    position: absolute !important;
                    top: 0 !important;
                    left: 0 !important;
                    /* don't force full width, let controls size themselves */
                    width: auto !important;
                    pointer-events: auto;
                }

                /* Sudah di atas, hapus duplikat style */
                .leaflet-control-geocoder .leaflet-control-geocoder-icon {
                    filter: none;
                }

                .leaflet-control-geocoder-form {
                    display: flex !important;
                    align-items: center;
                    gap: 6px;
                    background: #fff !important;
                    color: #111 !important;
                    border-radius: 8px !important;
                    padding: 6px 8px !important;
                    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.12) !important;
                    border: 1px solid rgba(0, 0, 0, 0.08) !important;
                    pointer-events: auto !important;
                }

                .leaflet-control-geocoder-form input {
                    background: transparent !important;
                    border: none !important;
                    outline: none !important;
                    color: inherit !important;
                    width: 260px !important;
                    padding: 6px !important;
                }

                /* Ensure input text is visible in dark themes: force light background and dark text */
                .leaflet-control-geocoder-form input,
                .leaflet-search-overlay input[type="search"] {
                    background: #fff !important;
                    color: #111 !important;
                    -webkit-text-fill-color: #111 !important;
                }

                .leaflet-control-geocoder-form input::placeholder,
                .leaflet-search-overlay input[type="search"]::placeholder {
                    color: #6b7280 !important;
                    opacity: 1 !important;
                }

                /* ensure control stays above Filament UI */
                .leaflet-control-geocoder {
                    z-index: 10051 !important;
                    top: 10px !important;
                    /* place to the right of the zoom control so it doesn't overlap */
                    left: 56px !important;
                    right: auto !important;
                }

                /* ensure zoom control remains at left edge */
                .leaflet-control-zoom {
                    z-index: 10052 !important;
                }

                /* simple dropdown for geocoder suggestions */
                .leaflet-geocoder-suggestions {
                    position: absolute;
                    top: 42px;
                    left: 0;
                    min-width: 260px;
                    max-height: 220px;
                    overflow: auto;
                    background: #fff !important;
                    color: #111 !important;
                    border: 1px solid rgba(0, 0, 0, 0.08) !important;
                    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.12);
                    border-radius: 6px;
                    z-index: 10060 !important;
                }

                .leaflet-geocoder-suggestions button {
                    display: block;
                    width: 100%;
                    text-align: left;
                    padding: 8px 10px;
                    background: transparent;
                    border: none;
                    cursor: pointer;
                    color: #111 !important;
                }

                .leaflet-geocoder-suggestions button:hover {
                    background: rgba(0, 0, 0, 0.04);
                }

                .leaflet-geocoder-suggestion {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    gap: 8px;
                }

                .leaflet-geocoder-suggestion .muted {
                    font-size: 12px;
                    color: #666;
                    white-space: nowrap;
                }

                /* make suggestion meta readable in dark mode */
                .leaflet-geocoder-suggestion .muted {
                    color: #4b5563 !important;
                }

                /* persistent overlay search (fallback) */
                .leaflet-search-overlay {
                    display: block;
                    max-width: 640px;
                    margin: 0 0 8px 0;
                    position: relative;
                }

                /* (removed body-attached overlay styles) */
                .leaflet-search-overlay .leaflet-search-box {
                    background: #fff;
                    border-radius: 8px;
                    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.12);
                    border: 1px solid rgba(0, 0, 0, 0.08);
                    padding: 6px 8px;
                    position: relative;
                    display: inline-flex;
                    align-items: center;
                    gap: 6px;
                }

                .leaflet-search-overlay input[type="search"] {
                    border: none;
                    outline: none;
                    width: 260px;
                    /* shorter, ends near the clear (x) */
                    padding: 6px;
                }

                .leaflet-search-btn {
                    background: #1d6eff;
                    color: #fff;
                    border: 1px solid rgba(29, 110, 255, 0.12);
                    padding: 6px 10px;
                    border-radius: 6px;
                    cursor: pointer;
                    font-size: 13px;
                    transition: background-color 150ms ease, transform 100ms ease;
                    box-shadow: 0 2px 6px rgba(29, 110, 255, 0.12);
                }

                .leaflet-search-btn:hover {
                    background: #155ad6;
                }

                .leaflet-spinner {
                    width: 18px;
                    height: 18px;
                    border-radius: 50%;
                    border: 2px solid rgba(0, 0, 0, 0.12);
                    border-top-color: rgba(0, 0, 0, 0.6);
                    display: inline-block;
                    animation: leaflet-spin 800ms linear infinite;
                }

                @keyframes leaflet-spin {
                    from {
                        transform: rotate(0deg);
                    }

                    to {
                        transform: rotate(360deg);
                    }
                }

                .leaflet-search-overlay .leaflet-geocoder-suggestions {
                    position: absolute;
                    top: calc(100% + 6px);
                    left: 0;
                    width: 100%;
                    box-sizing: border-box;
                    z-index: 10060 !important;
                }
            </style>

            @php
                // 🔹 Aman untuk CreatePage & EditPage
                // $record hanya ada di EditPage
                if (isset($record) && $record) {
                    $lat = $record->latitude ?? -8.554125776782495;
                    $lng = $record->longitude ?? 125.57863354845757;
                } else {
                    $lat = -8.554125776782495;
                    $lng = 125.57863354845757;
                }
                // Tambahkan variabel disabled untuk mode view
                $disabled = $disabled ?? false;
            @endphp

            <div>
                <!-- Static search box placed above the map so it is visible on initial load (only in Create/Edit) -->
                @if (!($disabled ?? false))
                    <div class="leaflet-search-overlay" style="display:block">
                        <div class="leaflet-search-box">
                            <input type="search" placeholder="Search location..." aria-label="Search location" />
                            <button type="button" class="leaflet-search-btn" aria-label="Search">Search</button>
                            <span class="leaflet-spinner" aria-hidden="true" style="display:none"></span>
                            <div class="leaflet-geocoder-suggestions" style="display:none"></div>
                        </div>
                    </div>
                @endif
                <div class="filament-map-picker" wire:ignore
                    style="height: 400px; border: 2px solid #ff9800; background: #e0e0e0;"
                    data-lat="{{ $lat }}" data-lng="{{ $lng }}"
                    data-disabled="{{ $disabled ? 'true' : 'false' }}">
                </div>
            </div>

            <script>
                (function() {
                    // legacy id-based initializer removed; use initLeafletContainer for class-based containers
                    function initLeafletContainer(container) {
                        if (!container) return null;
                        console.log('[Leaflet] initLeafletContainer called', container);
                        // ensure a stable id on the container
                        if (!container.id) {
                            container.id = 'map-' + Math.random().toString(36).substr(2, 9);
                        }
                        var mapId = container.id;
                        var lat = parseFloat(container.dataset.lat) || @json($lat);
                        var lng = parseFloat(container.dataset.lng) || @json($lng);
                        // Prefer previously chosen coordinates if available (persisted on the container),
                        // otherwise prefer the current Filament form inputs if they contain values.
                        try {
                            if (container.dataset._leaflet_last_lat && container.dataset._leaflet_last_lng) {
                                var _llat = parseFloat(container.dataset._leaflet_last_lat);
                                var _llng = parseFloat(container.dataset._leaflet_last_lng);
                                if (!isNaN(_llat) && !isNaN(_llng)) {
                                    lat = _llat;
                                    lng = _llng;
                                }
                            } else {
                                var latInputPref = document.querySelector('input[id="form.latitude"]');
                                var lngInputPref = document.querySelector('input[id="form.longitude"]');
                                if (latInputPref && latInputPref.value) {
                                    var p = parseFloat(latInputPref.value);
                                    if (!isNaN(p)) lat = p;
                                }
                                if (lngInputPref && lngInputPref.value) {
                                    var q = parseFloat(lngInputPref.value);
                                    if (!isNaN(q)) lng = q;
                                }
                            }
                        } catch (e) {
                            // ignore
                        }
                        window.leafletMaps = window.leafletMaps || {};
                        // remove and recreate if DOM changed
                        if (window.leafletMaps[mapId]) {
                            try {
                                window.leafletMaps[mapId].remove();
                            } catch (e) {}
                            delete window.leafletMaps[mapId];
                        }
                        console.log('[Leaflet] Creating map with id:', mapId, 'lat:', lat, 'lng:', lng);
                        var map = L.map(mapId).setView([lat, lng], 13);
                        var tileUrl = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
                        L.tileLayer(tileUrl, {
                            attribution: '© OpenStreetMap contributors',
                            maxZoom: 19,
                        }).addTo(map);

                        var marker = L.marker([lat, lng], {
                            draggable: container.dataset.disabled === 'true' ? false : true
                        }).addTo(map);

                        // Helper to format numbers with dot as decimal separator
                        function formatNumberDot(value) {
                            try {
                                var n = Number(value);
                                if (isNaN(n)) return String(value).replace(',', '.');
                                // preserve reasonable precision without introducing trailing zeros
                                return (Math.round(n * 1e6) / 1e6).toString();
                            } catch (e) {
                                return String(value).replace(',', '.');
                            }
                        }

                        // Ensure inputs use dot-formatted values when focused or copied
                        function attachDotCopyBehavior(el) {
                            if (!el || el.dataset._dotBound) return;
                            el.dataset._dotBound = '1';

                            function toDot(v) {
                                try {
                                    return formatNumberDot(v);
                                } catch (e) {
                                    return String(v).replace(',', '.');
                                }
                            }
                            el.addEventListener('focus', function() {
                                try {
                                    el.value = toDot(el.value);
                                } catch (e) {}
                            });
                            el.addEventListener('blur', function() {
                                try {
                                    el.value = toDot(el.value);
                                } catch (e) {}
                            });
                            el.addEventListener('copy', function(ev) {
                                try {
                                    var v = toDot(el.value);
                                    if (ev.clipboardData && ev.clipboardData.setData) {
                                        ev.clipboardData.setData('text/plain', v);
                                        ev.preventDefault();
                                    } else if (window.clipboardData && window.clipboardData.setData) {
                                        window.clipboardData.setData('Text', v);
                                        ev.preventDefault();
                                    }
                                } catch (e) {}
                            });
                        }

                        // Robust prefilling: try multiple selectors and retry a few times until inputs exist.
                        (function() {
                            function setInputValue(el, value) {
                                try {
                                    var v = String(value).replace(',', '.');
                                    // If input is type=number, set valueAsNumber as well to help some browsers
                                    if (el.type === 'number' || el.getAttribute('type') === 'number') {
                                        try {
                                            el.value = v;
                                            el.valueAsNumber = Number(v);
                                        } catch (e) {
                                            el.value = v;
                                        }
                                    } else {
                                        el.value = v;
                                    }
                                    el.setAttribute('value', v);
                                    el.dispatchEvent(new Event('input', {
                                        bubbles: true
                                    }));
                                    el.dispatchEvent(new Event('change', {
                                        bubbles: true
                                    }));
                                    try {
                                        attachDotCopyBehavior(el);
                                    } catch (e) {}
                                } catch (e) {}
                            }

                            function findLatLngInputs(context) {
                                context = context || document;
                                var candidates = [
                                    'input[id="form.latitude"]',
                                    'input[id$="latitude"]',
                                    'input[id*="latitude"]',
                                    'input[name$="[latitude]"]',
                                    'input[name*="latitude"]',
                                    'input[name$=".latitude"]',
                                    'input[type="text"][name*="latitude"]'
                                ];
                                var lat = null,
                                    lng = null;
                                for (var i = 0; i < candidates.length; i++) {
                                    try {
                                        var q = context.querySelector(candidates[i]);
                                        if (q) {
                                            lat = q;
                                            break;
                                        }
                                    } catch (e) {}
                                }
                                var lngCandidates = [
                                    'input[id="form.longitude"]',
                                    'input[id$="longitude"]',
                                    'input[id*="longitude"]',
                                    'input[name$="[longitude]"]',
                                    'input[name*="longitude"]',
                                    'input[name$=".longitude"]',
                                    'input[type="text"][name*="longitude"]'
                                ];
                                for (var j = 0; j < lngCandidates.length; j++) {
                                    try {
                                        var q2 = context.querySelector(lngCandidates[j]);
                                        if (q2) {
                                            lng = q2;
                                            break;
                                        }
                                    } catch (e) {}
                                }
                                return {
                                    latInput: lat,
                                    lngInput: lng
                                };
                            }

                            function tryPrefill(attempt) {
                                attempt = attempt || 1;
                                var found = findLatLngInputs(document);
                                var latInputPref = found.latInput;
                                var lngInputPref = found.lngInput;
                                var fv = formatNumberDot(lat);
                                var fv2 = formatNumberDot(lng);
                                var did = false;
                                if (latInputPref && (!latInputPref.value || latInputPref.value.trim() === '')) {
                                    setInputValue(latInputPref, fv);
                                    did = true;
                                }
                                if (lngInputPref && (!lngInputPref.value || lngInputPref.value.trim() === '')) {
                                    setInputValue(lngInputPref, fv2);
                                    did = true;
                                }

                                // also try within the container/form if not found globally
                                if ((!latInputPref || !lngInputPref) && container) {
                                    var within = findLatLngInputs(container);
                                    if (within.latInput && (!within.latInput.value || within.latInput.value.trim() ===
                                            '')) {
                                        setInputValue(within.latInput, fv);
                                        did = true;
                                    }
                                    if (within.lngInput && (!within.lngInput.value || within.lngInput.value.trim() ===
                                            '')) {
                                        setInputValue(within.lngInput, fv2);
                                        did = true;
                                    }
                                }

                                // Update Livewire state to avoid server-side reformat
                                try {
                                    var mapEl = container;
                                    var compEl = mapEl && mapEl.closest && mapEl.closest('[wire\:id]');
                                    var compId = compEl && compEl.getAttribute && compEl.getAttribute('wire:id');
                                    if (compId && window.Livewire && Livewire.find) {
                                        try {
                                            var live = Livewire.find(compId);
                                            live.set('data.data.0.latitude', fv);
                                            live.set('data.data.0.longitude', fv2);
                                        } catch (e) {
                                            try {
                                                var live2 = Livewire.find(compId);
                                                live2.set('data.latitude', fv);
                                                live2.set('data.longitude', fv2);
                                            } catch (e) {
                                                Livewire.emit('setLocation', {
                                                    lat: fv,
                                                    lng: fv2
                                                });
                                            }
                                        }
                                    } else {
                                        Livewire && Livewire.emit && Livewire.emit('setLocation', {
                                            lat: fv,
                                            lng: fv2
                                        });
                                    }
                                } catch (e) {}

                                if (!did && attempt < 6) {
                                    // retry after a short delay (handles async Livewire rendering)
                                    setTimeout(function() {
                                        tryPrefill(attempt + 1);
                                    }, 140);
                                }
                            }

                            tryPrefill(1);
                            // If the container is hidden initially (Filament tabs), run prefiller again when it becomes visible
                            try {
                                if (window.IntersectionObserver && container) {
                                    try {
                                        var __prefillObserver = new IntersectionObserver(function(entries) {
                                            entries.forEach(function(entry) {
                                                if (entry.isIntersecting) {
                                                    tryPrefill(1);
                                                    try {
                                                        __prefillObserver.disconnect();
                                                    } catch (e) {}
                                                }
                                            });
                                        }, {
                                            threshold: 0.05
                                        });
                                        __prefillObserver.observe(container);
                                    } catch (e) {}
                                }
                            } catch (e) {}
                        })();

                        // Bind the static search input (placed above the map in the Blade markup)
                        try {
                            var staticOverlay = (container.parentElement && container.parentElement.querySelector(
                                '.leaflet-search-overlay')) || container.querySelector('.leaflet-search-overlay');
                            if (staticOverlay && !staticOverlay.dataset._bound) {
                                staticOverlay.dataset._bound = '1';
                                var inputStatic = staticOverlay.querySelector('input[type="search"]');
                                var sugStatic = staticOverlay.querySelector('.leaflet-geocoder-suggestions');
                                var debounceTimeout = null;
                                var activeController = null;
                                var suggestionCache = new Map();
                                var MIN_CHARS = 3;
                                var DEBOUNCE_MS = 600;
                                var isComposing = false;

                                function clearSuggestions() {
                                    if (!sugStatic) return;
                                    sugStatic.style.display = 'none';
                                    sugStatic.innerHTML = '';
                                }

                                function renderSuggestions(list) {
                                    if (!sugStatic) return;
                                    sugStatic.innerHTML = '';
                                    // keep original server order (Nominatim returns relevance-ranked results)
                                    if (!list || !list.length) {
                                        var none = document.createElement('div');
                                        none.style.padding = '8px';
                                        none.style.color = '#666';
                                        none.textContent = 'No results';
                                        sugStatic.appendChild(none);
                                        sugStatic.style.display = 'block';
                                        return;
                                    }
                                    list.forEach(function(item) {
                                        var btn = document.createElement('button');
                                        btn.type = 'button';
                                        btn.className = 'leaflet-geocoder-suggestion';
                                        var label = document.createElement('span');
                                        label.textContent = item.display_name || (item.name || '');
                                        var meta = document.createElement('span');
                                        meta.className = 'muted';
                                        meta.textContent = (item.type || item.class || '');
                                        btn.appendChild(label);
                                        btn.appendChild(meta);
                                        // use pointerdown so selection happens before input blur hides suggestions
                                        btn.addEventListener('pointerdown', function(evt) {
                                            evt.preventDefault();
                                            try {
                                                var lat2 = null,
                                                    lon2 = null;
                                                // Prefer explicit lat/lon when available
                                                if ((item.lat && item.lon) || (item.lat && item.lng)) {
                                                    lat2 = parseFloat(item.lat);
                                                    lon2 = parseFloat(item.lon || item.lng);
                                                    setPosition(lat2, lon2);
                                                } else if (item.osm_type && item.osm_id && (item.type ===
                                                        'administrative' || item.category === 'boundary')) {
                                                    // For administrative boundaries, request polygon and compute interior point
                                                    try {
                                                        var lookupUrl =
                                                            '/geocode/?format=jsonv2&polygon_geojson=1&osm_type=' +
                                                            encodeURIComponent(item.osm_type) + '&osm_id=' +
                                                            encodeURIComponent(item.osm_id);
                                                        var spinner = staticOverlay && staticOverlay
                                                            .querySelector && staticOverlay.querySelector(
                                                                '.leaflet-spinner');
                                                        try {
                                                            if (spinner) spinner.style.display = 'inline-block';
                                                        } catch (e) {}
                                                        fetch(lookupUrl)
                                                            .then(function(r) {
                                                                return r.json();
                                                            })
                                                            .then(function(arr) {
                                                                var first = (arr && arr[0]) ? arr[0] : null;
                                                                if (first && first.geojson) {
                                                                    try {
                                                                        var feat = first.geojson;
                                                                        // turf.pointOnFeature ensures a point inside the polygon
                                                                        var p = null;
                                                                        try {
                                                                            p = turf.pointOnFeature(feat);
                                                                        } catch (e) {
                                                                            p = turf.centroid(feat);
                                                                        }
                                                                        if (p && p.geometry && p.geometry
                                                                            .coordinates) {
                                                                            var lonp = p.geometry
                                                                                .coordinates[0];
                                                                            var latp = p.geometry
                                                                                .coordinates[1];
                                                                            setPosition(latp, lonp, {
                                                                                skipSetView: true
                                                                            });
                                                                        }
                                                                        try {
                                                                            var bounds = L.geoJSON(feat)
                                                                                .getBounds();
                                                                            map.fitBounds(bounds, {
                                                                                maxZoom: 14,
                                                                                padding: [40, 40]
                                                                            });
                                                                        } catch (e) {}
                                                                        inputStatic.value = item
                                                                            .display_name || '';
                                                                    } catch (e) {}
                                                                }
                                                            })
                                                            .catch(function() {})
                                                            .finally(function() {
                                                                try {
                                                                    if (spinner) spinner.style.display =
                                                                        'none';
                                                                } catch (e) {}
                                                            });
                                                    } catch (e) {}
                                                } else if (item.boundingbox && item.boundingbox.length === 4) {
                                                    var bb = item.boundingbox.map(function(v) {
                                                        return parseFloat(v);
                                                    });
                                                    var south = bb[0],
                                                        north = bb[1],
                                                        west = bb[2],
                                                        east = bb[3];
                                                    // center of bbox
                                                    lat2 = (south + north) / 2;
                                                    lon2 = (west + east) / 2;
                                                    try {
                                                        map.fitBounds([
                                                            [south, west],
                                                            [north, east]
                                                        ], {
                                                            maxZoom: 14,
                                                            padding: [40, 40]
                                                        });
                                                    } catch (e) {}
                                                    // set marker without overriding the fitBounds view
                                                    setPosition(lat2, lon2, {
                                                        skipSetView: true
                                                    });
                                                }
                                                if (lat2 !== null && lon2 !== null) {
                                                    inputStatic.value = item.display_name || '';
                                                }
                                            } catch (e) {}
                                            // hide after brief delay
                                            setTimeout(clearSuggestions, 10);
                                        });
                                        sugStatic.appendChild(btn);
                                    });
                                    sugStatic.style.display = 'block';
                                }

                                function performSearch(q) {
                                    if (!q) {
                                        clearSuggestions();
                                        return;
                                    }
                                    if (activeController) {
                                        try {
                                            activeController.abort();
                                        } catch (e) {}
                                    }
                                    activeController = new AbortController();
                                    if (suggestionCache.has(q)) {
                                        renderSuggestions(suggestionCache.get(q));
                                        activeController = null;
                                        return;
                                    }
                                    var url = '/geocode/?format=jsonv2&polygon_geojson=0&q=' + encodeURIComponent(q) +
                                        '&limit=6';
                                    fetch(url, {
                                            signal: activeController.signal
                                        })
                                        .then(function(r) {
                                            console.log('[geocode] response status', r.status, url);
                                            if (r.status === 429) throw {
                                                retryAfter: true,
                                                status: 429
                                            };
                                            if (!r.ok) throw new Error('Network response not ok: ' + r.status);
                                            return r.json().catch(function(e) {
                                                console.warn('[geocode] failed to parse json', e);
                                                return null;
                                            });
                                        })
                                        .then(function(json) {
                                            console.log('[geocode] results for', q, json);
                                            if (!json) {
                                                clearSuggestions();
                                                var note = document.createElement('div');
                                                note.style.padding = '8px';
                                                note.style.color = '#b33';
                                                note.textContent = 'No results or invalid response from geocode proxy.';
                                                sugStatic.appendChild(note);
                                                sugStatic.style.display = 'block';
                                                return;
                                            }
                                            // If the proxy returned an error payload, surface it
                                            if (json && json.error) {
                                                clearSuggestions();
                                                var note = document.createElement('div');
                                                note.style.padding = '8px';
                                                note.style.color = '#b33';
                                                note.textContent = 'Geocode error: ' + (json.error || 'unknown');
                                                sugStatic.appendChild(note);
                                                sugStatic.style.display = 'block';
                                                return;
                                            }
                                            suggestionCache.set(q, json);
                                            renderSuggestions(json);
                                        })
                                        .catch(function(err) {
                                            if (err && err.name === 'AbortError') return; // expected
                                            if (err && err.retryAfter) {
                                                clearSuggestions();
                                                var note = document.createElement('div');
                                                note.style.padding = '8px';
                                                note.style.color = '#b33';
                                                note.textContent = 'Too many requests — please wait a moment.';
                                                sugStatic.appendChild(note);
                                                sugStatic.style.display = 'block';
                                                return;
                                            }
                                            console.error('[geocode] request error', err);
                                            clearSuggestions();
                                            var note = document.createElement('div');
                                            note.style.padding = '8px';
                                            note.style.color = '#b33';
                                            note.textContent = 'Geocode request failed.';
                                            sugStatic.appendChild(note);
                                            sugStatic.style.display = 'block';
                                        })
                                        .finally(function() {
                                            activeController = null;
                                        });
                                }

                                if (inputStatic) {
                                    inputStatic.addEventListener('input', function(evt) {
                                        var q = (evt.target.value || '').trim();
                                        if (isComposing) {
                                            return;
                                        }
                                        if (debounceTimeout) clearTimeout(debounceTimeout);
                                        if (activeController) {
                                            try {
                                                activeController.abort();
                                            } catch (e) {}
                                            activeController = null;
                                        }
                                        if (!q || q.length < MIN_CHARS) {
                                            clearSuggestions();
                                            return;
                                        }
                                        debounceTimeout = setTimeout(function() {
                                            performSearch(q);
                                        }, DEBOUNCE_MS);
                                    });
                                    // Enter key triggers immediate search (useful for short queries)
                                    inputStatic.addEventListener('keydown', function(evt) {
                                        if (evt.key === 'Enter' && !isComposing) {
                                            evt.preventDefault();
                                            var q = (inputStatic.value || '').trim();
                                            if (q) {
                                                if (debounceTimeout) {
                                                    clearTimeout(debounceTimeout);
                                                }
                                                performSearch(q);
                                            }
                                        }
                                    });

                                    // wire up search button
                                    var btnSearch = staticOverlay.querySelector('.leaflet-search-btn');
                                    if (btnSearch) {
                                        btnSearch.addEventListener('click', function() {
                                            var q = (inputStatic.value || '').trim();
                                            if (!q) return;
                                            if (debounceTimeout) {
                                                clearTimeout(debounceTimeout);
                                            }
                                            performSearch(q);
                                        });
                                    }
                                    // Handle IME/composition to avoid sending requests mid-composition
                                    inputStatic.addEventListener('compositionstart', function() {
                                        isComposing = true;
                                        if (debounceTimeout) {
                                            clearTimeout(debounceTimeout);
                                        }
                                    });
                                    inputStatic.addEventListener('compositionend', function(evt) {
                                        isComposing = false;
                                        var q = (evt.target.value || '').trim();
                                        if (q && q.length >= MIN_CHARS) {
                                            // trigger a search immediately after composition ends
                                            inputStatic.dispatchEvent(new Event('input', {
                                                bubbles: true
                                            }));
                                        }
                                    });

                                    // hide on blur after small delay to allow pointerdown handlers to run
                                    inputStatic.addEventListener('blur', function() {
                                        setTimeout(function() {
                                            clearSuggestions();
                                        }, 180);
                                    });
                                    inputStatic.addEventListener('focus', function() {
                                        if (sugStatic && sugStatic.children.length) sugStatic.style.display = 'block';
                                    });
                                }
                            }
                        } catch (e) {}

                        // overlay creation deferred until after geocoder control initialization

                        // Helper to update inputs, container dataset and emit Livewire event
                        function setPosition(latVal, lngVal, options) {
                            if (container.dataset.disabled === 'true') return; // read-only, jangan update apapun
                            options = options || {};
                            try {
                                marker.setLatLng([latVal, lngVal]);
                                if (!options.skipSetView) {
                                    map.setView([latVal, lngVal]);
                                }
                            } catch (e) {}
                            try {
                                var latInput = document.querySelector('input[id="form.latitude"]');
                                var lngInput = document.querySelector('input[id="form.longitude"]');
                                if (latInput) {
                                    var fvLat = formatNumberDot(latVal);
                                    try {
                                        latInput.value = fvLat;
                                        if (latInput.type === 'number' || latInput.getAttribute('type') === 'number') {
                                            try {
                                                latInput.valueAsNumber = Number(fvLat);
                                            } catch (e) {}
                                        }
                                        latInput.setAttribute('value', fvLat);
                                        latInput.dispatchEvent(new Event('input', {
                                            bubbles: true
                                        }));
                                        latInput.dispatchEvent(new Event('change', {
                                            bubbles: true
                                        }));
                                        try {
                                            attachDotCopyBehavior(latInput);
                                        } catch (e) {}
                                    } catch (e) {}
                                }
                                if (lngInput) {
                                    var fvLng = formatNumberDot(lngVal);
                                    try {
                                        lngInput.value = fvLng;
                                        if (lngInput.type === 'number' || lngInput.getAttribute('type') === 'number') {
                                            try {
                                                lngInput.valueAsNumber = Number(fvLng);
                                            } catch (e) {}
                                        }
                                        lngInput.setAttribute('value', fvLng);
                                        lngInput.dispatchEvent(new Event('input', {
                                            bubbles: true
                                        }));
                                        lngInput.dispatchEvent(new Event('change', {
                                            bubbles: true
                                        }));
                                        try {
                                            attachDotCopyBehavior(lngInput);
                                        } catch (e) {}
                                    } catch (e) {}
                                }
                            } catch (e) {}
                            try {
                                container.dataset._leaflet_last_lat = latVal;
                                container.dataset._leaflet_last_lng = lngVal;
                            } catch (e) {}

                            // Update Livewire state with dot-formatted strings to avoid locale formatting
                            try {
                                var fvLat = formatNumberDot(latVal);
                                var fvLng = formatNumberDot(lngVal);
                                // prefer direct Livewire.set if available
                                try {
                                    var mapEl = container;
                                    var compEl = mapEl && mapEl.closest && mapEl.closest('[wire\:id]');
                                    var compId = compEl && compEl.getAttribute && compEl.getAttribute('wire:id');
                                    if (compId && window.Livewire && Livewire.find) {
                                        var live = Livewire.find(compId);
                                        try {
                                            live.set('data.data.0.latitude', fvLat);
                                            live.set('data.data.0.longitude', fvLng);
                                        } catch (e) {
                                            try {
                                                live.set('data.latitude', fvLat);
                                                live.set('data.longitude', fvLng);
                                            } catch (e) {
                                                // fallback to emitting event
                                                Livewire.emit('setLocation', {
                                                    lat: fvLat,
                                                    lng: fvLng
                                                });
                                            }
                                        }
                                    } else {
                                        Livewire.emit('setLocation', {
                                            lat: fvLat,
                                            lng: fvLng
                                        });
                                    }
                                } catch (e) {
                                    try {
                                        Livewire.emit('setLocation', {
                                            lat: formatNumberDot(latVal),
                                            lng: formatNumberDot(lngVal)
                                        });
                                    } catch (e) {}
                                }
                            } catch (e) {}
                        }

                        // Add a search control (only in editable mode: Create / Edit)
                        try {
                            if (typeof L.Control.Geocoder === 'function' && container.dataset.disabled !== 'true') {
                                var hasStaticSearch = !!((container.parentElement && container.parentElement.querySelector(
                                    '.leaflet-search-overlay')) || container.querySelector('.leaflet-search-overlay'));
                                if (hasStaticSearch) {
                                    console.log(
                                        '[Leaflet] Static search overlay present; skipping Leaflet geocoder control to avoid duplicate search UIs'
                                    );
                                } else {
                                    // Use local proxy for Nominatim to avoid CORS and rate-limit issues
                                    var geocoder = L.Control.geocoder({
                                        collapsed: false,
                                        position: 'topleft',
                                        geocoder: L.Control.Geocoder.nominatim({
                                            serviceUrl: '/geocode/',
                                            geocodingQueryParams: {
                                                limit: 6
                                            }
                                        }),
                                        defaultMarkGeocode: false,
                                        placeholder: 'Search location...'
                                    }).addTo(map);

                                    // Ensure the form is focused and visible immediately.
                                    setTimeout(function() {
                                        try {
                                            var gc = geocoder._container || (geocoder.getContainer && geocoder
                                                .getContainer());
                                            if (gc) {
                                                gc.classList.add('leaflet-control-geocoder-expanded');
                                                var form = gc.querySelector('.leaflet-control-geocoder-form');
                                                var icon = gc.querySelector('.leaflet-control-geocoder-icon');
                                                if (form) {
                                                    form.style.display = 'flex';
                                                    var inp = form.querySelector('input');
                                                    if (inp) {
                                                        try {
                                                            inp.focus();
                                                        } catch (e) {}
                                                    }
                                                }
                                                if (icon) icon.style.display = 'none';
                                                try {
                                                    var overlayExisting = (container.querySelector && container
                                                        .querySelector('.leaflet-search-overlay')) || (map
                                                        .getContainer && map.getContainer().querySelector && map
                                                        .getContainer().querySelector('.leaflet-search-overlay'));
                                                    if (overlayExisting && overlayExisting.parentNode) {
                                                        overlayExisting.parentNode.removeChild(overlayExisting);
                                                        container._searchOverlayAdded = false;
                                                    }
                                                } catch (e) {}
                                            }
                                        } catch (e) {}
                                    }, 40);

                                    // If the library's built-in suggestions are not firing, the static
                                    // overlay above the map provides the fallback UX (already bound).
                                    geocoder.on('markgeocode', function(e) {
                                        var c = e.geocode && e.geocode.center;
                                        if (c) {
                                            setPosition(c.lat, c.lng);
                                        }
                                    });
                                    console.log('Geocoder control:', geocoder);
                                }
                            }
                        } catch (e) {}

                        window.leafletMaps[mapId] = map;
                        // keep marker reference on the map instance (avoid global exposure)
                        try {
                            map._leaflet_marker = marker;
                        } catch (e) {}

                        if (container.dataset.disabled !== 'true') {
                            marker.on('dragend', function(e) {
                                var pos = marker.getLatLng();
                                try {
                                    var latInput = document.querySelector('input[id="form.latitude"]');
                                    var lngInput = document.querySelector('input[id="form.longitude"]');
                                    if (latInput) {
                                        try {
                                            var fv = formatNumberDot(pos.lat);
                                            latInput.value = fv;
                                            try {
                                                if (latInput.type === 'number' || latInput.getAttribute('type') ===
                                                    'number') latInput.valueAsNumber = Number(fv);
                                            } catch (e) {}
                                            latInput.setAttribute('value', fv);
                                            latInput.dispatchEvent(new Event('input', {
                                                bubbles: true
                                            }));
                                            latInput.dispatchEvent(new Event('change', {
                                                bubbles: true
                                            }));
                                            attachDotCopyBehavior(latInput);
                                        } catch (e) {}
                                    }
                                    if (lngInput) {
                                        try {
                                            var fv2 = formatNumberDot(pos.lng);
                                            lngInput.value = fv2;
                                            try {
                                                if (lngInput.type === 'number' || lngInput.getAttribute('type') ===
                                                    'number') lngInput.valueAsNumber = Number(fv2);
                                            } catch (e) {}
                                            lngInput.setAttribute('value', fv2);
                                            lngInput.dispatchEvent(new Event('input', {
                                                bubbles: true
                                            }));
                                            lngInput.dispatchEvent(new Event('change', {
                                                bubbles: true
                                            }));
                                            attachDotCopyBehavior(lngInput);
                                        } catch (e) {}
                                    }
                                } catch (e) {}
                                try {
                                    container.dataset._leaflet_last_lat = pos.lat;
                                    container.dataset._leaflet_last_lng = pos.lng;
                                } catch (e) {}
                                if (window.__leaflet_allow_livewire_sync && window.Livewire && Livewire.emit) {
                                    Livewire.emit('setLocation', {
                                        lat: pos.lat,
                                        lng: pos.lng
                                    });
                                }
                            });
                            map.on('click', function(e) {
                                var newLat = e.latlng.lat;
                                var newLng = e.latlng.lng;
                                marker.setLatLng([newLat, newLng]);
                                var latInput = document.querySelector('input[id="form.latitude"]');
                                var lngInput = document.querySelector('input[id="form.longitude"]');
                                if (latInput) {
                                    try {
                                        var fv3 = formatNumberDot(newLat);
                                        latInput.value = fv3;
                                        try {
                                            if (latInput.type === 'number' || latInput.getAttribute('type') ===
                                                'number') latInput.valueAsNumber = Number(fv3);
                                        } catch (e) {}
                                        latInput.setAttribute('value', fv3);
                                        latInput.dispatchEvent(new Event('input', {
                                            bubbles: true
                                        }));
                                        latInput.dispatchEvent(new Event('change', {
                                            bubbles: true
                                        }));
                                        attachDotCopyBehavior(latInput);
                                    } catch (e) {}
                                }
                                if (lngInput) {
                                    try {
                                        var fv4 = formatNumberDot(newLng);
                                        lngInput.value = fv4;
                                        try {
                                            if (lngInput.type === 'number' || lngInput.getAttribute('type') ===
                                                'number') lngInput.valueAsNumber = Number(fv4);
                                        } catch (e) {}
                                        lngInput.setAttribute('value', fv4);
                                        lngInput.dispatchEvent(new Event('input', {
                                            bubbles: true
                                        }));
                                        lngInput.dispatchEvent(new Event('change', {
                                            bubbles: true
                                        }));
                                        attachDotCopyBehavior(lngInput);
                                    } catch (e) {}
                                }
                                try {
                                    container.dataset._leaflet_last_lat = newLat;
                                    container.dataset._leaflet_last_lng = newLng;
                                } catch (e) {}
                                if (window.__leaflet_allow_livewire_sync && window.Livewire && Livewire.emit) {
                                    Livewire.emit('setLocation', {
                                        lat: newLat,
                                        lng: newLng
                                    });
                                }
                            });
                        }

                        // ensure proper rendering when revealed
                        setTimeout(function() {
                            map.invalidateSize();
                        }, 50);
                        setTimeout(function() {
                            map.invalidateSize();
                        }, 250);
                        setTimeout(function() {
                            map.invalidateSize();
                        }, 800);

                        // ResizeObserver to catch container size changes (recommended)
                        if (window.ResizeObserver) {
                            try {
                                var ro = new ResizeObserver(function() {
                                    try {
                                        map.invalidateSize();
                                    } catch (e) {}
                                });
                                ro.observe(container);
                            } catch (e) {
                                // ignore
                            }
                        }

                        // window resize and visibility handlers as fallback
                        window.addEventListener('resize', function() {
                            try {
                                map.invalidateSize();
                            } catch (e) {}
                        });
                        document.addEventListener('visibilitychange', function() {
                            if (document.visibilityState === 'visible') {
                                setTimeout(function() {
                                    try {
                                        map.invalidateSize();
                                    } catch (e) {}
                                }, 120);
                            }
                        });

                        return map;
                    }

                    // Initialize all map containers on DOMContentLoaded
                    document.addEventListener('DOMContentLoaded', function() {
                        document.querySelectorAll('.filament-map-picker').forEach(function(el) {
                            initLeafletContainer(el);
                        });
                    });

                    if (window.Livewire) {
                        window.addEventListener('livewire:load', function() {
                            // give Livewire a tick then init
                            setTimeout(function() {
                                document.querySelectorAll('.filament-map-picker').forEach(function(el) {
                                    initLeafletContainer(el);
                                });
                            }, 50);
                        });

                        if (typeof Livewire.hook === 'function') {
                            // Run init after Livewire processes a message, but delay briefly
                            // so the DOM has time to be morphed and stabilized.
                            Livewire.hook('message.processed', function() {
                                setTimeout(function() {
                                    document.querySelectorAll('.filament-map-picker').forEach(function(el) {
                                        initLeafletContainer(el);
                                    });
                                }, 80);
                            });
                            // After Livewire processes updates, ensure default coords are present
                            Livewire.hook('message.processed', function() {
                                setTimeout(function() {
                                    document.querySelectorAll('.filament-map-picker').forEach(function(el) {
                                        try {
                                            var latInputPref = el.querySelector && el.querySelector(
                                                    'input[id="form.latitude"]') || document
                                                .querySelector('input[id="form.latitude"]');
                                            var lngInputPref = el.querySelector && el.querySelector(
                                                    'input[id="form.longitude"]') || document
                                                .querySelector('input[id="form.longitude"]');
                                            var defaultLat = parseFloat(el.dataset.lat) ||
                                                @json($lat);
                                            var defaultLng = parseFloat(el.dataset.lng) ||
                                                @json($lng);
                                            if (latInputPref && (!latInputPref.value || latInputPref
                                                    .value.trim() === '')) {
                                                var fv = (Math.round(defaultLat * 1e6) / 1e6)
                                                    .toString();
                                                latInputPref.value = fv;
                                                latInputPref.setAttribute('value', fv);
                                                latInputPref.dispatchEvent(new Event('input', {
                                                    bubbles: true
                                                }));
                                            }
                                            if (lngInputPref && (!lngInputPref.value || lngInputPref
                                                    .value.trim() === '')) {
                                                var fv2 = (Math.round(defaultLng * 1e6) / 1e6)
                                                    .toString();
                                                lngInputPref.value = fv2;
                                                lngInputPref.setAttribute('value', fv2);
                                                lngInputPref.dispatchEvent(new Event('input', {
                                                    bubbles: true
                                                }));
                                            }
                                        } catch (e) {}
                                    });
                                }, 120);
                            });
                        }
                    }

                    // MutationObserver to detect elements being (re)added by Livewire/Filament
                    try {
                        var mo = new MutationObserver(function(mutations) {
                            mutations.forEach(function(m) {
                                if (m.addedNodes && m.addedNodes.length) {
                                    m.addedNodes.forEach(function(node) {
                                        if (!node.querySelectorAll) return;
                                        var found = node.querySelectorAll('.filament-map-picker');
                                        if (node.classList && node.classList.contains(
                                                'filament-map-picker')) {
                                            found = Array.prototype.slice.call(found || []);
                                            found.unshift(node);
                                        }
                                        Array.prototype.forEach.call(found || [], function(el) {
                                            // init only if not yet initialized
                                            if (!el.dataset._leaflet_inited) {
                                                initLeafletContainer(el);
                                                el.dataset._leaflet_inited = '1';
                                            }
                                        });
                                    });
                                }
                            });
                        });
                        mo.observe(document.body, {
                            childList: true,
                            subtree: true
                        });
                    } catch (e) {
                        // ignore
                    }

                    // IntersectionObserver to detect when container becomes visible and force invalidateSize
                    if (window.IntersectionObserver) {
                        try {
                            var io = new IntersectionObserver(function(entries) {
                                entries.forEach(function(entry) {
                                    if (entry.isIntersecting) {
                                        var el = entry.target;
                                        var id = el.id;
                                        try {
                                            if (id && window.leafletMaps && window.leafletMaps[id]) {
                                                window.leafletMaps[id].invalidateSize();
                                            }
                                        } catch (e) {}
                                    }
                                });
                            }, {
                                threshold: [0, 0.1, 0.5, 1]
                            });
                            document.querySelectorAll('.filament-map-picker').forEach(function(el) {
                                io.observe(el);
                            });
                        } catch (e) {}
                    }

                    // Sync marker coordinates to Livewire when the Lokasi tab becomes active
                    function syncMarkerToLivewire() {
                        try {
                            var keys = Object.keys(window.leafletMaps || {});
                            if (!keys.length) return;
                            var id = keys[0];
                            var map = window.leafletMaps[id];
                            var marker = map && map._leaflet_marker ? map._leaflet_marker : null;
                            if (!marker || !map) return;
                            var pos = marker.getLatLng();
                            // Try multiple ways to update Livewire state so the payload includes lat/lng
                            try {
                                // prefer direct component set by locating the Livewire component closest to the map container
                                var mapEl = document.getElementById(id);
                                var compEl = mapEl && mapEl.closest && mapEl.closest('[wire\\:id]');
                                var compId = compEl && compEl.getAttribute('wire:id');
                                if (compId && window.Livewire && Livewire.find) {
                                    var live = Livewire.find(compId);
                                    // Filament forms usually store fields under data.data[0]
                                    try {
                                        live.set('data.data.0.latitude', pos.lat);
                                        live.set('data.data.0.longitude', pos.lng);
                                    } catch (e) {
                                        // fallback: try the simpler nested path
                                        try {
                                            live.set('data.latitude', pos.lat);
                                            live.set('data.longitude', pos.lng);
                                        } catch (e) {}
                                    }
                                    // also emit event as backup
                                    try {
                                        Livewire.emit('setLocation', {
                                            lat: pos.lat,
                                            lng: pos.lng
                                        });
                                    } catch (e) {}
                                    return;
                                }
                            } catch (e) {}

                            // fallback: emit event
                            if (window.Livewire && Livewire.emit) {
                                Livewire.emit('setLocation', {
                                    lat: pos.lat,
                                    lng: pos.lng
                                });
                            }
                        } catch (e) {
                            // ignore
                        }
                    }

                    // Attach click handler to tab labeled Lokasi; also observe aria-selected changes
                    try {
                        var tabs = document.querySelectorAll('[role="tab"]');
                        tabs.forEach(function(tab) {
                            if (tab.textContent && tab.textContent.trim().toLowerCase() === 'lokasi') {
                                tab.addEventListener('click', function() {
                                    // allow UI to reveal tab content then sync
                                    setTimeout(function() {
                                        // Re-init map container
                                        document.querySelectorAll('.filament-map-picker').forEach(
                                            function(el) {
                                                if (!el.dataset._leaflet_inited) {
                                                    initLeafletContainer(el);
                                                    el.dataset._leaflet_inited = '1';
                                                } else {
                                                    // invalidate size jika sudah ada
                                                    var id = el.id;
                                                    if (id && window.leafletMaps && window
                                                        .leafletMaps[id]) {
                                                        window.leafletMaps[id].invalidateSize();
                                                    }
                                                }
                                            });
                                        syncMarkerToLivewire();
                                    }, 80);
                                });
                                // observe selection changes
                                var tob = new MutationObserver(function() {
                                    if (tab.getAttribute('aria-selected') === 'true') {
                                        setTimeout(function() {
                                            document.querySelectorAll('.filament-map-picker').forEach(
                                                function(el) {
                                                    if (!el.dataset._leaflet_inited) {
                                                        initLeafletContainer(el);
                                                        el.dataset._leaflet_inited = '1';
                                                    } else {
                                                        var id = el.id;
                                                        if (id && window.leafletMaps && window
                                                            .leafletMaps[id]) {
                                                            window.leafletMaps[id].invalidateSize();
                                                        }
                                                    }
                                                });
                                            syncMarkerToLivewire();
                                        }, 80);
                                    }
                                });
                                tob.observe(tab, {
                                    attributes: true,
                                    attributeFilter: ['aria-selected']
                                });
                            }
                        });
                    } catch (e) {}

                    // Ensure marker coords are synced before form submit: listen for submit-button clicks
                    document.addEventListener('click', function(e) {
                        try {
                            var btn = e.target.closest && e.target.closest('button[type="submit"], button');
                            if (!btn) return;
                            var text = (btn.textContent || '').trim().toLowerCase();
                            // If it's a submit button or labelled Create, sync marker
                            if (btn.matches('button[type="submit"]') || text === 'create' || text.includes('create')) {
                                // If we've already orchestrated a synced submit, let it proceed
                                if (btn.dataset._leaflet_submitting === '1') {
                                    // reset and allow default action
                                    btn.dataset._leaflet_submitting = '0';
                                    return;
                                }

                                // prevent immediate create; sync marker to Livewire first,
                                // then re-trigger the click after Livewire processed the update
                                e.preventDefault();
                                e.stopPropagation();

                                // mark so next click will actually submit
                                btn.dataset._leaflet_submitting = '1';

                                // emit setLocation to server
                                syncMarkerToLivewire();

                                // wait for Livewire to process the update, then trigger submit
                                if (window.Livewire && typeof Livewire.hook === 'function') {
                                    var onceHook = function() {
                                        setTimeout(function() {
                                            try {
                                                btn.click();
                                            } catch (err) {}
                                        }, 30);
                                        // unhook
                                        try {
                                            Livewire.hooks?.removeHook?.('message.processed', onceHook);
                                        } catch (e) {}
                                    };
                                    // Livewire.hook returns nothing; use message.processed
                                    Livewire.hook('message.processed', onceHook);
                                } else {
                                    // fallback: small delay then click
                                    setTimeout(function() {
                                        try {
                                            btn.click();
                                        } catch (err) {}
                                    }, 200);
                                }
                            }
                        } catch (err) {}
                    }, {
                        capture: true
                    });
                })();
            </script>
</div>
