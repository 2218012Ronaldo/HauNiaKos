import "./bootstrap";
import "./pages/home";
import "./pages/notif";

window.goBackOrHome = function (homeUrl) {
    if (window.history.length > 1) {
        window.history.back();
    } else {
        window.location.href = homeUrl;
    }
};

// Location modal behavior
function qs(selector, root = document) {
    return root.querySelector(selector);
}

function qsa(selector, root = document) {
    return Array.from(root.querySelectorAll(selector));
}

// Simple transient toast helper
window.showCopyToast = function (message = "Tersalin") {
    try {
        const toast = document.getElementById("copyToast");
        if (!toast) return;
        toast.textContent = message;
        toast.classList.remove("hidden");
        // ensure visible
        toast.style.opacity = "1";
        // auto-hide after 3 seconds
        setTimeout(() => {
            try {
                toast.classList.add("hidden");
            } catch (e) {}
        }, 3000);
    } catch (e) {
        // fallback: no-op
    }
};

// Default center: Pusat Kota Dili, Timor-Leste
const DEFAULT_CITY_LAT = -8.558611; // approximate latitude for Dili city center
const DEFAULT_CITY_LNG = 125.573056; // approximate longitude for Dili city center

window.openLocationModal = function () {
    const modal = qs("#locationModal");
    if (!modal) return;
    modal.classList.remove("hidden");
    modal.classList.add("flex");
    document.body.style.overflow = "hidden";
};

window.closeLocationModal = function () {
    const modal = qs("#locationModal");
    if (!modal) return;
    modal.classList.add("hidden");
    modal.classList.remove("flex");
    document.body.style.overflow = "";
};

window.selectLocation = function (option) {
    const options = qsa(".location-option");
    const previous = (function () {
        try {
            return localStorage.getItem("ngekos_active_location") || "dili";
        } catch (e) {
            return "dili";
        }
    })();

    // If switching from gps -> dili ask for confirmation
    if (option === "dili" && previous === "gps") {
        const ok = confirm(
            'Anda akan mengganti lokasi dari "Lokasi Saya (GPS)" ke Default. Lanjutkan?',
        );
        if (!ok) {
            // revert visual selection to gps
            // ensure we still mark gps visually
            options.forEach((el) => {
                const opt = el.getAttribute("data-option");
                if (opt === "gps") {
                    el.classList.add("border-orange-200", "bg-orange-50/40");
                    const radioOuter = el.querySelector(".radio-outer");
                    const radioInner = radioOuter
                        ? radioOuter.querySelector(".radio-inner")
                        : null;
                    if (radioOuter) {
                        radioOuter.classList.remove("border-slate-300");
                        radioOuter.classList.add("border-orange-500");
                    }
                    if (radioInner) {
                        radioInner.classList.remove("scale-0", "opacity-0");
                        radioInner.classList.add("scale-100", "opacity-100");
                    }
                }
            });
            return;
        }
    }

    // Proceed to set selection
    options.forEach((el) => {
        const opt = el.getAttribute("data-option");
        const radioOuter = el.querySelector(".radio-outer");
        const radioInner = radioOuter
            ? radioOuter.querySelector(".radio-inner")
            : null;
        if (opt === option) {
            el.classList.add("border-orange-200", "bg-orange-50/40");
            el.classList.remove("border-slate-200");
            if (radioOuter) {
                radioOuter.classList.remove("border-slate-300");
                radioOuter.classList.add("border-orange-500");
            }
            if (radioInner) {
                radioInner.classList.remove("scale-0", "opacity-0");
                radioInner.classList.add("scale-100", "opacity-100");
            }
        } else {
            el.classList.remove("border-orange-200", "bg-orange-50/40");
            el.classList.add("border-slate-200", "bg-white");
            if (radioOuter) {
                radioOuter.classList.add("border-slate-300");
                radioOuter.classList.remove("border-orange-500");
            }
            if (radioInner) {
                radioInner.classList.remove("scale-100", "opacity-100");
                radioInner.classList.add("scale-0", "opacity-0");
            }
        }
    });
    // store selection
    try {
        localStorage.setItem("ngekos_active_location", option);
    } catch (e) {}

    // update top label immediately so refresh keeps state
    const label = qs("#activeLocationLabel");
    if (label) {
        if (option === "dili") label.textContent = "Dili (Default)";
        else if (option === "gps") label.textContent = "Lokasi Saya (GPS)";
        else label.textContent = option;
    }

    // stop realtime watch and set distances to Dili center when switching to default
    if (option === "dili") {
        stopWatching();
        // use Dili city center for default distances
        updateDistances(DEFAULT_CITY_LAT, DEFAULT_CITY_LNG);
    }

    // If switching from default -> gps, always request permission now and start realtime
    if (option === "gps" && previous !== "gps") {
        requestAndUpdateLocation(true);
    }
};

window.confirmLocation = function () {
    const active = localStorage.getItem("ngekos_active_location") || "dili";
    const label = qs("#activeLocationLabel");
    if (label) {
        if (active === "dili") label.textContent = "Dili (Default)";
        else if (active === "gps") label.textContent = "Lokasi Saya (GPS)";
        else label.textContent = active;
    }
    closeLocationModal();
};

function initLocationModal() {
    const modal = qs("#locationModal");
    if (!modal) return;
    const overlay = qs("#modalOverlay", modal);
    if (overlay) overlay.addEventListener("click", closeLocationModal);

    // initialize selected option from localStorage and apply label
    const active = (function () {
        try {
            return localStorage.getItem("ngekos_active_location") || "dili";
        } catch (e) {
            return "dili";
        }
    })();
    selectLocation(active);
    // also update top label on page load so refresh shows current mode
    const topLabel = qs("#activeLocationLabel");
    if (topLabel) {
        if (active === "dili") topLabel.textContent = "Dili (Default)";
        else if (active === "gps") topLabel.textContent = "Lokasi Saya (GPS)";
        else topLabel.textContent = active;
    }

    // close on escape
    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape") closeLocationModal();
    });
}

document.addEventListener("DOMContentLoaded", initLocationModal);

// Save original server-provided distances so we can restore them when user switches back to default
function saveOriginalDistances() {
    const els = qsa("[data-lat][data-lng]");
    els.forEach((el) => {
        const distanceEl = el.querySelector(".js-distance");
        if (distanceEl) {
            // store the original text (may be empty)
            try {
                el.dataset.originalDistance = distanceEl.textContent || "";
            } catch (e) {
                // ignore
            }
        }
    });
}

function restoreOriginalDistances() {
    const els = qsa("[data-lat][data-lng]");
    els.forEach((el) => {
        const distanceEl = el.querySelector(".js-distance");
        if (distanceEl) {
            const orig = el.dataset.originalDistance || "";
            distanceEl.textContent = orig;
        }
    });
}

// Real-time watch ID
let watchId = null;

function startWatching() {
    if (!navigator.geolocation) return;
    // avoid multiple watchers
    if (watchId !== null) return;
    watchId = navigator.geolocation.watchPosition(
        (pos) => {
            const lat = pos.coords.latitude;
            const lng = pos.coords.longitude;
            try {
                localStorage.setItem(
                    "ngekos_coords",
                    JSON.stringify({ lat, lng }),
                );
            } catch (e) {}
            updateDistances(lat, lng);
        },
        (err) => {
            console.error("watchPosition error", err);
        },
        { enableHighAccuracy: true, maximumAge: 5000, timeout: 10000 },
    );
}

function stopWatching() {
    if (
        watchId !== null &&
        navigator.geolocation &&
        navigator.geolocation.clearWatch
    ) {
        navigator.geolocation.clearWatch(watchId);
        watchId = null;
    }
}

// --- Distance handling (Haversine) ---
function toRad(deg) {
    return deg * (Math.PI / 180);
}

function haversineDistance(lat1, lon1, lat2, lon2) {
    const R = 6371; // km
    const dLat = toRad(lat2 - lat1);
    const dLon = toRad(lon2 - lon1);
    const a =
        Math.sin(dLat / 2) * Math.sin(dLat / 2) +
        Math.cos(toRad(lat1)) *
            Math.cos(toRad(lat2)) *
            Math.sin(dLon / 2) *
            Math.sin(dLon / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    return R * c;
}

function updateDistances(userLat, userLng) {
    if (!userLat || !userLng) return;
    const els = qsa("[data-lat][data-lng]");
    els.forEach((el) => {
        const lat = parseFloat(el.getAttribute("data-lat"));
        const lng = parseFloat(el.getAttribute("data-lng"));
        const distanceEl = el.querySelector(".js-distance");
        if (!Number.isFinite(lat) || !Number.isFinite(lng)) return;
        const km = haversineDistance(userLat, userLng, lat, lng);
        if (distanceEl) {
            distanceEl.textContent = `· ${km.toFixed(1)} km`;
        }
    });
}

function requestAndUpdateLocation(startWatch = false) {
    if (!navigator.geolocation) {
        alert("Geolocation is not supported by your browser.");
        return;
    }

    const chooseBtn = qs("#chooseLocationBtn");
    if (chooseBtn) chooseBtn.disabled = true;

    const permissionMessageEl = qs("#locationPermissionMessage");
    const permissionRetryBtn = qs("#permissionRetryBtn");
    const permissionHelpBtn = qs("#permissionHelpBtn");

    function showPermissionMessage(text) {
        if (!permissionMessageEl) return;
        permissionMessageEl.classList.remove("hidden");
        permissionMessageEl.querySelector("p").textContent = text;
    }

    function hidePermissionMessage() {
        if (!permissionMessageEl) return;
        permissionMessageEl.classList.add("hidden");
    }

    const proceed = () => {
        hidePermissionMessage();
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                const lat = pos.coords.latitude;
                const lng = pos.coords.longitude;
                try {
                    localStorage.setItem(
                        "ngekos_coords",
                        JSON.stringify({ lat, lng }),
                    );
                } catch (e) {}
                updateDistances(lat, lng);
                if (chooseBtn) chooseBtn.disabled = false;
                if (startWatch) startWatching();
            },
            (err) => {
                console.error("Geolocation error", err);
                if (err && err.code === 1) {
                    showPermissionMessage(
                        "Akses lokasi ditolak. Buka ikon gembok di address bar → Site settings → Location → Allow, lalu klik Retry.",
                    );
                } else if (err && err.code === 2) {
                    showPermissionMessage(
                        "Posisi tidak tersedia. Pastikan Location Service pada perangkat aktif.",
                    );
                } else if (err && err.code === 3) {
                    showPermissionMessage(
                        "Permintaan lokasi memakan waktu terlalu lama. Coba lagi.",
                    );
                } else {
                    showPermissionMessage(
                        "Tidak dapat mendapatkan lokasi Anda. Periksa pengaturan izin browser.",
                    );
                }
                if (chooseBtn) chooseBtn.disabled = false;
            },
            { enableHighAccuracy: true, maximumAge: 300000, timeout: 10000 },
        );
    };

    // Try permissions API first to give a clearer message when denied
    if (permissionRetryBtn) {
        permissionRetryBtn.addEventListener("click", () => {
            // try again
            proceed();
        });
    }

    if (permissionHelpBtn) {
        permissionHelpBtn.addEventListener("click", () => {
            // Provide brief instructions in alert as fallback
            alert(
                "Buka ikon gembok di address bar → Site settings → Location → pilih Allow. Jika tidak muncul, periksa chrome://settings/content/location.",
            );
        });
    }

    if (navigator.permissions && navigator.permissions.query) {
        navigator.permissions
            .query({ name: "geolocation" })
            .then((perm) => {
                if (perm.state === "denied") {
                    if (chooseBtn) chooseBtn.disabled = false;
                    showPermissionMessage(
                        "Izin lokasi untuk situs ini diblokir. Buka ikon gembok di address bar → Site settings → Location → Allow, lalu klik Retry.",
                    );
                    return;
                }
                proceed();
            })
            .catch(() => {
                proceed();
            });
    } else {
        proceed();
    }
}

// When user confirms location selection, if GPS mode selected, request geolocation and update distances
const originalConfirm = window.confirmLocation;
window.confirmLocation = function () {
    originalConfirm();
    const active = localStorage.getItem("ngekos_active_location") || "dili";
    if (active === "gps") {
        // if coords already stored, use them first
        try {
            const stored = JSON.parse(localStorage.getItem("ngekos_coords"));
            if (stored && stored.lat && stored.lng) {
                updateDistances(stored.lat, stored.lng);
                return;
            }
        } catch (e) {}
        requestAndUpdateLocation();
    }
};

// On load, if previously selected gps and coords exist, update distances
document.addEventListener("DOMContentLoaded", () => {
    // preserve original server distances on load
    saveOriginalDistances();

    const active = localStorage.getItem("ngekos_active_location") || "dili";
    if (active === "gps") {
        try {
            const stored = JSON.parse(localStorage.getItem("ngekos_coords"));
            if (stored && stored.lat && stored.lng) {
                updateDistances(stored.lat, stored.lng);
                // start realtime updates
                startWatching();
            } else {
                // try to request fresh coords if user previously chose gps and start realtime
                requestAndUpdateLocation(true);
            }
        } catch (e) {
            requestAndUpdateLocation(true);
        }
    } else {
        // ensure default distances are visible using Dili center
        updateDistances(DEFAULT_CITY_LAT, DEFAULT_CITY_LNG);
    }
});

/*
 * openDirections(lat, lng)
 * - Opens Google Maps directions in a new tab/window.
 * - Tries to obtain user's current location (via Geolocation API) and use it as origin.
 * - If permission is denied or unavailable, falls back to opening Directions with only destination.
 * - For Android devices a google.navigation intent is also attempted as a fast path (will open Maps app if available).
 * - This function is attached to `window` so it can be called from Blade templates inline.
 */
window.openDirections = function (lat, lng, travelmode = "driving") {
    try {
        if (!lat || !lng) {
            alert("Koordinat tujuan tidak tersedia.");
            return;
        }

        const destination = `${lat},${lng}`;

        // Helper to open a web directions URL (Google Maps web)
        function openWeb(origin) {
            const originPart = origin ? `&origin=${origin}` : "";
            const url = `https://www.google.com/maps/dir/?api=1${originPart}&destination=${destination}&travelmode=${encodeURIComponent(
                travelmode,
            )}`;
            window.open(url, "_blank", "noopener");
        }

        // Try to open Google Maps / Apple Maps app on mobile devices using app schemes
        function tryOpenInApp(origin) {
            const isAndroid = /Android/i.test(navigator.userAgent);
            const isIOS = /iPhone|iPad|iPod/i.test(navigator.userAgent);

            // If origin is provided we try to include it where supported
            const originParam = origin ? `&origin=${origin}` : "";

            if (isIOS) {
                // Prefer Google Maps app on iOS if installed
                try {
                    const gmUrl = `comgooglemaps://?daddr=${destination}&directionsmode=${encodeURIComponent(
                        travelmode,
                    )}${origin ? `&saddr=${origin}` : ""}`;
                    // Short timeout fallback to Apple Maps or web
                    window.location.href = gmUrl;
                    setTimeout(() => {
                        // If Google Maps app didn't open, try Apple Maps
                        const appleUrl = `maps://?daddr=${destination}${origin ? `&saddr=${origin}` : ""}`;
                        window.location.href = appleUrl;
                        setTimeout(() => {
                            // Final fallback to web
                            openWeb(origin);
                        }, 700);
                    }, 700);
                    return;
                } catch (e) {
                    // fallthrough to web
                }
            }

            if (isAndroid) {
                try {
                    // Use an intent URI which will open Google Maps app when installed
                    const intentUrl = `intent://maps.google.com/maps?daddr=${destination}${origin ? `&saddr=${origin}` : ""}&travelmode=${encodeURIComponent(
                        travelmode,
                    )}#Intent;package=com.google.android.apps.maps;scheme=https;end`;
                    window.location.href = intentUrl;
                    // fallback to web if app isn't installed
                    setTimeout(() => {
                        openWeb(origin);
                    }, 800);
                    return;
                } catch (e) {
                    // fallthrough to web
                }
            }

            // Desktop or unknown: open web
            openWeb(origin);
        }

        // If user allows geolocation, include origin param
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    const origin = `${pos.coords.latitude},${pos.coords.longitude}`;
                    tryOpenInApp(origin);
                },
                (err) => {
                    // permission denied or error — try app without explicit origin, else web
                    tryOpenInApp(null);
                },
                { timeout: 10000 },
            );
        } else {
            // no geolocation support
            tryOpenInApp(null);
        }
    } catch (e) {
        console.error("openDirections error", e);
    }
};

// copyDirectionsAndShowQR(lat, lng)
// - Shows a QR modal and copies the Google Maps directions link to clipboard (with fallback)
window.copyDirectionsAndShowQR = async function (lat, lng) {
    try {
        if (!lat || !lng) {
            alert("Koordinat tujuan tidak tersedia.");
            return;
        }

        const destination = `${lat},${lng}`;
        const url = `https://www.google.com/maps/dir/?api=1&destination=${encodeURIComponent(destination)}`;

        // Ensure modal exists (we added it in Blade). Populate image and input.
        const qrImg = qs("#directionsQrImage");
        const linkInput = qs("#directionsLinkInput");
        const copyBtn = qs("#copyDirectionsLinkBtn");
        const modal = qs("#directionsQrModal");

        if (qrImg)
            qrImg.src = `https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=${encodeURIComponent(url)}`;
        if (linkInput) linkInput.value = url;

        // Show modal
        if (modal) modal.classList.remove("hidden");

        // Copy handler: try Clipboard API then fallback to selecting the input or prompt
        async function doCopy() {
            try {
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    await navigator.clipboard.writeText(url);
                    if (copyBtn) copyBtn.textContent = "Tersalin";
                    try {
                        window.showCopyToast("Link rute disalin ke clipboard");
                    } catch (e) {}
                    setTimeout(
                        () => copyBtn && (copyBtn.textContent = "Copy link"),
                        1500,
                    );
                    return true;
                }
            } catch (e) {
                // ignore and fallback
            }

            try {
                if (linkInput) {
                    linkInput.removeAttribute("readonly");
                    linkInput.select();
                    document.execCommand("copy");
                    linkInput.setAttribute("readonly", "");
                    if (copyBtn) copyBtn.textContent = "Tersalin";
                    try {
                        window.showCopyToast("Link rute disalin ke clipboard");
                    } catch (e) {}
                    setTimeout(
                        () => copyBtn && (copyBtn.textContent = "Copy link"),
                        1500,
                    );
                    return true;
                }
            } catch (e) {
                // ignore
            }

            // Final fallback: prompt with link
            window.prompt("Salin link rute berikut:", url);
            return false;
        }

        if (copyBtn) {
            copyBtn.onclick = function (ev) {
                ev.preventDefault();
                doCopy();
            };
            // allow touch / pointer interactions
            try {
                copyBtn.style.pointerEvents = "auto";
                copyBtn.style.cursor = "pointer";
            } catch (e) {}
        }

        // Also try to copy immediately so users don't need to tap again (best-effort)
        try {
            await doCopy();
        } catch (e) {}
    } catch (e) {
        console.error("copyDirectionsAndShowQR error", e);
        alert("Terjadi kesalahan. Silakan salin link secara manual.");
    }
};
