document.addEventListener("DOMContentLoaded", function () {
    var form = document.querySelector(".classes-enquiry-form");

    if (!form) {
        return;
    }

    var courseSelect = form.querySelector("[data-selected-course]");
    var planSelect = form.querySelector("[data-selected-plan]");
    var courseCol = form.querySelector("[data-course-col]");
    var packageCol = form.querySelector("[data-package-col]");
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
                if (courseCol) {
                    courseCol.classList.remove("col-6");
                    courseCol.classList.add("col-12");
                }
                if (packageCol) {
                    packageCol.classList.add("d-none");
                }
                return;
            }

            coursePlans[course].forEach(function (plan) {
                var option = document.createElement("option");
                option.value = plan;
                option.textContent = plan;
                planSelect.appendChild(option);
            });

            planSelect.disabled = false;

            if (courseCol) {
                courseCol.classList.remove("col-12");
                courseCol.classList.add("col-6");
            }
            if (packageCol) {
                packageCol.classList.remove("d-none");
            }
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
