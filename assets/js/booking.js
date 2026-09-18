function goToStep(stepNumber) {
    var panels = document.querySelectorAll('.booking-form .step-panel');
    var steps = document.querySelectorAll('.stepper .step');

    panels.forEach(function (panel) {
        panel.classList.toggle('active', Number(panel.getAttribute('data-step')) === stepNumber);
    });

    steps.forEach(function (step, index) {
        step.classList.toggle('active', index + 1 === stepNumber);
    });
}

document.addEventListener('DOMContentLoaded', function () {
    var serviceInputs = document.querySelectorAll('input[name="service"]');
    var priceBadge = document.getElementById('selected-price');
    var priceMap = {
        'Artist Management Services': 5000,
        'Audio and Video Services': 12000,
        'Online Classes': 3000,
        'Recording Services': 7000
    };
    var extraSections = {
        'Artist Management Services': document.getElementById('extra-artist-management'),
        'Audio and Video Services': document.getElementById('extra-audio-video'),
        'Online Classes': document.getElementById('extra-online-classes'),
        'Recording Services': document.getElementById('extra-recording')
    };

    function toggleExtraSections() {
        var selected = document.querySelector('input[name="service"]:checked');
        var selectedVal = selected ? selected.value : '';
        Object.keys(extraSections).forEach(function (serviceName) {
            var section = extraSections[serviceName];
            if (section) {
                section.classList.toggle('active', selectedVal === serviceName);
            }
        });

        if (priceBadge) {
            var selectedPrice = priceMap[selectedVal] || 7000;
            priceBadge.textContent = 'Selected service price: ₹' + selectedPrice.toLocaleString();
        }
    }

    serviceInputs.forEach(function (input) {
        input.addEventListener('change', toggleExtraSections);
    });
    toggleExtraSections();
});
