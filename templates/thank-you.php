<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Template for thank you page.
get_header();
?>

<div class="listinger-thank-you">
    <h1><?php esc_html_e( 'Thank You!', 'listinger-ecommerce' ); ?></h1>
    <p><?php esc_html_e( 'Your order has been placed successfully.', 'listinger-ecommerce' ); ?></p>
</div>

<?php
get_footer();
?>
