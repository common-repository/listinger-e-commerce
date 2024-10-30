jQuery(document).ready(function($) {
    $('.listinger-contact-supplier').on('click', function() {
        var productName = $(this).data('product-name');
        var formHtml = `
            <div class="listinger-contact-supplier-form">
                <h2>Contact Supplier</h2>
                <p>Product/Service Name: ${productName}</p>
                <p>
                    <label for="listinger_supplier_mobile">Enter Mobile Number:</label>
                    <input type="text" id="listinger_supplier_mobile" name="mobile_number">
                </p>
                <p>
                    <label for="listinger_supplier_message">Message to Supplier:</label>
                    <textarea id="listinger_supplier_message" name="message" rows="4" cols="50"></textarea>
                </p>
                <p>
                    <button id="listinger_submit_contact_form">Submit</button>
                </p>
            </div>
        `;
        $('body').append('<div class="listinger-overlay"></div>');
        $('body').append(formHtml);

        $('#listinger_submit_contact_form').on('click', function() {
            var mobileNumber = $('#listinger_supplier_mobile').val();
            var message = $('#listinger_supplier_message').val();

            // Mobile number validation
            var mobileNumberPattern = /^\d{10}$/;
            if (!mobileNumberPattern.test(mobileNumber)) {
                alert('Please enter a valid 10-digit mobile number.');
                return;
            }

            $.ajax({
                url: listinger_ajax.ajax_url,
                method: 'POST',
                data: {
                    action: 'listinger_contact_supplier',
                    product_name: productName,
                    mobile_number: mobileNumber,
                    message: message,
                },
                success: function(response) {
                    alert(response.data.message);
                    $('.listinger-contact-supplier-form, .listinger-overlay').remove();
                },
            });
        });
    });

    $(document).on('click', '.listinger-overlay', function() {
        $('.listinger-contact-supplier-form, .listinger-overlay').remove();
    });
});
