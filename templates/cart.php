<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! session_id() ) {
    session_start();
}

get_header();
?>

<div class="listinger-cart">
    <h1><?php esc_html_e('Your Cart', 'listinger-ecommerce'); ?></h1>
    <?php if ( isset( $_SESSION['listinger_cart'] ) && ! empty( $_SESSION['listinger_cart'] ) ) : ?>
        <table class="listinger-cart-table">
            <thead>
                <tr>
                    <th><?php esc_html_e('Product', 'listinger-ecommerce'); ?></th>
                    <th><?php esc_html_e('Quantity', 'listinger-ecommerce'); ?></th>
                    <th><?php esc_html_e('Price', 'listinger-ecommerce'); ?></th>
                    <th><?php esc_html_e('Total', 'listinger-ecommerce'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php 
                foreach ( $_SESSION['listinger_cart'] as $product_id => $quantity ) :
                    $product_id = intval( $product_id );
                    $quantity = intval( $quantity );
                    
                    // Validate the product ID and quantity
                    if ( $product_id <= 0 || $quantity <= 0 ) {
                        continue;
                    }

                    $product = get_post( $product_id );
                    if ( ! $product ) {
                        continue;
                    }

                    $price = get_post_meta( $product_id, '_listinger_price', true );
                    $price = floatval( $price ); // Ensure price is a valid float
                    $total = $price * $quantity;
                ?>
                    <tr>
                        <td><?php echo esc_html( $product->post_title ); ?></td>
                        <td><?php echo esc_html( $quantity ); ?></td>
                        <td><?php echo esc_html( number_format( $price, 2 ) ); ?></td>
                        <td><?php echo esc_html( number_format( $total, 2 ) ); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <a href="<?php echo esc_url( home_url( '/checkout' ) ); ?>" class="listinger-button"><?php esc_html_e('Proceed to Checkout', 'listinger-ecommerce'); ?></a>
    <?php else : ?>
        <p><?php esc_html_e('Your cart is empty.', 'listinger-ecommerce'); ?></p>
    <?php endif; ?>
</div>

<?php
get_footer();
?>
