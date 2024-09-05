// Front Scripts

(function ($, window, document, pluginObject) {

    $(document).on('click', '.kazi-add-cart', function () {

        var thisButton = $(this),
            productID = thisButton.data('product-id');

        $.ajax({
            type: 'post',
            url: pluginObject.ajaxurl,
            data: {
                'action': 'add_to_cart',
                'product_id': productID,
            },
            success: function (response) {
                if (response.success) {
                    thisButton.html('Cart Added')
                } else {
                    console.error(response.data.message);
                }
            }
        });
    });

    $(document).on('click', '.kazi-remove-cart', function () {
        var thisButton = $(this),
            productID = thisButton.data('product-id');

        $.ajax({
            type: 'post',
            url: pluginObject.ajaxurl,
            data: {
                'action': 'remove_cart',
                'product_id': productID,
            },
            success: function (response) {
                if (response.success) {
                    thisButton.parents('.kazi-cart-wrap').css('display', 'none');
                } else {
                    console.error(response.data.message);
                }
            }
        });
    });

    $(document).on('click', '#kazi-increment-button', function () {
        var thisButton = $(this);
        let productIncrementID = thisButton.data('product-increment-id');

        $.ajax({
            type: 'post',
            url: pluginObject.ajaxurl,
            data: {
                'action': 'increase_item',
                'product_increment_id': productIncrementID,
            },
            success: function (response) {
                if (response.success) {
                    window.location.reload();
                } else {
                    console.error(response.data.message);
                }
            }
        });
    });

    $(document).on('click', '#kazi-decrement-button', function () {
        var thisButton = $(this);
        let productDecrementID = thisButton.data('product-decrement-id');

        $.ajax({
            type: 'post',
            url: pluginObject.ajaxurl,
            data: {
                'action': 'decrease_item',
                'product_decrement_id': productDecrementID,
            },
            success: function (response) {
                if (response.success) {
                    window.location.reload();
                } else {
                    console.error(response.data.message);
                }
            }
        });
    });

    $(document).on('change', "#kazi-quantity-input", function () {

        var thisField = jQuery(this),
            inputValue = thisField.val(),
            inputProductID = thisField.data('input-product-id');

        $.ajax({
            type: 'post',
            url: pluginObject.ajaxurl,
            data: {
                'action': 'input_quantity',
                'input_value': inputValue,
                'product_id': inputProductID,
            },
            success: function (response) {
                if (response.success) {
                    window.location.reload();
                } else {
                    console.error(response.data.message);
                }
            }
        });
    });

})(jQuery, window, document, kazi_cart);
