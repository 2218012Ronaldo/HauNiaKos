flatpickr("#moving_date", {
    minDate: "today",
    dateFormat: "d M Y",
    altInput: false,
    allowInput: false,
});

// Prevent keyboard from showing on mobile
const movingDateInput = document.getElementById("moving_date");
if (movingDateInput) {
    movingDateInput.setAttribute("readonly", true);
    movingDateInput.addEventListener("focus", function (e) {
        e.preventDefault();
        this.blur();
        // Open Flatpickr programmatically
        this._flatpickr.open();
    });
}

const minusButton = document.getElementById("Minus");
const plusButton = document.getElementById("Plus");
const durationInput = document.getElementById("Duration");
const priceElement = document.getElementById("price");
const maxDuration = 999; // Maximum allowed value

function formatUsd(amountInUsd) {
    return new Intl.NumberFormat("en-US", {
        style: "currency",
        currency: "USD",
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(amountInUsd);
}

function updatePrice() {
    let duration = parseInt(durationInput.value, 10);

    // Only update price if the value is a valid number
    if (!isNaN(duration) && duration >= 1 && duration <= maxDuration) {
        const totalPrice = defaultPrice * duration;
        priceElement.innerHTML = formatUsd(totalPrice);
    } else {
        priceElement.innerHTML = formatUsd(0);
    }
}

function validateInput(value) {
    // Replace any non-digit characters and limit to 3 digits
    value = value.replace(/\D/g, "").slice(0, 3);

    // Ensure value is not zero
    if (parseInt(value, 10) === 0) {
        return "1";
    }

    return value;
}

// Restrict input to numbers only, with a max of 3 digits
durationInput.addEventListener("input", () => {
    let value = validateInput(durationInput.value);

    // Prevent auto-reset to 1 when the input is being cleared for new value
    if (value === "") {
        durationInput.value = ""; // Allow the input to be empty
        priceElement.innerHTML = "$0.00"; // Optionally show 0 or placeholder
        return;
    }

    durationInput.value = value;
    updatePrice();
});

durationInput.addEventListener("blur", () => {
    // If the input is empty or zero when it loses focus, set it back to 1
    if (durationInput.value === "" || parseInt(durationInput.value, 10) === 0) {
        durationInput.value = "1";
        updatePrice();
    }
});

minusButton.addEventListener("click", () => {
    let value = parseInt(durationInput.value, 10);
    if (isNaN(value) || value <= 1) {
        value = 1; // Prevent going below 1
    } else {
        value--;
    }
    durationInput.value = value;
    updatePrice();
});

plusButton.addEventListener("click", () => {
    let value = parseInt(durationInput.value, 10);
    if (isNaN(value)) {
        value = 1; // Default to 1 if invalid
    } else if (value < maxDuration) {
        value++;
    } else {
        value = maxDuration; // Prevent going above 999
    }
    durationInput.value = value;
    updatePrice();
});

// Initial price update
updatePrice();
