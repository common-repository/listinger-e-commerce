jQuery(document).ready(function($) {
    console.log('Listinger E-commerce Plugin loaded.');

    // Initialize Slick Slider for related products
    if ($('.listinger-related-products-slider').length) {
        $('.listinger-related-products-slider').slick({
            dots: true,
            infinite: true,
            speed: 300,
            slidesToShow: 3,
            slidesToScroll: 3,
            responsive: [
                {
                    breakpoint: 1024,
                    settings: {
                        slidesToShow: 2,
                        slidesToScroll: 2,
                        infinite: true,
                        dots: true
                    }
                },
                {
                    breakpoint: 600,
                    settings: {
                        slidesToShow: 1,
                        slidesToScroll: 1
                    }
                }
            ]
        });
    }

    // Handle "Get Best Quote" button click
    $('.listinger-get-quote').on('click', function() {
        $('#listinger-contact-form').show();
    });

    // Handle contact form submission
    $('#listinger-contact-form').on('submit', function(e) {
        e.preventDefault();

        var formData = {
            action: 'listinger_contact_form',
            nonce: listinger_ajax.nonce,
            name: $('#listinger_contact_name').val(),
            mobile: $('#listinger_contact_mobile').val(),
            message: $('#listinger_contact_message').val()
        };

        $.post(listinger_ajax.ajax_url, formData, function(response) {
            if (response.success) {
                alert(response.data.message);
                $('#listinger-contact-form').hide();
            } else {
                alert('There was an error submitting the form.');
            }
        });
    });

    // Handle "Add to Wishlist" button click
    $('.listinger-add-to-wishlist').on('click', function() {
        var productId = $(this).data('product-id');

        var formData = {
            action: 'listinger_add_to_wishlist',
            nonce: listinger_ajax.nonce,
            product_id: productId
        };

        $.post(listinger_ajax.ajax_url, formData, function(response) {
            if (response.success) {
                alert(response.data.message);
            } else {
                alert(response.data.message);
            }
        });
    });

    // Handle "Add to Cart" button click
    $('.listinger-add-to-cart').on('click', function() {
        var productId = $(this).data('product-id');
        var formData = {
            action: 'listinger_add_to_cart',
            nonce: listinger_ajax.nonce,
            product_id: productId,
            quantity: 1 // default quantity to 1 for simplicity
        };

        $.post(listinger_ajax.ajax_url, formData, function(response) {
            if (response.success) {
                alert(response.data.message);
                location.reload(); // Reload the page to reflect changes
            } else {
                alert('There was an error adding the product to the cart.');
            }
        });
    });

    // Handle social media share buttons
    $('.listinger-share').on('click', function(e) {
        e.preventDefault();
        var url = $(this).attr('href');
        window.open(url, '_blank', 'width=600,height=400');
    });

    // Handle WhatsApp button click
    $('.listinger-whatsapp').on('click', function() {
        var productName = $(this).data('product-name');
        var whatsappUrl = 'https://wa.me/?text=' + encodeURIComponent(productName);
        window.open(whatsappUrl, '_blank');
    });
});
