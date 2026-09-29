document.addEventListener("DOMContentLoaded", function () {
    var modal = document.getElementById("plan-enquiry-modal");

    if (!modal) {
        return;
    }

    var form = document.getElementById("plan-enquiry-form");
    var planIdInput = document.getElementById("enquiry-plan-id");
    var packageLabel = document.getElementById("plan-enquiry-package");
    var errorBox = document.getElementById("plan-enquiry-error");
    var lastTrigger = null;

    function showError(message) {
        if (!errorBox) {
            return;
        }

        errorBox.innerHTML = "<strong>" + message + "</strong>";
        errorBox.style.display = "block";
    }

    function clearError() {
        if (!errorBox) {
            return;
        }

        errorBox.style.display = "none";
        errorBox.innerHTML = "";
    }

    function openModal(trigger) {
        var planId = trigger.getAttribute("data-plan-enquiry") || "";
        var name = trigger.getAttribute("data-plan-name") || "";

        // The plan id comes from the button, never from the URL, so a hand-edited
        // link cannot ask for a package the customer did not pick.
        if (planIdInput) {
            planIdInput.value = planId;
        }

        if (packageLabel) {
            packageLabel.textContent = name;
        }

        clearError();
        lastTrigger = trigger;
        modal.classList.add("open");
        document.body.style.overflow = "hidden";

        var first = document.getElementById("enquiry-name");
        if (first) {
            first.focus();
        }
    }

    function closeModal() {
        modal.classList.remove("open");
        document.body.style.overflow = "";

        if (lastTrigger && typeof lastTrigger.focus === "function") {
            lastTrigger.focus();
        }

        lastTrigger = null;
    }

    document.querySelectorAll("[data-plan-enquiry]").forEach(function (trigger) {
        trigger.addEventListener("click", function (event) {
            event.preventDefault();
            openModal(trigger);
        });
    });

    modal.querySelectorAll("[data-enquiry-close]").forEach(function (el) {
        el.addEventListener("click", closeModal);
    });

    document.addEventListener("keydown", function (event) {
        if (event.key === "Escape" && modal.classList.contains("open")) {
            closeModal();
        }
    });

    if (form) {
        form.addEventListener("submit", function (event) {
            // Read by id rather than off the form: HTMLFormElement.name is the
            // form's own name attribute, not the control called "name".
            var planId = planIdInput ? planIdInput.value.trim() : "";
            var name = (document.getElementById("enquiry-name") || {}).value || "";
            var email = (document.getElementById("enquiry-email") || {}).value || "";
            var phone = (document.getElementById("enquiry-phone") || {}).value || "";

            name = name.trim();
            email = email.trim();
            phone = phone.trim();

            var digits = phone.replace(/\D+/g, "");

            clearError();

            if (!planId) {
                event.preventDefault();
                showError("Please choose the package you want a quote for.");
                return;
            }

            if (name.length < 2) {
                event.preventDefault();
                showError("Please enter your full name.");
                return;
            }

            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                event.preventDefault();
                showError("Please enter a valid email address.");
                return;
            }

            if (digits.length < 7 || digits.length > 15) {
                event.preventDefault();
                showError("Please enter a valid phone number.");
                return;
            }
        });
    }
});
