function qs(selector, root = document) {
    return root.querySelector(selector);
}

function qsa(selector, root = document) {
    return Array.from(root.querySelectorAll(selector));
}

function getAuthUrl(type) {
    const loginModal = document.getElementById("loginModal");

    if (!loginModal) {
        return "#";
    }

    if (type === "register") {
        return loginModal.dataset.registerUrl || "#";
    }

    return loginModal.dataset.loginUrl || "#";
}

window.openLoginModal = function () {
    const loginModal = document.getElementById("loginModal");

    if (!loginModal) {
        return;
    }

    loginModal.classList.remove("hidden");
    loginModal.classList.add("flex");
    document.body.classList.add("overflow-hidden");
};

window.closeLoginModal = function () {
    const loginModal = document.getElementById("loginModal");

    if (!loginModal) {
        return;
    }

    loginModal.classList.add("hidden");
    loginModal.classList.remove("flex");
    document.body.classList.remove("overflow-hidden");
};

window.closeSuccessNotif = function () {
    const root = document.getElementById("successNotif");

    if (!root) {
        return;
    }

    root.classList.remove("notif-show");
    root.classList.add("notif-hide");
    root.setAttribute("aria-hidden", "true");

    setTimeout(() => {
        try {
            root.remove();
        } catch (error) {}
    }, 350);
};

window.showSuccessNotif = function (message = null) {
    const root = document.getElementById("successNotif");

    if (!root) {
        return;
    }

    if (message) {
        const text = document.getElementById("notifText");

        if (text) {
            text.textContent = message;
        }
    }

    const type = root.dataset.type || "login";
    const inner = document.getElementById("successNotifInner");

    if (inner) {
        if (type === "logout") {
            inner.classList.remove("notif-login");
            inner.classList.add("notif-logout");
        } else {
            inner.classList.remove("notif-logout");
            inner.classList.add("notif-login");
        }
    }

    root.classList.remove("notif-hide");
    root.classList.add("notif-show");
    root.setAttribute("aria-hidden", "false");

    setTimeout(() => {
        window.closeSuccessNotif();
    }, 6000);
};

window.toggleUserMenu = function (event) {
    const userMenuPanel = document.getElementById("userMenuPanel");
    const userMenuTrigger = document.getElementById("userMenuTrigger");
    const userMenuChevron = document.getElementById("userMenuChevron");

    if (!userMenuPanel || !userMenuTrigger) {
        return;
    }

    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }

    const isHidden = userMenuPanel.classList.contains("hidden");

    if (isHidden) {
        userMenuPanel.classList.remove("hidden");
        userMenuTrigger.setAttribute("aria-expanded", "true");

        if (userMenuChevron) {
            userMenuChevron.style.transform = "rotate(180deg)";
        }
    } else {
        window.closeUserMenu();
    }
};

window.closeUserMenu = function () {
    const userMenuPanel = document.getElementById("userMenuPanel");
    const userMenuTrigger = document.getElementById("userMenuTrigger");
    const userMenuChevron = document.getElementById("userMenuChevron");

    if (!userMenuPanel || !userMenuTrigger) {
        return;
    }

    userMenuPanel.classList.add("hidden");
    userMenuTrigger.setAttribute("aria-expanded", "false");

    if (userMenuChevron) {
        userMenuChevron.style.transform = "rotate(0deg)";
    }
};

window.selectRole = function (role) {
    const roleSelectionPanel = document.getElementById("roleSelectionPanel");
    const authPanel = document.getElementById("authPanel");
    const infoText = document.getElementById("infoText");
    const loginLink = document.getElementById("loginLink");
    const registerLink = document.getElementById("registerLink");

    if (!roleSelectionPanel || !authPanel) {
        return;
    }

    roleSelectionPanel.classList.add("hidden");
    authPanel.classList.remove("hidden");
    authPanel.classList.add("flex");

    if (infoText) {
        infoText.textContent =
            role === "owner_kost" ? "Role: pemilik kos" : "Role: pencari kos";
    }

    if (loginLink) {
        loginLink.href = `${getAuthUrl("login")}?role=${role}`;
    }

    if (registerLink) {
        registerLink.href = `${getAuthUrl("register")}?role=${role}`;
    }
};

window.backToRoleSelection = function () {
    const roleSelectionPanel = document.getElementById("roleSelectionPanel");
    const authPanel = document.getElementById("authPanel");
    const infoText = document.getElementById("infoText");

    if (!roleSelectionPanel || !authPanel) {
        return;
    }

    authPanel.classList.add("hidden");
    authPanel.classList.remove("flex");
    roleSelectionPanel.classList.remove("hidden");

    if (infoText) {
        infoText.textContent =
            "Role yang dipilih akan dipakai untuk login dan daftar.";
    }
};

// ====== Filter Functions ======

function formatRupiahValue(value) {
    return new Intl.NumberFormat("id-ID").format(Number(value || 0));
}

window.openRecommendationFilterPanel = function () {
    const recommendationFilterPanel = document.getElementById(
        "recommendationFilterPanel",
    );
    const filterTrigger = document.getElementById("filterTrigger");

    if (!recommendationFilterPanel || !filterTrigger) {
        return;
    }

    recommendationFilterPanel.classList.remove("hidden");
    filterTrigger.setAttribute("aria-expanded", "true");
};

window.closeRecommendationFilterPanel = function () {
    const recommendationFilterPanel = document.getElementById(
        "recommendationFilterPanel",
    );
    const filterTrigger = document.getElementById("filterTrigger");

    if (!recommendationFilterPanel || !filterTrigger) {
        return;
    }

    recommendationFilterPanel.classList.add("hidden");
    filterTrigger.setAttribute("aria-expanded", "false");
};

function syncPriceRange() {
    const priceMaxRange = document.getElementById("priceMaxRange");
    const priceMaxValue = document.getElementById("priceMaxValue");

    if (!priceMaxRange || !priceMaxValue) {
        return;
    }

    priceMaxValue.textContent = formatRupiahValue(priceMaxRange.value);
}

function syncDistanceRange() {
    const distanceMaxRange = document.getElementById("distanceMaxRange");
    const distanceMaxValue = document.getElementById("distanceMaxValue");

    if (!distanceMaxRange || !distanceMaxValue) {
        return;
    }

    distanceMaxValue.textContent = Number(distanceMaxRange.value)
        .toFixed(1)
        .replace(".", ",");
}

function setActiveRatingButton(value) {
    const ratingStarButtons = document.querySelectorAll(".rating-star-btn");

    ratingStarButtons.forEach((button) => {
        const isActive = button.dataset.value === String(value);

        button.classList.toggle("is-active", isActive);
        button.classList.toggle("border-orange-300", isActive);
        button.classList.toggle("bg-orange-50", isActive);
        button.classList.toggle("text-orange-700", isActive);
        button.classList.toggle("shadow-sm", isActive);
        button.classList.toggle("font-semibold", isActive);

        button.classList.toggle("border-slate-200", !isActive);
        button.classList.toggle("bg-white", !isActive);
        button.classList.toggle(
            "text-slate-500",
            !isActive && button.dataset.value === "all",
        );
        button.classList.toggle(
            "text-slate-600",
            !isActive && button.dataset.value !== "all",
        );
    });
}

function syncFilterToggleState() {
    const priceEnabledCheckbox = document.getElementById(
        "priceEnabledCheckbox",
    );
    const priceSliderGroup = document.getElementById("priceSliderGroup");
    const priceMaxRange = document.getElementById("priceMaxRange");
    const distanceEnabledCheckbox = document.getElementById(
        "distanceEnabledCheckbox",
    );
    const distanceSliderGroup = document.getElementById("distanceSliderGroup");
    const distanceMaxRange = document.getElementById("distanceMaxRange");

    if (priceEnabledCheckbox && priceSliderGroup && priceMaxRange) {
        const isPriceEnabled = priceEnabledCheckbox.checked;

        priceSliderGroup.classList.toggle("hidden", !isPriceEnabled);
        priceMaxRange.disabled = !isPriceEnabled;
    }

    if (distanceEnabledCheckbox && distanceSliderGroup && distanceMaxRange) {
        const isDistanceEnabled = distanceEnabledCheckbox.checked;

        distanceSliderGroup.classList.toggle("hidden", !isDistanceEnabled);
        distanceMaxRange.disabled = !isDistanceEnabled;
    }
}

function initFiltersUI() {
    const filterWrapper = document.getElementById("filterWrapper");
    const filterTrigger = document.getElementById("filterTrigger");
    const recommendationFilterPanel = document.getElementById(
        "recommendationFilterPanel",
    );
    const priceEnabledCheckbox = document.getElementById(
        "priceEnabledCheckbox",
    );
    const priceMaxRange = document.getElementById("priceMaxRange");
    const distanceEnabledCheckbox = document.getElementById(
        "distanceEnabledCheckbox",
    );
    const distanceMaxRange = document.getElementById("distanceMaxRange");
    const ratingCategoryInput = document.getElementById("ratingCategoryInput");
    const ratingStarButtons = document.querySelectorAll(".rating-star-btn");
    const recommendationFilterForm = document.getElementById(
        "recommendationFilterForm",
    );

    // Filter panel toggle
    if (filterTrigger) {
        filterTrigger.addEventListener("click", (event) => {
            event.preventDefault();
            event.stopPropagation();

            if (!recommendationFilterPanel) {
                return;
            }

            const isClosed =
                recommendationFilterPanel.classList.contains("hidden");

            if (isClosed) {
                window.openRecommendationFilterPanel();
            } else {
                window.closeRecommendationFilterPanel();
            }
        });
    }

    // Close filter when clicking outside
    document.addEventListener("click", (event) => {
        if (!filterWrapper || !recommendationFilterPanel || !filterTrigger) {
            return;
        }

        if (!filterWrapper.contains(event.target)) {
            window.closeRecommendationFilterPanel();
        }
    });

    // Price range sync
    if (priceMaxRange) {
        priceMaxRange.addEventListener("input", () => {
            syncPriceRange();
        });

        syncPriceRange();
    }

    // Distance range sync
    if (distanceMaxRange) {
        distanceMaxRange.addEventListener("input", syncDistanceRange);
        syncDistanceRange();
    }

    // Rating button listeners
    ratingStarButtons.forEach((button) => {
        button.addEventListener("click", (e) => {
            e.preventDefault();

            if (!ratingCategoryInput) {
                return;
            }

            ratingCategoryInput.value = button.dataset.value || "all";
            setActiveRatingButton(ratingCategoryInput.value);
        });
    });

    if (ratingCategoryInput) {
        setActiveRatingButton(ratingCategoryInput.value);
    }

    // Persist rating selection on form submit
    if (recommendationFilterForm) {
        recommendationFilterForm.addEventListener("submit", () => {
            if (!ratingCategoryInput) return;

            const activeBtn = document.querySelector(
                ".rating-star-btn.is-active",
            );
            if (activeBtn) {
                ratingCategoryInput.value = activeBtn.dataset.value || "all";
            }
        });
    }

    // Filter toggle state
    if (priceEnabledCheckbox) {
        priceEnabledCheckbox.addEventListener("change", syncFilterToggleState);
    }

    if (distanceEnabledCheckbox) {
        distanceEnabledCheckbox.addEventListener(
            "change",
            syncFilterToggleState,
        );
    }

    syncFilterToggleState();
}

function initHomeAuthUi() {
    const root = document.getElementById("successNotif");

    if (root) {
        const signature = `${root.dataset.type || "login"}:${root.dataset.message || ""}`;

        if (sessionStorage.getItem("successNotifSignature") === signature) {
            root.remove();
            return;
        }

        sessionStorage.setItem("successNotifSignature", signature);

        // Ensure element is visible if something hid it
        try {
            root.style.display = root.style.display || "block";
        } catch (e) {}

        // Ensure element is moved to body to prevent clipping by transformed ancestors
        setTimeout(() => {
            try {
                if (root.parentNode !== document.body)
                    document.body.appendChild(root);
            } catch (e) {}

            window.showSuccessNotif();
        }, 30);
    }

    document.addEventListener("click", (event) => {
        const userMenuPanel = document.getElementById("userMenuPanel");
        const userMenuTrigger = document.getElementById("userMenuTrigger");

        if (!userMenuPanel || !userMenuTrigger) {
            return;
        }

        if (
            !userMenuPanel.contains(event.target) &&
            !userMenuTrigger.contains(event.target)
        ) {
            window.closeUserMenu();
        }
    });

    document.addEventListener("keydown", (event) => {
        if (event.key === "Escape") {
            window.closeUserMenu();
            window.closeLoginModal();
            window.closeRecommendationFilterPanel();
        }
    });

    initFiltersUI();
}

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initHomeAuthUi);
} else {
    initHomeAuthUi();
}
