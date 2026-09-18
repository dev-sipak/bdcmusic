document.addEventListener("DOMContentLoaded", function () {
    var form = document.querySelector(".classes-enquiry-form");

    if (!form) {
        return;
    }

    var courseSelect = form.querySelector("[data-selected-course]");
    var planSelect = form.querySelector("[data-selected-plan]");
    var modeInput = form.querySelector("[data-course-mode]");
    var modeRadios = form.querySelectorAll('input[name="class_mode_selection"]');

    var coursePlans = {
        "Singing": ["Basic", "Standard", "Premium", "Enterprise"],
        "Music Production": ["Basic", "Standard", "Premium", "Enterprise"],
        "Instrument": ["Basic", "Standard", "Premium", "Enterprise"],
        "Video Editing": ["Basic", "Standard", "Premium", "Enterprise"]
    };

    if (courseSelect && planSelect) {
        courseSelect.addEventListener("change", function () {
            var course = this.value;

            planSelect.innerHTML = '<option value="" selected disabled>Select a package</option>';
            planSelect.disabled = true;

            if (!course || !coursePlans[course]) {
                return;
            }

            coursePlans[course].forEach(function (plan) {
                var option = document.createElement("option");
                option.value = plan;
                option.textContent = plan;
                planSelect.appendChild(option);
            });

            planSelect.disabled = false;
        });
    }

    modeRadios.forEach(function (radio) {
        radio.addEventListener("change", function () {
            if (radio.checked && modeInput) {
                modeInput.value = radio.value;
            }
        });
    });
});
