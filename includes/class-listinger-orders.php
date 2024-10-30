<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Listinger_Orders {
    public function __construct() {
        add_action('init', array($this, 'register_order_post_type'));
        add_action('wp_ajax_listinger_add_to_cart', array($this, 'add_to_cart'));
        add_action('wp_ajax_nopriv_listinger_add_to_cart', array($this, 'add_to_cart'));
        add_action('admin_post_listinger_process_order', array($this, 'process_order'));
        add_action('admin_post_nopriv_listinger_process_order', array($this, 'process_order'));
        add_shortcode('listinger_add_to_cart', array($this, 'add_to_cart_shortcode'));
        add_shortcode('listinger_checkout', array($this, 'checkout_shortcode'));
        add_action('add_meta_boxes', array($this, 'add_order_meta_boxes'));
    }

    public function register_order_post_type() {
        $labels = array(
            'name'               => esc_html_x('Orders', 'post type general name', 'listinger-ecommerce'),
            'singular_name'      => esc_html_x('Order', 'post type singular name', 'listinger-ecommerce'),
            'menu_name'          => esc_html__('Orders', 'listinger-ecommerce'),
            'name_admin_bar'     => esc_html_x('Order', 'add new on admin bar', 'listinger-ecommerce'),
            'add_new'            => esc_html_x('Add New', 'order', 'listinger-ecommerce'),
            'add_new_item'       => esc_html__('Add New Order', 'listinger-ecommerce'),
            'new_item'           => esc_html__('New Order', 'listinger-ecommerce'),
            'edit_item'          => esc_html__('Edit Order', 'listinger-ecommerce'),
            'view_item'          => esc_html__('View Order', 'listinger-ecommerce'),
            'all_items'          => esc_html__('All Orders', 'listinger-ecommerce'),
            'search_items'       => esc_html__('Search Orders', 'listinger-ecommerce'),
            'parent_item_colon'  => esc_html__('Parent Orders:', 'listinger-ecommerce'),
            'not_found'          => esc_html__('No orders found.', 'listinger-ecommerce'),
            'not_found_in_trash' => esc_html__('No orders found in Trash.', 'listinger-ecommerce'),
        );

        $args = array(
            'labels'             => $labels,
            'public'             => false,
            'publicly_queryable' => false,
            'show_ui'            => true,
            'show_in_menu'       => 'edit.php?post_type=listinger_product',
            'query_var'          => true,
            'rewrite'            => array('slug' => 'listinger_order'),
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => array('title'),
        );

        register_post_type('listinger_order', $args);
    }

    public function add_to_cart() {
        check_ajax_referer('listinger_ajax_nonce', 'nonce');

        $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
        if (!$product_id) {
            wp_send_json_error(array('message' => esc_html__('Invalid product ID.', 'listinger-ecommerce')));
        }

        $cart = isset($_COOKIE['listinger_cart']) ? json_decode(stripslashes($_COOKIE['listinger_cart']), true) : array();

        if (isset($cart[$product_id])) {
            $cart[$product_id]++;
        } else {
            $cart[$product_id] = 1;
        }

        setcookie('listinger_cart', wp_json_encode($cart), time() + 3600, COOKIEPATH, COOKIE_DOMAIN, false);

        wp_send_json_success(array('message' => esc_html__('Product added to cart.', 'listinger-ecommerce')));
    }

    public function process_order() {
        if (!isset($_POST['listinger_process_order_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['listinger_process_order_nonce'])), 'listinger_process_order')) {
            wp_die(esc_html__('Security check failed', 'listinger-ecommerce'));
        }

        $billing_name = isset($_POST['billing_name']) ? sanitize_text_field($_POST['billing_name']) : '';
        $billing_email = isset($_POST['billing_email']) ? sanitize_email($_POST['billing_email']) : '';
        $billing_address = isset($_POST['billing_address']) ? sanitize_textarea_field($_POST['billing_address']) : '';
        $billing_mobile = isset($_POST['billing_mobile']) ? sanitize_text_field($_POST['billing_mobile']) : '';

        if (!$billing_name || !$billing_email || !$billing_address || !$billing_mobile) {
            wp_die(esc_html__('Missing required fields', 'listinger-ecommerce'));
        }

        $cart = isset($_COOKIE['listinger_cart']) ? json_decode(stripslashes($_COOKIE['listinger_cart']), true) : array();

        $order_data = array(
            'post_title'    => 'Order - ' . current_time('mysql'),
            'post_content'  => '',
            'post_status'   => 'publish',
            'post_type'     => 'listinger_order',
        );

        $order_id = wp_insert_post($order_data);

        if ($order_id) {
            update_post_meta($order_id, '_listinger_billing_name', $billing_name);
            update_post_meta($order_id, '_listinger_billing_email', $billing_email);
            update_post_meta($order_id, '_listinger_billing_address', $billing_address);
            update_post_meta($order_id, '_listinger_billing_mobile', $billing_mobile);
            update_post_meta($order_id, '_listinger_order_items', $cart);

            // Clear the cart
            setcookie('listinger_cart', '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN, false);

            wp_redirect(home_url('/thank-you'));
            exit;
        } else {
            wp_die(esc_html__('Failed to process order', 'listinger-ecommerce'));
        }
    }

    public function add_to_cart_shortcode($atts) {
        $atts = shortcode_atts(array(
            'product_id' => 0,
        ), $atts, 'listinger_add_to_cart');

        if ($atts['product_id']) {
            return '<button class="listinger-add-to-cart" data-product-id="' . esc_attr($atts['product_id']) . '">' . esc_html__('Add to Cart', 'listinger-ecommerce') . '</button>';
        }

        return '';
    }

    public function checkout_shortcode() {
        $cart = isset($_COOKIE['listinger_cart']) ? json_decode(stripslashes($_COOKIE['listinger_cart']), true) : array();

        ob_start();
        ?>

        <div class="listinger-checkout">
            <h1><?php esc_html_e('Checkout', 'listinger-ecommerce'); ?></h1>
            <?php if (!empty($cart)): ?>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <input type="hidden" name="action" value="listinger_process_order">
                    <?php wp_nonce_field('listinger_process_order', 'listinger_process_order_nonce'); ?>
                    <table class="listinger-checkout-table">
                        <thead>
                            <tr>
                                <th><?php esc_html_e('Product', 'listinger-ecommerce'); ?></th>
                                <th><?php esc_html_e('Quantity', 'listinger-ecommerce'); ?></th>
                                <th><?php esc_html_e('Price', 'listinger-ecommerce'); ?></th>
                                <th><?php esc_html_e('Total', 'listinger-ecommerce'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cart as $product_id => $quantity): 
                                $product = get_post($product_id);
                                $price = get_post_meta($product_id, '_listinger_price', true);
                                $total = $price * $quantity;
                            ?>
                                <tr>
                                    <td><?php echo esc_html($product->post_title); ?></td>
                                    <td><?php echo esc_html($quantity); ?></td>
                                    <td><?php echo esc_html($price); ?></td>
                                    <td><?php echo esc_html($total); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <p>
                        <label for="listinger_billing_name"><?php esc_html_e('Name:', 'listinger-ecommerce'); ?></label>
                        <input type="text" id="listinger_billing_name" name="billing_name" required>
                    </p>
                    <p>
                        <label for="listinger_billing_email"><?php esc_html_e('Email:', 'listinger-ecommerce'); ?></label>
                        <input type="email" id="listinger_billing_email" name="billing_email" required>
                    </p>
                    <p>
                        <label for="listinger_billing_mobile"><?php esc_html_e('Mobile:', 'listinger-ecommerce'); ?></label>
                        <input type="text" id="listinger_billing_mobile" name="billing_mobile" required>
                    </p>
                    <p>
                        <label for="listinger_billing_address"><?php esc_html_e('Address:', 'listinger-ecommerce'); ?></label>
                        <textarea id="listinger_billing_address" name="billing_address" required></textarea>
                    </p>
                    <p>
                        <input type="submit" value="<?php esc_html_e('Place Order', 'listinger-ecommerce'); ?>" class="listinger-button">
                    </p>
                </form>
            <?php else: ?>
                <p><?php esc_html_e('Your cart is empty.', 'listinger-ecommerce'); ?></p>
            <?php endif; ?>
        </div>

        <?php
        return ob_get_clean();
    }

    public function add_order_meta_boxes() {
        add_meta_box(
            'listinger_order_details',
            esc_html__('Order Details', 'listinger-ecommerce'),
            array($this, 'render_order_meta_box'),
            'listinger_order',
            'normal',
            'high'
        );
    }

    public function render_order_meta_box($post) {
        $billing_name = get_post_meta($post->ID, '_listinger_billing_name', true);
        $billing_email = get_post_meta($post->ID, '_listinger_billing_email', true);
        $billing_address = get_post_meta($post->ID, '_listinger_billing_address', true);
        $billing_mobile = get_post_meta($post->ID, '_listinger_billing_mobile', true);
        $order_items = get_post_meta($post->ID, '_listinger_order_items', true);

        ?>
        <p><strong><?php esc_html_e('Name:', 'listinger-ecommerce'); ?></strong> <?php echo esc_html($billing_name); ?></p>
        <p><strong><?php esc_html_e('Email:', 'listinger-ecommerce'); ?></strong> <?php echo esc_html($billing_email); ?></p>
        <p><strong><?php esc_html_e('Mobile:', 'listinger-ecommerce'); ?></strong> <?php echo esc_html($billing_mobile); ?></p>
        <p><strong><?php esc_html_e('Address:', 'listinger-ecommerce'); ?></strong> <?php echo esc_html($billing_address); ?></p>

        <h3><?php esc_html_e('Order Items:', 'listinger-ecommerce'); ?></h3>
        <table class="listinger-order-items-table">
            <thead>
                <tr>
                    <th><?php esc_html_e('Product', 'listinger-ecommerce'); ?></th>
                    <th><?php esc_html_e('Quantity', 'listinger-ecommerce'); ?></th>
                    <th><?php esc_html_e('Price', 'listinger-ecommerce'); ?></th>
                    <th><?php esc_html_e('Total', 'listinger-ecommerce'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($order_items as $product_id => $quantity): 
                    $product = get_post($product_id);
                    $price = get_post_meta($product_id, '_listinger_price', true);
                    $total = $price * $quantity;
                ?>
                    <tr>
                        <td><?php echo esc_html($product->post_title); ?></td>
                        <td><?php echo esc_html($quantity); ?></td>
                        <td><?php echo esc_html($price); ?></td>
                        <td><?php echo esc_html($total); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php
    }
}

new Listinger_Orders();

