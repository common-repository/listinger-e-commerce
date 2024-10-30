<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

get_header(); // Load the header

// Retrieve global custom buttons from settings
$global_buttons = get_option('listinger_custom_buttons', array());

// Start the loop to display the single product
if (have_posts()) :
    while (have_posts()) : the_post();

        // Retrieve post-specific custom buttons
        $post_buttons = get_post_meta(get_the_ID(), '_listinger_product_buttons', true);

        // Determine which buttons to use (post-specific buttons override global ones)
        $buttons = !empty($post_buttons) ? $post_buttons : $global_buttons;
?>
<div class="listinger-single-product-container">

    <!-- Left Column: Featured Image -->
    <div class="listinger-product-image">
        <?php if (has_post_thumbnail()) : ?>
            <?php the_post_thumbnail('full'); ?>
        <?php endif; ?>
    </div>

    <!-- Right Column: Doctor Details -->
    <div class="basic-info">
        <h1><?php echo esc_html(get_post_meta(get_the_ID(), 'doctor-name', true)); ?></h1>
        <p><?php echo esc_html(get_post_meta(get_the_ID(), 'specialist', true)); ?></p>
        <p><?php echo esc_html(get_post_meta(get_the_ID(), 'address', true)); ?></p>
        <p><strong><?php esc_html_e('Consulting Fee:', 'listinger-ecommerce'); ?></strong> <?php echo esc_html(get_post_meta(get_the_ID(), 'consulting-fee', true)); ?></p>
        <p><?php echo esc_html(get_post_meta(get_the_ID(), 'clinic-name', true)); ?></p>
        <p><?php echo esc_html(get_post_meta(get_the_ID(), 'clinic-location', true)); ?></p>

        <!-- Add to Cart Button -->
        <button id="add-to-cart" data-product-id="<?php echo esc_attr(get_the_ID()); ?>" class="listinger-button">
            <?php esc_html_e('Add to Cart', 'listinger-ecommerce'); ?>
        </button>

        <!-- Custom Buttons Section -->
        <div class="listinger-custom-buttons">
            <div>
                <?php
                if (!empty($buttons)) {
                    foreach ($buttons as $button) {
                        $label = esc_html($button['label']);
                        $type = esc_attr($button['type']);
                        $url = esc_url($button['url']);
                        $icon = esc_attr($button['icon']); // Get the icon
                        $whatsapp_number = esc_attr($button['whatsapp_number']); // Get the WhatsApp number

                        if ($type === 'mobile') {
                            echo "<a href='" . esc_url('tel:' . $url) . "' class='button'>" . esc_html($label) . "</a>";
                        } elseif ($type === 'contact') {
                            echo "<a href='#contact-form' class='button'>" . esc_html($label) . "</a>";
                        } elseif ($type === 'whatsapp') {
                            echo "<a href='" . esc_url('https://wa.me/' . $whatsapp_number) . "' target='_blank' class='button'>" . esc_html($label) . "</a>";
                        } elseif ($type === 'insta' || $type === 'facebook') {
                            echo "<a href='" . esc_url($url) . "' target='_blank' class='button'><i class='fa " . esc_attr($icon) . "'></i> " . esc_html($label) . "</a>";
                        } elseif ($type === 'custom') {
                            echo "<a href='" . esc_url($url) . "' target='_blank' class='button'>" . esc_html($label) . "</a>";
                        }
                    }
                }
                ?>
            </div>
        </div>
    </div>

    <!-- Full Width Sections -->
    <div class="listinger-full-width">
        <div class="listinger-profile">
            <p><?php echo wp_kses_post(wpautop(get_post_meta(get_the_ID(), 'profile', true))); ?></p>
        </div>

        <div class="listinger-specialization">
            <?php foreach (['specialization'] as $field_name) : ?>
            <?php $meta_value = get_post_meta(get_the_ID(), $field_name, true); ?>
            <?php if (!empty($meta_value)) : ?>
                <div class="listinger-meta-section full-width">
                    <h2><?php echo esc_html(ucfirst(str_replace('-', ' ', $field_name))); ?></h2>
                    <div class="listinger-three-column">
                        <?php
                        $items = preg_split('/\r\n|\r|\n/', trim($meta_value));
                        $total_items = count($items);
                        $items_per_column = ceil($total_items / 3);

                        for ($i = 0; $i < 3; $i++) {
                            echo '<ul>'; // Open a new column
                            for ($j = $i * $items_per_column; $j < ($i + 1) * $items_per_column && $j < $total_items; $j++) {
                                echo '<li><i class="fa fa-check-circle"></i> ' . esc_html($items[$j]) . '</li>';
                            }
                            echo '</ul>'; // Close the column
                        }
                        ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
        </div>
    </div>
</div>

<?php
    endwhile;
endif;

get_footer(); // Load the footer

// Enqueue the necessary scripts and styles
function listinger_enqueue_single_product_assets() {
    // Enqueue CSS for single product page
    wp_register_style('listinger-single-product', plugins_url('/assets/css/listinger-single-product.css', __FILE__));
    wp_enqueue_style('listinger-single-product');
    
    // Enqueue the add-to-cart script
    wp_register_script('listinger-add-to-cart', plugins_url('/assets/js/listinger-add-to-cart.js', __FILE__), array('jquery'), null, true);
    
    // Localize script to pass Ajax URL and other localized data
    wp_localize_script('listinger-add-to-cart', 'listinger_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('listinger_add_to_cart_nonce'),
        'success_message' => esc_js(__('Product added to cart!', 'listinger-ecommerce'))
    ));

    wp_enqueue_script('listinger-add-to-cart');
}
add_action('wp_enqueue_scripts', 'listinger_enqueue_single_product_assets');

