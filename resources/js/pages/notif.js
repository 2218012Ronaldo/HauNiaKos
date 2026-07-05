function getHomeNotificationConfig() {
    return window.homeNotificationConfig || {};
}

function toArray(value) {
    return Array.isArray(value) ? value : [];
}

function toStringValue(value, fallback = "") {
    if (value === null || value === undefined) {
        return fallback;
    }

    return String(value);
}

function isSuccessfulNotification(notification) {
    const status = String(notification?.status || "").toLowerCase();

    return ["payment_success", "paid", "settlement"].includes(status);
}

function normalizeNotification(
    notification,
    index,
    notificationRole,
    isOwnerRole,
) {
    if (notificationRole === "guest") {
        return notification;
    }

    return {
        ...notification,
        created_at:
            notification.created_at ||
            new Date(Date.now() - index * 3600000).toISOString(),
    };
}

document.addEventListener("DOMContentLoaded", () => {
    const config = getHomeNotificationConfig();
    const initialNotifications = toArray(config.initialNotifications);
    const sessionCheckout = config.sessionCheckout || null;
    const notificationRole = config.notificationRole || "guest";
    const storageKey = `app_notifications_v1_${notificationRole}`;
    const dismissedNotificationIdsKey = `app_notifications_dismissed_v1_${notificationRole}`;
    const notificationFeedUrl = config.notificationFeedUrl || "";
    const transactionIndexUrl = config.transactionIndexUrl || "";
    const checkBookingBaseUrl = config.checkBookingBaseUrl || "";
    const checkoutUrl = config.checkoutUrl || "";
    const baseKosUrl = config.baseKosUrl || "";
    const csrfToken = config.csrfToken || "";
    const isOwnerRole = notificationRole === "owner";
    const isAdminRole = notificationRole === "admin";
    const isUserRole = notificationRole === "user";

    const badge = document.getElementById("notifBadge");
    const list = document.getElementById("notificationList");
    const dropdown = document.getElementById("notificationDropdown");

    if (!dropdown || !list) {
        return;
    }

    dropdown.dataset.open = "false";

    function saveToStorage(arr) {
        try {
            localStorage.setItem(storageKey, JSON.stringify(arr || []));
        } catch (error) {}
    }

    function loadDismissedNotificationIds() {
        try {
            return new Set(
                JSON.parse(
                    localStorage.getItem(dismissedNotificationIdsKey) || "[]",
                ),
            );
        } catch (error) {
            return new Set();
        }
    }

    function saveDismissedNotificationIds() {
        try {
            localStorage.setItem(
                dismissedNotificationIdsKey,
                JSON.stringify([...dismissedNotificationIds]),
            );
        } catch (error) {}
    }

    function loadFromStorage() {
        try {
            return JSON.parse(localStorage.getItem(storageKey) || "null");
        } catch (error) {
            return null;
        }
    }

    function loadInitialNotifications() {
        if (notificationRole === "guest") {
            return [];
        }

        if (
            Array.isArray(initialNotifications) &&
            initialNotifications.length
        ) {
            if (isAdminRole) {
                return initialNotifications.filter((notification) =>
                    isSuccessfulNotification(notification),
                );
            }

            return initialNotifications;
        }

        const storedNotifications = loadFromStorage();
        if (Array.isArray(storedNotifications) && storedNotifications.length) {
            if (isAdminRole) {
                return storedNotifications.filter((notification) =>
                    isSuccessfulNotification(notification),
                );
            }

            return storedNotifications;
        }

        return [];
    }

    const dismissedNotificationIds = loadDismissedNotificationIds();
    const expandedNotificationIdsKey = `app_notifications_expanded_v1_${notificationRole}`;

    function loadExpandedNotificationIds() {
        try {
            return new Set(
                JSON.parse(
                    localStorage.getItem(expandedNotificationIdsKey) || "[]",
                ),
            );
        } catch (error) {
            return new Set();
        }
    }

    function saveExpandedNotificationIds() {
        try {
            localStorage.setItem(
                expandedNotificationIdsKey,
                JSON.stringify([...expandedNotificationIds]),
            );
        } catch (error) {}
    }

    const expandedNotificationIds = loadExpandedNotificationIds();
    let notifications = loadInitialNotifications().map((notification, index) =>
        normalizeNotification(
            notification,
            index,
            notificationRole,
            isOwnerRole,
        ),
    );

    function buildCheckBookingUrl(notification) {
        const checkoutData =
            notification?.checkout_data || notification?.checkoutData || {};
        const query = new URLSearchParams();

        if (notification?.trx || checkoutData.booking_id) {
            query.set(
                "code",
                String(notification?.trx || checkoutData.booking_id || ""),
            );
        }

        if (checkoutData.customer_email || notification?.customer_email) {
            query.set(
                "email",
                String(
                    checkoutData.customer_email ||
                        notification?.customer_email ||
                        "",
                ),
            );
        }

        if (checkoutData.customer_phone || notification?.customer_phone) {
            query.set(
                "phone_number",
                String(
                    checkoutData.customer_phone ||
                        notification?.customer_phone ||
                        "",
                ),
            );
        }

        const queryString = query.toString();

        return queryString
            ? `${checkBookingBaseUrl}?${queryString}`
            : checkBookingBaseUrl;
    }

    async function copyNotificationValue(event, label, value) {
        try {
            if (event && typeof event.stopPropagation === "function") {
                event.stopPropagation();
            }
        } catch (error) {}

        if (!value) {
            return;
        }

        try {
            await navigator.clipboard.writeText(String(value));

            if (event?.currentTarget) {
                const button = event.currentTarget;
                const originalText = button.textContent;
                button.textContent = "Copied";

                window.setTimeout(() => {
                    button.textContent = originalText || "Copy";
                }, 1400);
            }
        } catch (error) {}
    }

    function toggleNotificationDetails(event, notificationId) {
        try {
            if (event && typeof event.stopPropagation === "function") {
                event.stopPropagation();
            }
        } catch (error) {}

        const key = String(notificationId || "");
        if (!key) {
            return;
        }

        if (expandedNotificationIds.has(key)) {
            expandedNotificationIds.delete(key);
        } else {
            expandedNotificationIds.add(key);
        }

        saveExpandedNotificationIds();
        renderNotifications();
    }

    function getVisibleNotifications() {
        return notifications.filter((notification) => {
            const notificationId = String(notification.id || "");

            return (
                notificationId === "" ||
                !dismissedNotificationIds.has(notificationId)
            );
        });
    }

    function mergeServerNotifications(serverNotifications) {
        const merged = new Map();

        notifications.forEach((notification) => {
            const notificationId = String(notification.id || "");

            if (
                notificationId !== "" &&
                !dismissedNotificationIds.has(notificationId)
            ) {
                merged.set(notificationId, notification);
            }
        });

        (serverNotifications || []).forEach((notification, index) => {
            const normalizedNotification = normalizeNotification(
                notification,
                index,
                notificationRole,
                isOwnerRole,
            );
            const notificationId = String(normalizedNotification.id || "");

            if (
                notificationId !== "" &&
                dismissedNotificationIds.has(notificationId)
            ) {
                return;
            }

            merged.set(
                notificationId || `local-${index}-${Date.now()}`,
                normalizedNotification,
            );
        });

        notifications = [...merged.values()];
        notifications.sort((a, b) => {
            const da = new Date(a.created_at || a.date || 0).getTime();
            const db = new Date(b.created_at || b.date || 0).getTime();

            return db - da;
        });
        saveToStorage(notifications);
    }

    async function fetchNotificationFeed() {
        if (!notificationFeedUrl || notificationRole === "guest") {
            return;
        }

        try {
            const response = await fetch(notificationFeedUrl, {
                headers: {
                    Accept: "application/json",
                },
                credentials: "same-origin",
            });

            if (!response.ok) {
                return;
            }

            const payload = await response.json();
            const serverNotifications = Array.isArray(payload.data)
                ? payload.data
                : [];

            if (serverNotifications.length === 0) {
                return;
            }

            mergeServerNotifications(serverNotifications);
            updateBadge();

            if (dropdown.dataset.open === "true") {
                renderNotifications();
            }
        } catch (error) {}
    }

    function formatNotificationDate(value) {
        if (!value) {
            return "";
        }

        const date = new Date(value);
        if (Number.isNaN(date.getTime())) {
            return String(value);
        }

        return new Intl.DateTimeFormat("en-US", {
            month: "short",
            day: "numeric",
            hour: "2-digit",
            minute: "2-digit",
        }).format(date);
    }

    function getNotificationMeta(status) {
        const normalizedStatus = String(status || "approved").toLowerCase();

        if (isAdminRole) {
            if (
                ["payment_success", "paid", "settlement"].includes(
                    normalizedStatus,
                )
            ) {
                return {
                    badge: "Booking Success",
                    tone: "from-emerald-500/12 to-teal-500/12 text-emerald-700",
                    pill: "bg-emerald-500/10 text-emerald-700",
                    description: "A booking has been paid successfully.",
                    showCheckStatus: true,
                };
            }

            return {
                badge: "Booking Success",
                tone: "from-emerald-500/12 to-teal-500/12 text-emerald-700",
                pill: "bg-emerald-500/10 text-emerald-700",
                description: "A booking update is available.",
                showCheckStatus: true,
            };
        }

        if (isOwnerRole) {
            if (
                ["pending", "waiting_approval", "approval_pending"].includes(
                    normalizedStatus,
                )
            ) {
                return {
                    badge: "Pending Approval",
                    tone: "from-white/95 to-slate-50/95 text-slate-800",
                    pill: "bg-amber-500/10 text-amber-800",
                    description: "A new booking is waiting for your approval.",
                    actionStyle: "owner-review",
                };
            }

            if (
                ["payment_success", "paid", "settlement"].includes(
                    normalizedStatus,
                )
            ) {
                return {
                    badge: "Paid",
                    tone: "from-emerald-500/12 to-teal-500/12 text-emerald-700",
                    pill: "bg-emerald-500/10 text-emerald-700",
                    description: "The user has completed the payment.",
                    actionStyle: "owner-paid",
                };
            }

            if (normalizedStatus === "not_approved") {
                return {
                    badge: "Rejected",
                    tone: "from-rose-500/12 to-red-500/12 text-rose-700",
                    pill: "bg-rose-500/10 text-rose-700",
                    description: "This booking has been rejected.",
                    actionStyle: "owner-closed",
                };
            }

            return {
                badge: "Approved",
                tone: "from-emerald-500/12 to-green-500/12 text-emerald-700",
                pill: "bg-emerald-500/10 text-emerald-700",
                description: "This booking has been approved.",
                actionStyle: "owner-closed",
            };
        }

        if (
            ["payment_success", "paid", "settlement"].includes(normalizedStatus)
        ) {
            return {
                badge: "Payment Success",
                tone: "from-emerald-500/14 to-teal-500/14 text-emerald-800",
                pill: "bg-emerald-500/10 text-emerald-700",
                description: "Your payment has been confirmed successfully.",
                showPayNow: false,
                showViewKos: true,
            };
        }

        if (normalizedStatus === "approved") {
            return {
                badge: "Approved",
                tone: "from-emerald-500/12 to-lime-500/12 text-emerald-800",
                pill: "bg-emerald-500/10 text-emerald-700",
                description: "Your booking has been approved by the owner.",
                showPayNow: true,
                showViewKos: false,
            };
        }

        if (normalizedStatus === "not_approved") {
            return {
                badge: "Rejected",
                tone: "from-rose-500/20 to-red-500/18 text-rose-950",
                pill: "bg-rose-500/15 text-rose-800",
                description: "This booking has been rejected.",
                showPayNow: false,
                showViewKos: false,
            };
        }

        return {
            badge: "Approved",
            tone: "from-emerald-500/12 to-lime-500/12 text-emerald-800",
            pill: "bg-emerald-500/10 text-emerald-700",
            description: "Your booking has been approved by the owner.",
            showPayNow: true,
            showViewKos: false,
        };
    }

    function updateBadge() {
        if (!badge) {
            return;
        }

        const visibleNotifications = getVisibleNotifications();

        if (!visibleNotifications || visibleNotifications.length === 0) {
            badge.classList.add("hidden");
            badge.textContent = "";
        } else {
            badge.classList.remove("hidden");
            badge.textContent = String(visibleNotifications.length);
        }
    }

    function renderNotifications() {
        list.innerHTML = "";
        const visibleNotifications = getVisibleNotifications()
            .slice()
            .sort((a, b) => {
                const da = new Date(a.created_at || a.date || 0).getTime();
                const db = new Date(b.created_at || b.date || 0).getTime();

                return db - da;
            });

        if (!visibleNotifications || visibleNotifications.length === 0) {
            list.innerHTML =
                '<div class="text-center text-sm text-slate-500">No notifications</div>';
            return;
        }

        visibleNotifications.forEach((notification, index) => {
            const trx = notification.trx || notification.code || "";
            const kos =
                notification.kos_name ||
                notification.kos ||
                notification.name ||
                "the boarding house";
            const status = notification.status || "approved";
            const meta = getNotificationMeta(status);
            const dateLabel = formatNotificationDate(
                notification.created_at ||
                    notification.date ||
                    notification.time,
            );
            const viewUrl = notification.kos_slug
                ? `${baseKosUrl}/${notification.kos_slug}`
                : notification.view_url || "#";
            const normalizedStatus = String(status || "").toLowerCase();
            const notificationId = String(notification.id || `index-${index}`);
            const isOwnerActionable =
                isOwnerRole &&
                ["pending", "waiting_approval", "approval_pending"].includes(
                    normalizedStatus,
                );
            const isOwnerPaid =
                isOwnerRole &&
                ["payment_success", "paid", "settlement"].includes(
                    normalizedStatus,
                );
            const isOwnerResettable =
                isOwnerRole &&
                normalizedStatus !== "pending" &&
                normalizedStatus !== "waiting_approval" &&
                normalizedStatus !== "approval_pending";
            const isUserPaymentSuccess =
                isUserRole &&
                ["payment_success", "paid", "settlement"].includes(
                    normalizedStatus,
                );
            const isDetailExpanded =
                expandedNotificationIds.has(notificationId);
            const checkoutData =
                notification.checkout_data || notification.checkoutData || {};
            const bookingId = notification.trx || checkoutData.booking_id || "";
            const customerEmail =
                checkoutData.customer_email ||
                notification.customer_email ||
                "";
            const customerPhone =
                checkoutData.customer_phone ||
                notification.customer_phone ||
                "";
            const adminActionButtons =
                isAdminRole && meta.showCheckStatus
                    ? `<a href="${transactionIndexUrl || "#"}" class="notification-action-check inline-flex cursor-pointer items-center rounded-full px-4 py-1.5 text-xs font-semibold transition">Check Status</a>`
                    : "";
            const ownerActionButtons = isOwnerActionable
                ? `
                    <button type="button" class="notification-action-approve inline-flex cursor-pointer items-center rounded-full px-4 py-1.5 text-xs font-semibold transition" onclick="handleOwnerNotificationDecision(event, ${index}, 'approve')">Approve</button>
                    <button type="button" class="notification-action-reject inline-flex cursor-pointer items-center rounded-full px-4 py-1.5 text-xs font-semibold transition" onclick="handleOwnerNotificationDecision(event, ${index}, 'reject')">Reject</button>
                    ${isOwnerResettable ? `<button type="button" class="notification-action-reset inline-flex cursor-pointer items-center rounded-full px-3.5 py-1.5 text-xs font-semibold transition" onclick="resetOwnerNotification(event, ${index})">Reset</button>` : ""}
                `
                : isOwnerPaid
                  ? `<a href="${transactionIndexUrl || "#"}" class="notification-action-check inline-flex cursor-pointer items-center rounded-full px-4 py-1.5 text-xs font-semibold transition">Check Status</a>`
                  : "";
            const userActionButtons = isUserPaymentSuccess
                ? `<a href="${buildCheckBookingUrl(notification)}" class="notification-action-view inline-flex cursor-pointer items-center rounded-full px-3.5 py-1.5 text-xs font-semibold transition">View My Booking</a>`
                : meta.showViewKos
                  ? `<a href="${viewUrl}" class="notification-action-view inline-flex cursor-pointer items-center rounded-full px-3.5 py-1.5 text-xs font-semibold transition">View Kos</a>`
                  : "";
            const payNowButton =
                meta.showPayNow && !isUserPaymentSuccess
                    ? `<button type="button" class="inline-flex cursor-pointer items-center rounded-full bg-gradient-to-r from-orange-500 to-orange-600 px-4 py-1.5 text-xs font-semibold text-white shadow-[0px_10px_18px_-12px_rgba(249,115,22,0.9)] transition hover:from-orange-600 hover:to-orange-700" onclick="payNowFromNotif(event, ${index})">Pay Now</button>`
                    : "";
            const actionButtons = isOwnerRole
                ? ownerActionButtons
                : isAdminRole
                  ? adminActionButtons
                  : `${userActionButtons}${payNowButton}`;
            const detailIcon = isDetailExpanded
                ? '<path d="M6 14l6-6 6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />'
                : '<path d="M6 10l6 6 6-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />';
            const detailsPanel = isUserPaymentSuccess
                ? `
                    <div class="mt-4 rounded-2xl border border-white/60 bg-white/55 p-3 backdrop-blur-md">
                        <button type="button" onclick="toggleNotificationDetails(event, '${notificationId}')" class="flex w-full items-center justify-between gap-3 text-left">
                            <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-600">Detail</span>
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-white text-slate-700 shadow-sm ring-1 ring-slate-200/70">
                                <svg class="h-4 w-4 transition-transform duration-200" viewBox="0 0 24 24" fill="none" aria-hidden="true">${detailIcon}</svg>
                            </span>
                        </button>
                        ${
                            isDetailExpanded
                                ? `
                            <div class="mt-3 space-y-2">
                                ${[
                                    ["Booking ID", bookingId],
                                    ["Email", customerEmail],
                                    ["Phone", customerPhone],
                                ]
                                    .map(
                                        ([label, value]) => `
                                    <div class="flex items-center justify-between gap-3 rounded-2xl bg-white/85 px-3 py-2.5 shadow-sm ring-1 ring-slate-200/70">
                                        <div class="min-w-0">
                                            <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-400">${label}</p>
                                            <p class="truncate text-sm font-semibold text-slate-900">${value || "-"}</p>
                                        </div>
                                        <button type="button" class="shrink-0 rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-[11px] font-semibold text-slate-700 transition hover:border-sky-300 hover:bg-sky-50 hover:text-sky-700" onclick="copyNotificationValue(event, '${label}', ${JSON.stringify(value || "")})">Copy</button>
                                    </div>
                                `,
                                    )
                                    .join("")}
                            </div>
                        `
                                : ""
                        }
                    </div>
                `
                : "";

            const container = document.createElement("div");
            container.className = `rounded-[1.5rem] border border-slate-200/70 bg-gradient-to-br ${meta.tone} p-4 shadow-[0px_18px_44px_-30px_rgba(15,23,42,0.35)] backdrop-blur-2xl`;
            container.innerHTML = `
                <div class="flex items-start gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl border border-white/70 bg-white/80 text-slate-900 shadow-sm backdrop-blur-md">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M12 22a2.5 2.5 0 0 0 2.5-2.5h-5A2.5 2.5 0 0 0 12 22Z" fill="currentColor" opacity="0.85" />
                            <path d="M18 16H6c1-1 1.5-2 1.5-4V9a4.5 4.5 0 1 1 9 0v3c0 2 .5 3 1.5 4Z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center rounded-full ${meta.pill} px-2 py-0.5 text-[10px] font-semibold uppercase tracking-[0.16em]">${meta.badge}</span>
                            <span class="text-[11px] font-medium text-slate-500">Booking update</span>
                        </div>
                        <p class="mt-2 text-sm font-semibold leading-6 text-slate-900">${notification.body && notification.body.length ? notification.body : meta.description} Booking code <span class="font-bold text-slate-950">${trx}</span> for <span class="font-bold text-slate-950">${kos}</span>.</p>
                        <p class="mt-2 text-xs leading-5 text-slate-600">${dateLabel ? `Received on ${dateLabel}.` : "Recent update from your booking activity."}</p>
                    </div>
                </div>
                ${detailsPanel}
                <div class="mt-4 flex items-center justify-end gap-2">
                    ${actionButtons}
                    <button type="button" class="notification-action-dismiss inline-flex cursor-pointer items-center rounded-full px-3.5 py-1.5 text-xs font-semibold transition" onclick="dismissNotif(event, ${index})">Dismiss</button>
                </div>
            `;

            list.appendChild(container);
        });
    }

    window.handleOwnerNotificationDecision = function (event, index, decision) {
        try {
            if (event && typeof event.stopPropagation === "function") {
                event.stopPropagation();
            }
        } catch (error) {}

        const visible = getVisibleNotifications()
            .slice()
            .sort((a, b) => {
                const da = new Date(a.created_at || a.date || 0).getTime();
                const db = new Date(b.created_at || b.date || 0).getTime();

                return db - da;
            });

        const notification = visible[index];

        if (!notification) {
            return;
        }

        const transactionCode =
            notification.trx ||
            notification.code ||
            notification.reference ||
            "";

        if (!transactionCode) {
            return;
        }

        // Call backend API to approve/reject
        fetch("/booking/approve-reject", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": window.homeNotificationConfig?.csrfToken || "",
            },
            body: JSON.stringify({
                transaction_code: transactionCode,
                decision: decision,
            }),
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    // Refresh notifications from server
                    fetchNotificationFeed().then(() => {
                        renderNotifications();
                        updateBadge();
                    });
                }
            })
            .catch((error) => {
                console.error("Error:", error);
            });
    };

    window.resetOwnerNotification = function (event, index) {
        try {
            if (event && typeof event.stopPropagation === "function") {
                event.stopPropagation();
            }
        } catch (error) {}

        const visible = getVisibleNotifications()
            .slice()
            .sort((a, b) => {
                const da = new Date(a.created_at || a.date || 0).getTime();
                const db = new Date(b.created_at || b.date || 0).getTime();

                return db - da;
            });

        const notification = visible[index];
        if (!notification) {
            return;
        }

        const idKey = String(notification.id || "");

        notifications = notifications.map((item) => {
            if (String(item.id || "") === idKey) {
                return {
                    ...item,
                    status: "pending",
                    updated_at: new Date().toISOString(),
                };
            }

            return item;
        });

        saveToStorage(notifications);
        renderNotifications();
        updateBadge();
    };

    window.payNowFromNotif = function (event, index) {
        try {
            if (event && typeof event.stopPropagation === "function") {
                event.stopPropagation();
            }
        } catch (error) {}

        const notification = notifications[index];

        if (!notification) {
            return;
        }

        const checkoutData =
            notification.checkoutData ||
            notification.checkout_data ||
            sessionCheckout ||
            null;

        if (!checkoutData) {
            window.location.href = checkoutUrl || checkBookingBaseUrl;
            return;
        }

        const transactionCode =
            notification.trx ||
            checkoutData.trx ||
            checkoutData.booking_id ||
            "";
        const kosSlug = notification.kos_slug || checkoutData.kos_slug || "";

        if (!transactionCode || !kosSlug) {
            window.location.href = checkoutUrl || checkBookingBaseUrl;
            return;
        }

        const payNowUrl = `${baseKosUrl}/booking/${kosSlug}/pay-now?transaction_code=${encodeURIComponent(transactionCode)}`;
        window.location.href = payNowUrl;
    };

    window.dismissNotif = function (event, index) {
        try {
            if (event && typeof event.stopPropagation === "function") {
                event.stopPropagation();
            }
        } catch (error) {}

        const notification = getVisibleNotifications()[index];

        if (notification && notification.id) {
            dismissedNotificationIds.add(String(notification.id));
            saveDismissedNotificationIds();
        }

        notifications = notifications.filter(
            (item) => String(item.id || "") !== String(notification?.id || ""),
        );
        saveToStorage(notifications);
        renderNotifications();
        updateBadge();
    };

    window.toggleNotificationDetails = toggleNotificationDetails;
    window.copyNotificationValue = copyNotificationValue;

    window.clearAllNotifs = function () {
        getVisibleNotifications().forEach((notification) => {
            if (notification.id) {
                dismissedNotificationIds.add(String(notification.id));
            }
        });

        saveDismissedNotificationIds();
        notifications = [];
        saveToStorage(notifications);
        renderNotifications();
        updateBadge();

        try {
            fetch("/notifications/clear", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                },
                body: JSON.stringify({}),
            }).catch(() => {});
        } catch (error) {}
    };

    window.toggleNotifModal = function () {
        const isOpen = dropdown.dataset.open === "true";

        if (!isOpen) {
            dropdown.dataset.open = "true";
            dropdown.classList.remove(
                "opacity-0",
                "pointer-events-none",
                "translate-y-2",
                "scale-[0.98]",
            );
            dropdown.classList.add(
                "opacity-100",
                "pointer-events-auto",
                "translate-y-0",
                "scale-100",
            );
            renderNotifications();
        } else {
            window.closeNotifModal();
        }
    };

    window.closeNotifModal = function () {
        dropdown.dataset.open = "false";
        dropdown.classList.add(
            "opacity-0",
            "pointer-events-none",
            "translate-y-2",
            "scale-[0.98]",
        );
        dropdown.classList.remove(
            "opacity-100",
            "pointer-events-auto",
            "translate-y-0",
            "scale-100",
        );
    };

    document.addEventListener("click", (event) => {
        const notifBtn = document.getElementById("notifBtn");

        if (dropdown.dataset.open !== "true" || !notifBtn) {
            return;
        }

        if (
            !dropdown.contains(event.target) &&
            !notifBtn.contains(event.target)
        ) {
            window.closeNotifModal();
        }
    });

    updateBadge();
    saveToStorage(notifications);
    saveDismissedNotificationIds();

    if (notificationRole !== "guest") {
        fetchNotificationFeed();
        window.setInterval(fetchNotificationFeed, 5000);
    }

    window.addEventListener("load", () => {
        window.closeNotifModal();
    });
});
