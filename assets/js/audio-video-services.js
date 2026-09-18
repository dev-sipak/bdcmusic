document.addEventListener("DOMContentLoaded", function () {
    const form = document.querySelector(".premium-booking-form");

    if (!form) {
        return;
    }

    const categoryInputs = form.querySelectorAll('input[name="service_category"]');
    const serviceSelect = form.querySelector("#service_type");
    const summary = document.querySelector("#live-summary");

    if (!serviceSelect) {
        return;
    }

    function currentCategory() {
        const selected = form.querySelector('input[name="service_category"]:checked');
        return selected ? selected.value : "Audio";
    }

    function filterServices() {
        const category = currentCategory();

        Array.from(serviceSelect.options).forEach(function (option) {
            if (!option.dataset.category) {
                option.hidden = false;
                return;
            }

            option.hidden = option.dataset.category !== category;
        });

        if (serviceSelect.selectedOptions[0] && serviceSelect.selectedOptions[0].hidden) {
            serviceSelect.value = "";
        }
    }

    function updateSummary() {
        if (!summary) {
            return;
        }

        const formats = Array.from(form.querySelectorAll('input[name="delivery_format[]"]:checked'))
            .map(function (input) {
                return input.value;
            })
            .join(", ");

        summary.innerHTML = "Category: <strong>" + currentCategory() + "</strong><br>" +
            "Service: <strong>" + (serviceSelect.value || "Not selected") + "</strong><br>" +
            "Delivery: <strong>" + (formats || "Not selected") + "</strong>";
    }

    categoryInputs.forEach(function (input) {
        input.addEventListener("change", function () {
            filterServices();
            updateSummary();
        });
    });

    form.querySelectorAll("select, input").forEach(function (input) {
        input.addEventListener("change", updateSummary);
    });

    filterServices();
    updateSummary();
});
