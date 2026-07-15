(function () {
    "use strict";

    function getMoneyFormat() {
        return window.cartPriceFormat || {
            symbol: "",
            position: "left",
            decimals: 2,
            decimalSeparator: ".",
            thousandsSeparator: ","
        };
    }

    function formatPrice(num) {
        var value = parseFloat(num || 0);

        if (isNaN(value)) {
            value = 0;
        }

        var format = getMoneyFormat();
        var decimals = parseInt(format.decimals, 10);

        if (isNaN(decimals) || decimals < 0) {
            decimals = 2;
        }

        var fixed = value.toFixed(decimals);
        var parts = fixed.split(".");
        var thousandsSeparator = format.thousandsSeparator || ",";
        var decimalSeparator = format.decimalSeparator || ".";
        var integerPart = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, thousandsSeparator);
        var decimalPart = parts[1] ? decimalSeparator + parts[1] : "";
        var formatted = integerPart + decimalPart;

        switch (format.position) {
            case "left_with_space":
                return (format.symbol || "") + " " + formatted;
            case "right":
                return formatted + (format.symbol || "");
            case "right_with_space":
                return formatted + " " + (format.symbol || "");
            default:
                return (format.symbol || "") + formatted;
        }
    }

    function readAmount($el) {
        if (!$el || !$el.length) {
            return 0;
        }

        var amount = parseFloat($el.data("amount"));

        if (!isNaN(amount)) {
            return amount;
        }

        amount = parseFloat(String($el.text() || "").replace(/[^0-9.\-]/g, ""));
        return isNaN(amount) ? 0 : amount;
    }

    function setAmount($el, amount) {
        if (!$el || !$el.length) {
            return;
        }

        $el.data("amount", amount);
        $el.text(formatPrice(amount));
    }

    function calculateModuleExtraPrice($module) {
        var priceType = String($module.data("price-type") || "none");
        var baseAmount = parseFloat($module.data("price-amount") || 0);

        if (isNaN(baseAmount)) {
            baseAmount = 0;
        }

        if (priceType === "additive") {
            var total = 0;

            $module.find(".checkout-extra-service:checked").each(function () {
                var price = parseFloat($(this).data("price") || 0);
                if (!isNaN(price)) {
                    total += price;
                }
            });

            return total;
        }

        if (priceType === "per_person") {
            var adults = parseInt($module.find('input[name$="[adults]"]').val(), 10) || 0;
            return adults * baseAmount;
        }

        if (priceType === "per_day") {
            var checkIn = $module.find(".checkout-date-input").first().val();
            var checkOut = $module.find(".checkout-date-input").last().val();

            if (checkIn && checkOut) {
                var inDate = new Date(checkIn);
                var outDate = new Date(checkOut);
                var days = Math.max(0, Math.ceil((outDate - inDate) / (1000 * 60 * 60 * 24)));
                return days * baseAmount;
            }

            return 0;
        }

        if (priceType === "per_hour") {
            return $module.find(".checkout-time-slot:checked").length > 0 ? baseAmount : 0;
        }

        return 0;
    }

  function calculateCheckoutExtras() {
    // ✅ FIX: agar is page par module cards hi nahi hain (jaise checkout/payment page),
    // to server ke already-correct rendered totals ko overwrite mat karo.
    if ($(".checkout-module-card").length === 0) {
        return;
    }

    var totalExtras = 0;

    $(".checkout-module-card").each(function () {
        totalExtras += calculateModuleExtraPrice($(this));
    });

    setAmount($(".js-cart-extras"), totalExtras);

    var subtotal = readAmount($(".js-cart-subtotal"));
    var discount = readAmount($(".js-cart-discount"));
    var tax = readAmount($(".js-cart-tax"));
    var delivery = readAmount($(".js-cart-delivery_fee"));
    var total = subtotal - discount + tax + delivery + totalExtras;

    if (total < 0) {
        total = 0;
    }

    setAmount($(".js-cart-total"), total);
}

    $(document).ready(function () {
        if (typeof hasErrors !== "undefined" && hasErrors === "true") {
            showToast("error", oopsLang, hasErrorsHintLang);
        }
    });

    $("body").on("click", ".js-cart-checkout", function (e) {
        e.preventDefault();

        const $this = $(this);
        const $form = $this.closest("form");

        $this.addClass("loadingbar").prop("disabled", true);

        $form.trigger("submit");
    });

    $("body").on("click", ".js-cart-payment-btn", function (e) {
        e.preventDefault();

        const $this = $(this);
        const $form = $this.closest("form");
        const $selectedChannel = $form.find('input[name="gateway"]:checked');

        if ($selectedChannel.length) {
            $this.addClass("loadingbar").prop("disabled", true);

            showToast("success", pleaseWaitLang, transferringToLang);

            const channelName = $selectedChannel.attr("data-class");

            if (channelName === "Razorpay") {
                $(".razorpay-payment-button").trigger("click");
            } else {
                $form.trigger("submit");
            }
        } else {
            showToast("error", "", selectPaymentGatewayLang);
        }
    });

    $("body").on("change", 'input[name="gateway"]', function () {
        const id = $(this).attr("id");
        const $btnTextEl = $(".js-pay-now-text");

        if ($btnTextEl && $btnTextEl.length) {
            $btnTextEl.text(id === "gateway_offline" ? "Submit offline payment" : "Pay Now!");
        }
    });

    $("body").on("click", ".js-validate-coupon-btn", function (e) {
        e.preventDefault();

        const $this = $(this);
        const $parent = $this.parent();
        const coupon = $parent.find('input[name="coupon"]').val();
        const path = "/cart/coupon/validate";

        if (coupon) {
            const $cartSummaryCard = $(".js-cart-summary-container");
            $this.addClass("loadingbar").prop("disabled", true);

            $.post(path, { coupon: coupon }, function (result) {
                $this.removeClass("loadingbar").prop("disabled", false);

                if (result.code === 200) {
                    $cartSummaryCard.html(result.html);
                    $this.addClass("d-none");
                    $parent.find(".js-remove-coupon-btn").removeClass("d-none");
                    calculateCheckoutExtras();
                }
            }).fail(err => {
                $this.removeClass("loadingbar").prop("disabled", false);
                const errors = err.responseJSON;

                if (errors.error) {
                    showToast("error", errors.error.title, errors.error.msg);
                }
            });
        } else {
            showToast("error", couponLang, enterCouponLang);
        }
    });

    $("body").on("click", ".js-remove-coupon-btn", function (e) {
        e.preventDefault();

        var html = '<div class="px-16 pb-24 pt-16">\n' +
            '    <p class="text-center">' + removeCouponHintLang + '</p>\n' +
            '    <div class="mt-24 d-flex align-items-center justify-content-center">\n' +
            '        <a href="/cart" class="btn btn-sm btn-primary">' + removeLang + '</a>\n' +
            '        <button type="button" class="btn btn-sm btn-danger ml-12 close-swl">' + cancelLang + '</button>\n' +
            '    </div>\n' +
            '</div>';

        Swal.fire({
            title: removeCouponTitleLang,
            html: html,
            icon: "warning",
            showConfirmButton: false,
            showCancelButton: false,
            allowOutsideClick: () => !Swal.isLoading(),
        });
    });

    $("body").on("change", ".checkout-extra-service, .checkout-date-input, .checkout-stepper-input, .checkout-time-slot, .checkout-staff-select", function () {
        calculateCheckoutExtras();
    });

    $(document).on("checkout:priceUpdate", function () {
        calculateCheckoutExtras();
    });

    $(document).ready(function () {
        if (typeof hasErrors !== "undefined" && hasErrors === "true") {
            showToast("error", oopsLang, hasErrorsHintLang);
        }

        calculateCheckoutExtras();
    });
})(jQuery);
