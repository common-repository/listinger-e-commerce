<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
/**
 * Template Name: Listinger Checkout
 */

get_header();

// Retrieve and sanitize cart items from session
$cart = isset($_SESSION['listinger_cart']) && is_array($_SESSION['listinger_cart']) ? $_SESSION['listinger_cart'] : array();

// Sanitize and validate cart data
if ( ! empty( $cart ) ) {
    foreach ( $cart as $product_id => $quantity ) {
        $product_id = intval( $product_id ); // Ensure product_id is an integer
        $quantity = intval( $quantity ); // Ensure quantity is an integer

        // Validate that both product_id and quantity are positive integers
        if ( $product_id <= 0 || $quantity <= 0 ) {
            unset( $cart[$product_id] ); // Remove invalid cart items
        }
    }
}
?>

<div class="listinger-checkout">
    <h1><?php esc_html_e('Checkout', 'listinger-ecommerce'); ?></h1>
    <?php if ( ! empty( $cart ) ) : ?>
        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <input type="hidden" name="action" value="listinger_process_order">
            <?php wp_nonce_field( 'listinger_process_order', 'listinger_process_order_nonce' ); ?>
            <table class="listinger-checkout-table" style="border:5px solid;">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'Product', 'listinger-ecommerce' ); ?></th>
                        <th><?php esc_html_e( 'Quantity', 'listinger-ecommerce' ); ?></th>
                        <th><?php esc_html_e( 'Price', 'listinger-ecommerce' ); ?></th>
                        <th><?php esc_html_e( 'Total', 'listinger-ecommerce' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    foreach ( $cart as $product_id => $quantity ) : 
                        $product = get_post( intval( $product_id ) );
                        $price = floatval( get_post_meta( intval( $product_id ), '_listinger_price', true ) );
                        $total = $price * intval( $quantity );

                        if ( ! $product || $price <= 0 ) {
                            continue; // Skip invalid or missing products
                        }
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
            <div class="listinger-form-row">
                <div class="listinger-form-column">
                    <p>
                        <label for="listinger_billing_name"><?php esc_html_e( 'Name:', 'listinger-ecommerce' ); ?></label>
                        <input type="text" id="listinger_billing_name" name="billing_name" required>
                    </p>
                </div>
                <div class="listinger-form-column">
                    <p>
                        <label for="listinger_billing_email"><?php esc_html_e( 'Email:', 'listinger-ecommerce' ); ?></label>
                        <input type="email" id="listinger_billing_email" name="billing_email" required>
                    </p>
                </div>
            </div>
            <div class="listinger-form-row">
                <div class="listinger-form-column-full">
                    <p>
                        <label for="listinger_billing_address"><?php esc_html_e( 'Address:', 'listinger-ecommerce' ); ?></label>
                        <textarea id="listinger_billing_address" name="billing_address" required></textarea>
                    </p>
                </div>
            </div>
            <p>
                <input type="submit" value="<?php esc_html_e( 'Place Order', 'listinger-ecommerce' ); ?>" class="listinger-button">
            </p>
        </form>
    <?php else : ?>
        <p><?php esc_html_e( 'Your cart is empty.', 'listinger-ecommerce' ); ?></p>
    <?php endif; ?>
</div>

<?php
get_footer();
?>

<?php
// Enqueue styles and scripts specifically for this template
function listinger_enqueue_checkout_styles() {
    wp_register_style( 'listinger-checkout-styles', plugins_url( '/assets/css/checkout.css', __FILE__ ) );
    wp_enqueue_style( 'listinger-checkout-styles' );
}
add_action( 'wp_enqueue_scripts', 'listinger_enqueue_checkout_styles' );
?>
