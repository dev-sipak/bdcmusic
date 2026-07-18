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

    if (typeof Swiper === "undefined") {

        console.error("Swiper JS not loaded.");

        return;

    }

    new Swiper(".artist-slider", {

        slidesPerView: 4,
        spaceBetween: 30,

        loop: true,

        speed: 1200,

        grabCursor: true,

        autoplay: {
            delay: 6000,
            disableOnInteraction: false,
        },

        pagination: {
            el: ".artist-slider .swiper-pagination",
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
                slidesPerView: 4,
                spaceBetween: 30
            }

        }

    });

});

/*=========================================================
HERO SLIDER
=========================================================*/

const heroSlider = new Swiper(".hero-slider",{

    slidesPerView:1,

    loop:true,

    speed:1200,

    effect:"fade",

    fadeEffect:{
        crossFade:true
    },

    autoplay:{
        delay:5000,
        disableOnInteraction:false,
    },

    pagination:{
        el:".hero-slider .swiper-pagination",
        clickable:true,
    },

    allowTouchMove:true,

});