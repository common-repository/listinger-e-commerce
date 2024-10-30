jQuery(document).ready(function($) {
    console.log('Listinger E-commerce Plugin loaded.');

    // Handle "Add to Cart" button click
    $('.listinger-add-to-cart').on('click', function() {
        var productId = $(this).data('product-id');

        $.ajax({
            url: listinger_ajax.ajax_url,
            method: 'POST',
            data: {
                action: 'listinger_add_to_cart',
                product_id: productId,
                nonce: listinger_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                } else {
                    alert(response.data.message);
                }
            }
        });
    });

    // Handle "Checkout" button click
    $('.listinger-checkout-button').on('click', function() {
        window.location.href = listinger_ajax.checkout_url;
    });
});
