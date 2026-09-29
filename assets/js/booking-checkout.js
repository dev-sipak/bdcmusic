/**
 * Booking checkout behaviour.
 *
 * Three jobs, all progressive enhancements over markup that already works
 * without JavaScript:
 *
 *   1. Reveal conditional fields when the controlling answer unlocks them.
 *   2. Show only the package group the customer has picked.
 *   3. Hand the payment step off to Razorpay without a page reload.
 *
 * The server decides what is required and what a booking costs. Everything here
 * only decides what is *visible*, so a customer with JavaScript disabled submits
 * the same booking as one without it.
 */
(function () {
    'use strict';

    var HIDDEN = 'd-none';

    // ─── Conditional fields ─────────────────────────────────────
    //
    // A field is hidden or shown based on the value of another control. A
    // checkbox parent is a set, so it unlocks when any checked value matches; a
    // radio or select parent holds a single value.

    function parentValues(form, name) {
        var nodes = form.querySelectorAll('[name="' + name + '"]');
        var values = [];

        for (var i = 0; i < nodes.length; i++) {
            var node = nodes[i];
            if (!node.checked) {
                continue;
            }
            // A checkbox without a value attribute is its own value, matching
            // how the validator reads it.
            values.push(node.value === '' || node.value === 'on' ? '1' : node.value);
        }

        return values;
    }

    function isUnlocked(form, field) {
        var wanted = (field.getAttribute('data-booking-equals') || '').split('|');
        var values = parentValues(form, field.getAttribute('data-booking-depends'));

        for (var i = 0; i < values.length; i++) {
            if (wanted.indexOf(values[i]) !== -1) {
                return true;
            }
        }

        return false;
    }

    /**
     * Set a control back to the definition's required state when a field is
     * revealed, and strip it while the field is hidden. The server renders
     * `required` only on fields that start visible, so this is what keeps the
     * browser from objecting to a question the customer cannot even see.
     */
    function syncRequired(field, visible) {
        var controls = field.querySelectorAll('input, select, textarea');

        // The renderer puts the flag on the field wrapper rather than on each
        // control, so a field declared required in the registry carries it once
        // no matter how many controls it renders.
        var requiredByDefinition = field.getAttribute('data-booking-required') === '1';

        for (var i = 0; i < controls.length; i++) {
            var control = controls[i];

            // Never touch file inputs: setting `required` on one is a browser
            // special case, and clearing it would not undo a pre-existing
            // requirement. The server validates uploads regardless.
            if (control.type === 'file') {
                continue;
            }

            if (visible) {
                if (requiredByDefinition || control.getAttribute('data-booking-required') === '1') {
                    control.required = true;
                }
            } else {
                if (control.required) {
                    control.setAttribute('data-booking-required', '1');
                }
                control.required = false;
            }
        }
    }

    function applyConditionals(form) {
        var fields = form.querySelectorAll('[data-booking-depends]');

        for (var i = 0; i < fields.length; i++) {
            var field = fields[i];
            var visible = isUnlocked(form, field);

            field.classList.toggle(HIDDEN, !visible);
            syncRequired(field, visible);
        }
    }

    function watchConditionals(form) {
        applyConditionals(form);

        // Re-check on any change: the parent may be a different control type to
        // the one the field depends on, and a select or a group of checkboxes
        // can change without a blur.
        form.addEventListener('change', function () {
            applyConditionals(form);
        });
    }

    // ─── Package groups ─────────────────────────────────────────
    //
    // Classes offers several groups of plans. Only the selected group is shown,
    // so the customer is not comparing a Vocal course against a Guitar course.

    function showGroup(groupKey) {
        var panels = document.querySelectorAll('[data-booking-group]');

        for (var i = 0; i < panels.length; i++) {
            panels[i].classList.toggle(HIDDEN, panels[i].getAttribute('data-booking-group') !== groupKey);
        }
    }

    // The selector and the panels are siblings rather than nested, so this works
    // across the whole page. Only one package step is ever rendered, so there is
    // never a second set of panels to collide with.
    function initGroups() {
        var inputs = document.querySelectorAll('[data-booking-group-input]');

        if (!inputs.length) {
            return;
        }

        var apply = function () {
            for (var i = 0; i < inputs.length; i++) {
                if (inputs[i].checked) {
                    showGroup(inputs[i].value);
                    return;
                }
            }

            // Nothing selected yet: fall back to the first group so the step is
            // never shown empty.
            showGroup(inputs[0].value);
        };

        apply();

        for (var i = 0; i < inputs.length; i++) {
            inputs[i].addEventListener('change', apply);
        }
    }

    // ─── Payment ────────────────────────────────────────────────

    function setStatus(statusEl, message, isError) {
        if (!statusEl) {
            return;
        }

        statusEl.textContent = message;
        statusEl.classList.toggle('is-error', Boolean(isError));
    }

    function postForm(url, data) {
        return fetch(url, {
            method: 'POST',
            body: data,
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).then(function (response) {
            return response.json().catch(function () {
                throw new Error('The server sent an unexpected response. Please try again.');
            });
        });
    }

    function initPayment(form) {
        var button = form.querySelector('[data-booking-pay-button]');
        var statusEl = document.querySelector('[data-booking-payment-status]');

        if (!button) {
            return;
        }

        var createUrl = form.getAttribute('data-booking-create-url');
        var verifyUrl = form.getAttribute('data-booking-verify-url');
        var thanksUrl = form.getAttribute('data-booking-thanks-url');
        var busy = false;

        form.addEventListener('submit', function (event) {
            // The first post creates the booking and the Razorpay order. Anything
            // the server rejects is shown here instead of being lost by a reload.
            if (busy) {
                return;
            }

            event.preventDefault();
            busy = true;
            button.disabled = true;
            setStatus(statusEl, 'Preparing your order…', false);

            var body = new FormData();
            var fields = new FormData(form);

            fields.forEach(function (value, key) {
                body.append(key, value);
            });

            // The payment step always ends in a call to the verify endpoint. In
            // demo mode the two are the same call, because no gateway is involved.
            function confirmPayment(bookingId, paymentId, orderId, signature) {
                var confirm = new FormData();
                confirm.append('booking_id', bookingId);
                confirm.append('razorpay_payment_id', paymentId);
                confirm.append('razorpay_order_id', orderId);
                confirm.append('razorpay_signature', signature);
                confirm.append('csrf_token', result.csrf_token);

                setStatus(statusEl, 'Confirming your payment…', false);

                return postForm(verifyUrl, confirm).then(function (verified) {
                    if (!verified || verified.success !== true) {
                        throw new Error((verified && verified.message) || 'We could not confirm the payment. Our team will email you shortly.');
                    }

                    window.location.href = thanksUrl + '?order=' + encodeURIComponent(bookingId);
                });
            }

            function giveUp(error) {
                busy = false;
                button.disabled = false;
                setStatus(statusEl, error.message, true);
            }

            postForm(createUrl, body).then(function (result) {
                if (!result || result.success !== true) {
                    throw new Error((result && result.message) || 'We could not start the payment. Please try again.');
                }

                // No gateway is configured, so there is no checkout to open. Go
                // straight to the server that decides whether the booking is paid.
                if (result.demo === true) {
                    setStatus(statusEl, 'Completing your booking…', false);

                    confirmPayment(
                        result.booking_id,
                        'pay_demo_' + result.booking_id,
                        result.order_id,
                        'sig_demo'
                    ).catch(giveUp);

                    return;
                }

                if (typeof window.Razorpay === 'undefined') {
                    throw new Error('The secure payment window could not load. Please check your connection and try again, or contact us to book directly.');
                }

                setStatus(statusEl, 'Opening secure checkout…', false);

                var options = {
                    key: result.key_id,
                    amount: result.amount,
                    currency: result.currency || 'INR',
                    name: result.name,
                    description: result.description,
                    order_id: result.order_id,
                    prefill: result.prefill || {},
                    notes: result.notes || {},
                    theme: { color: '#ff6b35' },
                    modal: {
                        ondismiss: function () {
                            busy = false;
                            button.disabled = false;
                            setStatus(statusEl, 'Payment was closed. Your booking is saved and you can pay again.', false);
                        }
                    },
                    handler: function (response) {
                        confirmPayment(
                            result.booking_id,
                            response.razorpay_payment_id,
                            response.razorpay_order_id,
                            response.razorpay_signature
                        ).catch(giveUp);
                    }
                };

                new window.Razorpay(options).open();
            }).catch(giveUp);
        });
    }

    // ─── Start ──────────────────────────────────────────────────

    function start() {
        var forms = document.querySelectorAll('form');

        for (var i = 0; i < forms.length; i++) {
            watchConditionals(forms[i]);
        }

        initGroups();

        var payment = document.querySelector('[data-booking-payment]');

        if (payment) {
            initPayment(payment);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
}());
