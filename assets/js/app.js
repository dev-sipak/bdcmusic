/*=========================================================
COUNTER ANIMATION
=========================================================*/

const counters = document.querySelectorAll(".counter-grid h2");

if (counters.length) {

    const counterObserver = new IntersectionObserver((entries) => {

        entries.forEach(entry => {

            if (!entry.isIntersecting) return;

            const el = entry.target;

            const end = parseInt(el.textContent.replace(/\D/g, ""), 10);

            const suffix = el.textContent.replace(/[0-9]/g, "");

            let current = 0;

            const duration = 2000;
            const increment = end / (duration / 16);

            function updateCounter() {

                current += increment;

                if (current < end) {

                    el.textContent = Math.floor(current) + suffix;

                    requestAnimationFrame(updateCounter);

                } else {

                    el.textContent = end + suffix;

                }

            }

            updateCounter();

            counterObserver.unobserve(el);

        });

    }, {
        threshold: 0.4
    });

    counters.forEach(counter => counterObserver.observe(counter));

}


/*=========================================================
ARTIST SWIPER
=========================================================*/

document.addEventListener("DOMContentLoaded", function () {

    const menuToggle = document.querySelector(".menu-toggle");
    const nav = document.querySelector(".nav");
    const dropdownItem = document.querySelector(".has-dropdown");
    const dropdownToggle = document.querySelector(".dropdown-toggle");

    if (menuToggle && nav) {

        menuToggle.addEventListener("click", function () {

            const isOpen = nav.classList.toggle("nav-open");
            menuToggle.setAttribute("aria-expanded", isOpen);

            menuToggle.innerHTML = isOpen
                ? '<i class="fa-solid fa-xmark"></i>'
                : '<i class="fa-solid fa-bars"></i>';

        });

        if (dropdownItem && dropdownToggle) {

     // Mobile click dropdown
    dropdownToggle.addEventListener("click", function (event) {

        if (window.innerWidth <= 992) {

            event.preventDefault();
            event.stopPropagation();

            const isOpen = dropdownItem.classList.toggle("dropdown-open");

            dropdownToggle.setAttribute(
                "aria-expanded",
                isOpen ? "true" : "false"
            );
        }

    });


   
    // Close dropdown when clicking outside (mobile only)
    document.addEventListener("click", function (event) {

        if (window.innerWidth <= 992) {

            if (!dropdownItem.contains(event.target)) {

                dropdownItem.classList.remove("dropdown-open");

                dropdownToggle.setAttribute(
                    "aria-expanded",
                    "false"
                );

            }

        }

    });


    // Escape close
    document.addEventListener("keydown", function (event) {

        if (event.key === "Escape") {

            dropdownItem.classList.remove("dropdown-open");

            dropdownToggle.setAttribute(
                "aria-expanded",
                "false"
            );

            nav.classList.remove("nav-open");

            menuToggle.setAttribute(
                "aria-expanded",
                "false"
            );

            menuToggle.innerHTML =
                '<i class="fa-solid fa-bars"></i>';

        }

    });

}

        document.querySelectorAll(".nav a").forEach(function (link) {

            link.addEventListener("click", function () {

                nav.classList.remove("nav-open");
                if (dropdownItem && dropdownToggle) {
                    dropdownItem.classList.remove("dropdown-open");
                    dropdownToggle.setAttribute("aria-expanded", "false");
                }
                menuToggle.setAttribute("aria-expanded", "false");
                menuToggle.innerHTML = '<i class="fa-solid fa-bars"></i>';

            });

        });

    }

    if (typeof Swiper === "undefined") {

        console.error("Swiper JS not loaded.");

        return;

    }

    if (typeof IntersectionObserver !== "undefined") {

        const revealObserver = new IntersectionObserver((entries) => {

            entries.forEach((entry) => {

                if (entry.isIntersecting) {
                    entry.target.classList.add("is-visible");
                    revealObserver.unobserve(entry.target);
                }

            });

        }, {
            threshold: 0.16
        });

        document.querySelectorAll(".reveal").forEach((item) => revealObserver.observe(item));

    } else {

        document.querySelectorAll(".reveal").forEach((item) => item.classList.add("is-visible"));

    }

    new Swiper(".person-slider", {

        slidesPerView: 5,
        spaceBetween: 30,

        loop: true,

        speed: 1200,

        allowTouchMove: false,

        grabCursor: true,

        autoplay: {
            delay: 6000,
            disableOnInteraction: false,
        },

        pagination: {
            el: ".person-slider .swiper-pagination",
            clickable: true,
        },

        breakpoints: {

            320: {
                slidesPerView: 1,
                spaceBetween: 20
            },

            576: {
                slidesPerView: 2,
                spaceBetween: 20
            },

            768: {
                slidesPerView: 2,
                spaceBetween: 25
            },

            992: {
                slidesPerView: 3,
                spaceBetween: 30
            },

            1200: {
                slidesPerView: 5,
                spaceBetween: 30
            }

        }

    });

});


/*=========================================================
HERO CARD SLIDER
=========================================================*/
document.addEventListener("DOMContentLoaded", () => {

    new Swiper(".hero-card-slider", {
        slidesPerView: 1,
        loop: true,
        speed: 1000,
		effect:"fade",
		fadeEffect:{
			crossFade:true
		},
        grabCursor: true,
        allowTouchMove: true,
        observer: true,
        observeParents: true,
        autoplay: {
            delay: 3500,
            disableOnInteraction: false
        }
    });

});

document.addEventListener("DOMContentLoaded", () => {

new Swiper(".testimonial-slider", {
    slidesPerView: 1,
    spaceBetween: 30,
    loop: true,
    grabCursor: true,
    autoHeight: true,

    autoplay: {
        delay: 6000,
        disableOnInteraction: false,
    },

    pagination: {
        el: ".testimonial-slider .swiper-pagination",
        clickable: true,
    },

    breakpoints: {
        0: {
            slidesPerView: 1,
            spaceBetween: 20,
        },
        768: {
            slidesPerView: 1,
            spaceBetween: 24,
        },
        1024: {
            slidesPerView: 1,
            spaceBetween: 30,
        }
    }
});
document.querySelectorAll(".faq-item").forEach(item => {
    item.addEventListener("toggle", () => {

        if (!item.open) {
            item.classList.remove("active");
            return;
        }

        document.querySelectorAll(".faq-item").forEach(faq => {
            if (faq !== item) {
                faq.open = false;
                faq.classList.remove("active");
            }
        });

        item.classList.add("active");

    });
});

});
