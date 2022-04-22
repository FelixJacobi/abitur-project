/**
 * Created by felix on 02.07.17.
 */

Blinkfair.Order = function() {
    "use strict";

    function scrollToTop()
    {
        $('html, body').animate({
            scrollTop: $("#alert-hook").offset().top
        }, 2000);
    }

    function registerSubmitHandler()
    {

        var form = $('#order');

        form.submit(function (e) {
            var espresso = $('input[data-order-type="espresso"]');
            var coffee = $('input[data-order-type="coffee"]');
            var errors = '';
            var ship = $('input[name="order_ship"]:checked');
            var plz = $('input[name="order_plz"]');

            if (!espresso.is(':checked') && !coffee.is(':checked')) {
                errors += '<p>Sie müssen mindestens Kaffee oder Espresso auswählen.</p>';
            }

            var espressoAmount = $('input[name="order_espresso_amount"]').val();
            if (espresso.is(':checked') && (espressoAmount.length === 0 || espressoAmount === '0')) {
                errors += '<p>Sie haben Espresso gewählt, aber keine Stückzahl angegeben.</p>';
            }

            var coffeeAmount = $('input[name="order_coffee_amount"]').val();
            if (coffee.is(':checked') && (coffeeAmount.length === 0 || coffeeAmount === '0')) {
                errors += '<p>Sie haben Kaffee gewählt, aber keine Stückzahl angegeben.</p>';
            }

            if (ship.val() === 'officepost' && $('input[name="order_office_number"]').val().length === 0) {
                errors += '<p>Sie haben den Versand per Behördenpost gewählt, aber keine Behördennummer angegeben.';
            }

            if (plz.val() == 0) {
                // no validation error -> handled by Browser
            } else if (!parseInt(plz.val()) || plz.val().length !== 5) {
                errors += '<p>Die Postleitzahl ist ungültig.</p>';
            } else if (plz.val().match('/22549|22547|22559|22587|22589|22609/g') === null && ship.val() === 'personal') {
                errors += '<p>Sie haben die persönliche Lieferung als Versandmethode ausgewählt. Leider ist die persönliche Lieferung für Ihre Adresse nicht verfügbar, da Ihre Postleitzahl sich außerhalb des Einzugsgebietes befindet. Bitte wählen Sie eine andere Versandmethode.</p>';
            }

            var alert = new Blinkfair.Alert();

            if (errors.length > 0) {
                alert.showAlert(errors, 'danger', 1000000);
                scrollToTop();

                e.preventDefault();
                return false;
            } else {
                $.ajax({
                    beforeSend: function () {
                    },
                    success: function (data) {
                        if (data.type === 'success') {
                            var msg = '';

                            for (var i in data.messages) {
                                msg += '<p>' + data.messages[i] + '</p>';
                            }

                            if (msg.length > 0) {
                                alert.showAlert(msg, 'success', 1000000);
                                scrollToTop();
                            }
                        } else if (data.type === 'error') {
                            var errors = '';

                            for (var i in data.errors) {
                                errors += '<p>' + data.errors[i] + '</p>';
                            }

                            if (errors.length > 0) {
                                alert.showAlert(errors, 'danger', 1000000);
                                scrollToTop();
                            }
                        }
                    },
                    error: function (data) {
                        var errors = 'Unerwarteter Fehler beim Versenden des Formulars.';

                        for (var i in data.errors) {
                            errors += '<p>' + data.errors[i] + '</p>';
                        }

                        alert.showAlert(errors, 'danger', 1000000);
                        scrollToTop();
                    },
                    url: 'order/submit',
                    type: 'POST',
                    data: new FormData(this),
                    processData: false,
                    contentType: false
                });

                e.preventDefault();
                return false;
            }
        });
    }

    function calculatePrice()
    {
        var price = 0;

        if ($('input[name="order_ship"]:checked').val() === 'hermes') {
            price = price + 4.50;
        }

        var espresso = $('input[data-order-type="espresso"]');
        var coffee = $('input[data-order-type="coffee"]');

        if (espresso.is(':checked')) {
            var amount = parseInt($('input[name="order_espresso_amount"]').val());

            if (amount > 0) {
                price = price + amount * 8;
            }
        }

        if (coffee.is(':checked')) {
            var amount = parseInt($('input[name="order_coffee_amount"]').val());

            if (amount > 0) {
                price = price + amount * 7;
            }
        }

        price = (Math.round(price * 100) / 100).toString();
        price += (price.indexOf('.') == -1)? '.00' : '0';
        price = price.replace('.', ',') + '€';

        console.log(price);
        $('#order_price').html(price);
    }

    function initialize() {
        calculatePrice();

        $('input[name="order_products[]"]').change(function () {
            calculatePrice();
        });

        $('input[name="order_coffee_amount"]').change(function () {
            calculatePrice();
        });

        $('input[name="order_espresso_amount"]').change(function () {
            calculatePrice();
        });

        $('input[name="order_ship"]').change(function () {
            calculatePrice();
        });

        registerSubmitHandler();
    }

    // Public API
    return {
        init: function() {
            initialize();
        },
        registerSubmitHandler: function() {
            registerSubmitHandler();
        }
    };

};

$(document).ready(function() {
    var order = new Blinkfair.Order;
    order.init();
});