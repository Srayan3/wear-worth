/* Checkout page behavior: live delivery-charge/total calculation, and
   deterministic (plain-JS, not CSS :has()) selected-card highlighting +
   payment-method instruction panels. */
(function () {
    'use strict';

    // ---- Delivery charge + total recalculation ----
    (function () {
        var subtotalEl = document.getElementById('checkoutSubtotal');
        var deliveryEl = document.getElementById('checkoutDelivery');
        var totalEl = document.getElementById('checkoutTotal');
        var extraNote = document.getElementById('checkoutDeliveryNote');
        var radios = document.querySelectorAll('input[name="delivery_zone_id"]');
        if (!subtotalEl || !radios.length) return;

        var subtotal = parseFloat(subtotalEl.dataset.value || '0');
        var symbol = (subtotalEl.textContent.match(/^\D+/) || ['৳'])[0];

        function format(n) {
            return symbol + n.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
        }

        function recalc() {
            var checked = document.querySelector('input[name="delivery_zone_id"]:checked');
            var charge = checked ? parseFloat(checked.dataset.charge || '0') : 0;
            deliveryEl.textContent = format(charge);
            console.log(charge);
            if(charge > 80){
                extraNote.textContent = "You need to pay the delivery charge in advance if you're ordering from outside Dhaka.";
            } else {
                extraNote.textContent = "—";
            }
            totalEl.textContent = format(subtotal + charge);
        }

        radios.forEach(function (r) { r.addEventListener('change', recalc); });
        recalc();
    })();

    // ---- Selected-card highlighting (delivery zone cards) ----
    (function () {
        var radios = document.querySelectorAll('input[name="delivery_zone_id"]');
        function sync() {
            radios.forEach(function (radio) {
                var card = radio.closest('.delivery-option');
                if (card) card.classList.toggle('is-selected', radio.checked);
            });
        }
        radios.forEach(function (r) { r.addEventListener('change', sync); });
        sync();
    })();

    // ---- Payment method: selected-card highlight + instructions panel ----
    // Cash on Delivery never has a Transaction ID field in the page's HTML
    // at all — the server only ever renders that field for non-COD methods
    // (see views/checkout.php). This block just shows/hides each method's
    // own instructions panel deterministically in plain JS (not a CSS
    // :has() selector, which can behave inconsistently across browsers) —
    // it can never cause COD to display or require a transaction ID.
    (function () {
        var radios = document.querySelectorAll('input[name="payment_method_id"]');
        function sync() {
            radios.forEach(function (radio) {
                var option = radio.closest('.payment-option');
                if (!option) return;
                option.classList.toggle('is-selected', radio.checked);
                var panel = option.querySelector('.payment-option__instructions');
                // Default (no-JS) state is visible — JS just hides the
                // panels for methods that aren't currently selected, so
                // nothing depends on JS running for correctness, only tidiness.
                if (panel) panel.classList.toggle('is-hidden', !radio.checked);
            });
        }
        radios.forEach(function (r) { r.addEventListener('change', sync); });
        sync();
    })();
})();
