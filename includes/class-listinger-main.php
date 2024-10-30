<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Listinger_Simple_Product_Listing {

    public function __construct() {
        // Add product custom post type.
        add_action( 'init', array( $this, 'register_product_post_type' ), 0 );

        // Add product category taxonomy.
        add_action( 'init', array( $this, 'register_product_category_taxonomy' ), 0 );

        // Enqueue scripts and styles.
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts_styles' ) );

        // Enqueue admin scripts and styles for media uploader.
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts_styles' ) );

        // Add custom fields for products.
        add_action( 'add_meta_boxes', array( $this, 'add_product_meta_boxes' ) );
        add_action( 'save_post', array( $this, 'save_product_meta' ) );

        // Load templates.
        add_filter( 'single_template', array( $this, 'load_single_product_template' ) );
        add_filter( 'archive_template', array( $this, 'load_archive_product_template' ) );
        add_filter( 'taxonomy_template', array( $this, 'load_category_product_template' ) );

        // Register shortcodes.
        add_shortcode( 'listinger_product_list', array( $this, 'shortcode_product_list' ) );
        add_shortcode( 'listinger_product', array( $this, 'shortcode_single_product' ) );
        add_shortcode( 'listinger_product_urls', array( $this, 'shortcode_product_urls' ) );
        add_shortcode( 'listinger_cart', array( $this, 'shortcode_cart' ) );
        add_shortcode( 'listinger_checkout', array( $this, 'shortcode_checkout' ) );
        add_shortcode( 'listinger_checkout_button', array( $this, 'shortcode_checkout_button' ) );

        // Handle AJAX for contact form and cart.
        add_action( 'wp_ajax_listinger_contact_supplier', array( $this, 'handle_contact_supplier' ) );
        add_action( 'wp_ajax_nopriv_listinger_contact_supplier', array( $this, 'handle_contact_supplier' ) );
        add_action( 'wp_ajax_listinger_add_to_cart', array( $this, 'handle_add_to_cart' ) );
        add_action( 'wp_ajax_nopriv_listinger_add_to_cart', array( $this, 'handle_add_to_cart' ) );

        // Flush rewrite rules on activation.
        register_activation_hook( __FILE__, array( $this, 'flush_rewrite_rules' ) );

        // Add custom fields for product category.
        add_action('listinger_product_category_add_form_fields', array( $this, 'listinger_add_category_custom_field'), 10, 2);
        add_action('listinger_product_category_edit_form_fields', array( $this, 'listinger_edit_category_custom_field'), 10, 2);
        add_action('created_listinger_product_category', array( $this, 'listinger_save_category_custom_field'), 10, 2);
        add_action('edited_listinger_product_category', array( $this, 'listinger_save_category_custom_field'), 10, 2);

        // Start session if not started.
        if (!session_id()) {
            session_start();
        }
    }

    public function flush_rewrite_rules() {
        $this->register_product_post_type();
        $this->register_product_category_taxonomy();
        flush_rewrite_rules();
    }

    public function register_product_post_type() {
        // Register the custom post type for products.
        register_post_type( 'listinger_product', array(
            'labels' => array(
                'name' => esc_html__( 'Listinger', 'listinger-ecommerce' ),
                'singular_name' => esc_html__( 'Product', 'listinger-ecommerce' ),
                'menu_name' => esc_html__( 'Listinger', 'listinger-ecommerce' ),
                'name_admin_bar' => esc_html__( 'Product', 'listinger-ecommerce' ),
                'add_new' => esc_html__( 'Add New', 'listinger-ecommerce' ),
                'add_new_item' => esc_html__( 'Add New Product', 'listinger-ecommerce' ),
                'new_item' => esc_html__( 'New Product', 'listinger-ecommerce' ),
                'edit_item' => esc_html__( 'Edit Product', 'listinger-ecommerce' ),
                'view_item' => esc_html__( 'View Product', 'listinger-ecommerce' ),
                'all_items' => esc_html__( 'All Products', 'listinger-ecommerce' ),
                'search_items' => esc_html__( 'Search Products', 'listinger-ecommerce' ),
                'parent_item_colon' => esc_html__( 'Parent Products:', 'listinger-ecommerce' ),
                'not_found' => esc_html__( 'No products found.', 'listinger-ecommerce' ),
                'not_found_in_trash' => esc_html__( 'No products found in Trash.', 'listinger-ecommerce' ),
            ),
            'public' => true,
            'has_archive' => true,
            'rewrite' => array( 'slug' => 'fmcg-products' ),
            'supports' => array( 'title', 'editor', 'thumbnail' ),
        ) );
    }

    public function register_product_category_taxonomy() {
        // Register the custom taxonomy for product categories.
        register_taxonomy( 'listinger_product_category', 'listinger_product', array(
            'labels' => array(
                'name' => esc_html__( 'Product Categories', 'listinger-ecommerce' ),
                'singular_name' => esc_html__( 'Product Category', 'listinger-ecommerce' )
            ),
            'hierarchical' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => array( 'slug' => 'fmcg-product-category' ),
        ) );
    }

    public function enqueue_scripts_styles() {
        // Enqueue necessary scripts and styles.
        wp_enqueue_style( 'listinger-ecommerce-styles', LISTINGER_ECOMMERCE_URL . 'assets/css/styles.css' );
        wp_enqueue_script( 'listinger-ecommerce-scripts', LISTINGER_ECOMMERCE_URL . 'assets/js/scripts.js', array( 'jquery' ), LISTINGER_ECOMMERCE_VERSION, true );
        wp_enqueue_script( 'listinger-contact-supplier', LISTINGER_ECOMMERCE_URL . 'assets/js/contact-supplier.js', array( 'jquery' ), LISTINGER_ECOMMERCE_VERSION, true );

        // Localize script for AJAX.
        wp_localize_script( 'listinger-ecommerce-scripts', 'listinger_ajax', array(
            'ajax_url' => esc_url( admin_url( 'admin-ajax.php' ) ),
            'nonce' => wp_create_nonce( 'listinger_ajax_nonce' ),
        ));
    }

    public function enqueue_admin_scripts_styles() {
        // Enqueue WordPress media uploader script.
        wp_enqueue_media();
        wp_enqueue_script( 'listinger-admin-scripts', LISTINGER_ECOMMERCE_URL . 'assets/js/admin-scripts.js', array( 'jquery' ), LISTINGER_ECOMMERCE_VERSION, true );
    }

    public function add_product_meta_boxes() {
        // Add custom meta boxes for products.
        add_meta_box( 'listinger_product_details', esc_html__( 'Product Details', 'listinger-ecommerce' ), array( $this, 'render_product_meta_box' ), 'listinger_product', 'normal', 'high' );
    }

    public function render_product_meta_box( $post ) {
        // Render the custom meta box.
        wp_nonce_field( 'listinger_save_product_meta', 'listinger_product_meta_nonce' );
        $meta_fields = array(
            'price', 'stock', 'min_order_qty', 'packaging_size', 'type', 'brand', 'country_of_origin', 
            'description', 'image_url', 'fragrance', 'skin_type', 'packaging_type', 'face_wash_type', 
            'purpose', 'key_ingredients', 'third_party_manufacturing', 'shelf_life', 'mobile_number', 
            'address', 'age_group', 'gender', 'ingredient_type', 'features', 'type_of_cream', 'gas', 
            'organic', 'shade', 'skin_tone', 'color', 'flavor', 'form', 'usage_application', 'flavor_base', 
            'oil_type', 'hair_oil_type', 'soap_type', 'is_it_handmade', 'finish_type', 'prescription_non_prescription'
        );

        $meta_values = array();
        foreach ($meta_fields as $field) {
            $meta_values[$field] = get_post_meta( $post->ID, '_listinger_' . $field, true );
        }
        ?>
        <div class="listinger-product-meta-box">
            <div class="listinger-product-image">
                <label for="listinger_product_image"><?php esc_html_e( 'Product Images:', 'listinger-ecommerce' ); ?></label>
                <input type="hidden" id="listinger_product_images" name="listinger_product_images" value="<?php echo esc_attr( $meta_values['images'] ); ?>" />
                <div id="listinger_product_images_preview" style="width: 100%; max-height: 400px; overflow-y: auto; display: flex; gap: 10px;">
                    <?php 
                    if ($meta_values['images']) {
                        $images = explode(',', $meta_values['images']);
                        foreach ($images as $image_id) {
                            $image_url = esc_url( wp_get_attachment_url( $image_id ) );
                            echo '<img src="' . esc_url($image_url) . '" style="max-height: 400px;">';
                        }
                    }
                    ?>
                </div>
                <button type="button" class="button" id="listinger_upload_images_button"><?php esc_html_e( 'Upload Images', 'listinger-ecommerce' ); ?></button>
                <button type="button" class="button" id="listinger_remove_images_button"><?php esc_html_e( 'Remove Images', 'listinger-ecommerce' ); ?></button>
            </div>
            <div class="listinger-product-video">
                <label for="listinger_product_video"><?php esc_html_e( 'Product Video:', 'listinger-ecommerce' ); ?></label>
                <input type="text" id="listinger_product_video" name="listinger_product_video" value="<?php echo esc_attr( $meta_values['video'] ); ?>" placeholder="<?php esc_attr_e( 'Enter YouTube or Vimeo URL', 'listinger-ecommerce' ); ?>" />
            </div>
            <div class="listinger-product-details">
                <?php foreach ($meta_fields as $field) : ?>
                    <p>
                        <?php
                        printf( esc_html__( '%s:', 'listinger-ecommerce' ), esc_html( ucwords( str_replace( '_', ' ', $field ) ) ) );
                        ?>
                        <?php if ($field == 'description') : ?>
                            <textarea id="listinger_<?php echo esc_attr($field); ?>" name="listinger_<?php echo esc_attr($field); ?>"><?php echo esc_textarea( $meta_values[$field] ); ?></textarea>
                        <?php else : ?>
                            <input type="text" id="listinger_<?php echo esc_attr($field); ?>" name="listinger_<?php echo esc_attr($field); ?>" value="<?php echo esc_attr( $meta_values[$field] ); ?>" />
                        <?php endif; ?>
                    </p>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }

    public function save_product_meta( $post_id ) {
        // Save custom meta data for products.
        if ( ! isset( $_POST['listinger_product_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['listinger_product_meta_nonce'] ) ), 'listinger_save_product_meta' ) ) {
            return;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        $fields = array(
            'price', 'stock', 'min_order_qty', 'packaging_size', 'type', 'brand', 'country_of_origin', 
            'description', 'image_url', 'fragrance', 'skin_type', 'packaging_type', 'face_wash_type', 
            'purpose', 'key_ingredients', 'third_party_manufacturing', 'shelf_life', 'mobile_number', 
            'address', 'age_group', 'gender', 'ingredient_type', 'features', 'type_of_cream', 'gas', 
            'organic', 'shade', 'skin_tone', 'color', 'flavor', 'form', 'usage_application', 'flavor_base', 
            'oil_type', 'hair_oil_type', 'soap_type', 'is_it_handmade', 'finish_type', 'prescription_non_prescription'
        );

        foreach ( $fields as $field ) {
            if ( isset( $_POST['listinger_' . $field] ) ) {
                update_post_meta( $post_id, '_listinger_' . $field, sanitize_text_field( wp_unslash( $_POST['listinger_' . $field] ) ) );
            }
        }
    }

    public function load_single_product_template( $template ) {
        if ( 'listinger_product' === get_post_type() && locate_template( array( 'single-listinger_product.php' ) ) !== $template ) {
            return LISTINGER_ECOMMERCE_DIR . 'templates/single-listinger_product.php';
        }
        return $template;
    }

    public function load_archive_product_template( $template ) {
        if ( is_post_type_archive( 'listinger_product' ) && locate_template( array( 'archive-listinger_product.php' ) ) !== $template ) {
            return LISTINGER_ECOMMERCE_DIR . 'templates/archive-listinger_product.php';
        }
        return $template;
    }

    public function load_category_product_template( $template ) {
        if ( is_tax( 'listinger_product_category' ) && locate_template( array( 'taxonomy-listinger_product_category.php' ) ) !== $template ) {
            return LISTINGER_ECOMMERCE_DIR . 'templates/taxonomy-listinger_product_category.php';
        }
        return $template;
    }

    public function shortcode_product_list( $atts ) {
        ob_start();

        // Extract shortcode attributes.
        $atts = shortcode_atts( array(
            'category' => '', // Attribute for single category.
        ), $atts, 'listinger_product_list' );

        // Query arguments.
        $args = array(
            'post_type' => 'listinger_product',
            'posts_per_page' => -1,
        );

        // If category attribute is set, construct the tax query.
        if ( ! empty( $atts['category'] ) ) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'listinger_product_category',
                    'field'    => 'slug',
                    'terms'    => sanitize_text_field( $atts['category'] ),
                ),
            );
        }

        // Execute query.
        $products = new WP_Query( $args );

        // Check if any products are found.
        if ( $products->have_posts() ) : ?>
            <ul class="listinger-product-list">
                <?php while ( $products->have_posts() ) : $products->the_post(); 
                    $meta_fields = array(
                        'price', 'min_order_qty', 'packaging_size', 'brand', 'mobile_number', 
                        'address', 'image_url', 'description'
                    );
                    $meta_values = array();
                    foreach ($meta_fields as $field) {
                        $meta_values[$field] = get_post_meta( get_the_ID(), '_listinger_' . $field, true );
                    }
                    ?>
                    <li class="listinger-product-item">
                        <div class="listinger-product-thumbnail">
                            <?php 
                            if ( has_post_thumbnail() ) {
                                the_post_thumbnail( 'medium' );
                            } else {
                                // Fallback to display image from single product page.
                                $featured_image_id = get_post_thumbnail_id( get_the_ID() );
                                if ( $featured_image_id ) {
                                    echo wp_get_attachment_image( $featured_image_id, 'medium' );
                                }
                            }
                            ?>
                        </div>
                        <div class="listinger-product-details">
                            <h3><a href="<?php echo esc_url( get_the_permalink() ); ?>"><?php echo esc_html( get_the_title() ); ?></a></h3>
                            <?php foreach ($meta_fields as $field) : ?>
                                <?php if (!empty($meta_values[$field]) && $field != 'image_url') : ?>
                                    <p>
                                        <?php
                                        printf( esc_html__( '%s:', 'listinger-ecommerce' ), esc_html( ucwords( str_replace( '_', ' ', $field ) ) ) );
                                        ?>
                                        <?php echo esc_html( $meta_values[$field] ); ?>
                                    </p>
                                <?php endif; ?>
                            <?php endforeach; ?>
                            <p>
                                <?php
                                $description = get_the_content();
                                if ( strlen( $description ) > 150 ) {
                                    echo esc_html( substr( $description, 0, 150 ) ) . '... ';
                                    echo '<a href="' . esc_url( get_the_permalink() ) . '">' . esc_html__( 'View More', 'listinger-ecommerce' ) . '</a>';
                                } else {
                                    echo esc_html( $description );
                                }
                                ?>
                            </p>
                            <div class="listinger-product-actions">
                                <?php if (!empty($meta_values['mobile_number'])) : ?>
                                    <button class="listinger-button listinger-mobile-number" style="background-color: #ff5722;"><?php esc_html_e( '+91 ', 'listinger-ecommerce' ); ?><?php echo esc_html( $meta_values['mobile_number'] ); ?></button>
                                <?php endif; ?>
                                <button class="listinger-button listinger-contact-supplier" data-product-name="<?php echo esc_attr( get_the_title() ); ?>"><?php esc_html_e( 'Contact Supplier', 'listinger-ecommerce' ); ?></button>
                                <button class="listinger-button listinger-add-to-cart" data-product-id="<?php echo esc_attr( get_the_ID() ); ?>"><?php esc_html_e( 'Add to Cart', 'listinger-ecommerce' ); ?></button>
                                <button class="listinger-button listinger-whatsapp" onclick="window.open('https://wa.me/?text=<?php echo esc_url( urlencode(get_the_title()) ); ?>', '_blank');">
                                    <?php esc_html_e( 'WhatsApp', 'listinger-ecommerce' ); ?>
                                </button>
                            </div>
                        </div>
                    </li>
                <?php endwhile; wp_reset_postdata(); ?>
            </ul>
        <?php else : ?>
            <p><?php esc_html_e( 'No products found.', 'listinger-ecommerce' ); ?></p>
        <?php endif;

        return ob_get_clean();
    }

    public function shortcode_product_urls( $atts ) {
        ob_start();

        // Extract shortcode attributes.
        $atts = shortcode_atts( array(
            'category' => '', // Attribute for single category.
            'columns' => 1,   // Default to full width if not specified.
            'links' => 9,     // Default to 9 links.
        ), $atts, 'listinger_product_urls' );

        // Generate a unique transient key based on the attributes.
        $transient_key = 'listinger_product_urls_' . md5( serialize( $atts ) );

        // Attempt to get cached data.
        $product_urls = get_transient( $transient_key );

        if ( false === $product_urls ) {
            // Query arguments.
            $args = array(
                'post_type' => 'listinger_product',
                'posts_per_page' => -1,
                'fields' => 'ids', // Only get post IDs.
            );

            // If category attribute is set, construct the tax query.
            if ( ! empty( $atts['category'] ) ) {
                $args['tax_query'] = array(
                    array(
                        'taxonomy' => 'listinger_product_category',
                        'field'    => 'slug',
                        'terms'    => sanitize_text_field( $atts['category'] ),
                    ),
                );
            }

            // Execute query.
            $products = new WP_Query( $args );
            $product_urls = $products->posts;

            // Cache the results for 12 hours.
            set_transient( $transient_key, $product_urls, 12 * HOUR_IN_SECONDS );
        }

        // Check if any products are found.
        if ( ! empty( $product_urls ) ) :
            $total_products = count($product_urls);
            $columns = absint($atts['columns']);
            $links = absint($atts['links']);
            $column_class = 'columns-' . $columns;
            ?>
            
            <ul class="listinger-product-urls <?php echo esc_attr( $column_class ); ?>">
                <?php foreach ( array_slice( $product_urls, 0, $links ) as $post_id ) : ?>
                    <li>
                        <a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>">
                            <?php echo esc_html( get_the_title( $post_id ) ); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
            <?php if ( $total_products > $links ) :
                $term = get_term_by('slug', sanitize_text_field( $atts['category'] ), 'listinger_product_category');
                $custom_slug = get_term_meta($term->term_id, 'custom_slug', true);

                if ($custom_slug) {
                    $view_all_url = home_url('/' . $custom_slug . '/');
                } else {
                    $view_all_url = get_term_link($term);
                }

                // Ensure View All link works on homepage as well.
                if (is_front_page() && is_page()) {
                    $view_all_url = home_url('/' . $custom_slug . '/');
                }
                ?>
                <a href="<?php echo esc_url( $view_all_url ); ?>" class="view-all-products">
                    <?php esc_html_e( 'View All', 'listinger-ecommerce'); ?>
                </a>
            <?php endif; ?>
        <?php else : ?>
            <p><?php esc_html_e( 'No products found.', 'listinger-ecommerce' ); ?></p>
        <?php endif;

        return ob_get_clean();
    }

    public function shortcode_cart() {
        ob_start();

        if ( ! isset( $_SESSION['listinger_cart'] ) || empty( $_SESSION['listinger_cart'] ) ) {
            echo '<p>' . esc_html__( 'Your cart is empty.', 'listinger-ecommerce' ) . '</p>';
        } else {
            echo '<table class="listinger-cart-table">';
            echo '<thead><tr><th>' . esc_html__( 'Product', 'listinger-ecommerce' ) . '</th><th>' . esc_html__( 'Quantity', 'listinger-ecommerce' ) . '</th><th>' . esc_html__( 'Price', 'listinger-ecommerce' ) . '</th><th>' . esc_html__( 'Total', 'listinger-ecommerce' ) . '</th></tr></thead>';
            echo '<tbody>';

            $cart_total = 0;

            foreach ( $_SESSION['listinger_cart'] as $product_id => $quantity ) {
                $product = get_post( $product_id );
                $price = get_post_meta( $product_id, '_listinger_price', true );
                $total = $price * $quantity;
                $cart_total += $total;

                echo '<tr>';
                echo '<td>' . esc_html( $product->post_title ) . '</td>';
                echo '<td>' . esc_html( $quantity ) . '</td>';
                echo '<td>' . esc_html( wc_price( $price ) ) . '</td>';
                echo '<td>' . esc_html( wc_price( $total ) ) . '</td>';
                echo '</tr>';
            }

            echo '</tbody>';
            echo '<tfoot>';
            echo '<tr><th colspan="3">' . esc_html__( 'Cart Total', 'listinger-ecommerce' ) . '</th><th>' . esc_html( wc_price( $cart_total ) ) . '</th></tr>';
            echo '</tfoot>';
            echo '</table>';

            echo '<a href="' . esc_url( home_url( '/listinger-checkout' ) ) . '" class="listinger-button listinger-checkout-button">' . esc_html__( 'Proceed to Checkout', 'listinger-ecommerce' ) . '</a>';
        }

        return ob_get_clean();
    }

    public function shortcode_checkout() {
        ob_start();

        if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['listinger_checkout_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['listinger_checkout_nonce'] ) ), 'listinger_checkout' ) ) {
            $name = sanitize_text_field( $_POST['listinger_name'] );
            $email = sanitize_email( $_POST['listinger_email'] );
            $address = sanitize_textarea_field( $_POST['listinger_address'] );
            $mobile_number = sanitize_text_field( $_POST['listinger_mobile_number'] );

            $order_data = array(
                'post_type'   => 'listinger_order',
                'post_status' => 'publish',
                'post_title'  => $name . ' - ' . current_time( 'mysql' ),
                'meta_input'  => array(
                    '_listinger_email'   => $email,
                    '_listinger_address' => $address,
                    '_listinger_mobile_number' => $mobile_number,
                    '_listinger_cart'    => $_SESSION['listinger_cart'],
                ),
            );

            $order_id = wp_insert_post( $order_data );

            if ( $order_id ) {
                // Clear the cart after order is placed.
                unset( $_SESSION['listinger_cart'] );

                echo '<p>' . esc_html__( 'Thank you for your order!', 'listinger-ecommerce' ) . '</p>';
            } else {
                echo '<p>' . esc_html__( 'There was an issue processing your order. Please try again.', 'listinger-ecommerce' ) . '</p>';
            }
        } else {
            if ( ! isset( $_SESSION['listinger_cart'] ) || empty( $_SESSION['listinger_cart'] ) ) {
                echo '<p>' . esc_html__( 'Your cart is empty.', 'listinger-ecommerce' ) . '</p>';
            } else {
                ?>
                <form method="post" class="listinger-checkout-form">
                    <p>
                        <label for="listinger_name"><?php esc_html_e( 'Name', 'listinger-ecommerce' ); ?></label>
                        <input type="text" id="listinger_name" name="listinger_name" required>
                    </p>
                    <p>
                        <label for="listinger_email"><?php esc_html_e( 'Email', 'listinger-ecommerce' ); ?></label>
                        <input type="email" id="listinger_email" name="listinger_email" required>
                    </p>
                    <p>
                        <label for="listinger_mobile_number"><?php esc_html_e( 'Mobile Number', 'listinger-ecommerce' ); ?></label>
                        <input type="text" id="listinger_mobile_number" name="listinger_mobile_number" required>
                    </p>
                    <p>
                        <label for="listinger_address"><?php esc_html_e( 'Address', 'listinger-ecommerce' ); ?></label>
                        <textarea id="listinger_address" name="listinger_address" required></textarea>
                    </p>
                    <?php wp_nonce_field( 'listinger_checkout', 'listinger_checkout_nonce' ); ?>
                    <p>
                        <input type="submit" value="<?php esc_html_e( 'Place Order', 'listinger-ecommerce' ); ?>">
                    </p>
                </form>
                <?php
            }
        }

        return ob_get_clean();
    }

    public function shortcode_checkout_button() {
        return '<a href="' . esc_url( home_url('/listinger-checkout') ) . '" class="listinger-button listinger-checkout-button">' . esc_html__( 'Proceed to Checkout', 'listinger-ecommerce' ) . '</a>';
    }

    public function handle_contact_supplier() {
        check_ajax_referer( 'listinger_ajax_nonce', 'nonce' );

        $product_name = sanitize_text_field( $_POST['product_name'] );
        $mobile_number = sanitize_text_field( $_POST['mobile_number'] );
        $message = sanitize_textarea_field( $_POST['message'] );

        // Handle the form submission (e.g., send an email, save to database).
        wp_mail(
            'supplier@example.com',
            esc_html__( 'Contact Supplier: ', 'listinger-ecommerce' ) . $product_name,
            esc_html__( 'Product/Service Name: ', 'listinger-ecommerce' ) . $product_name . "\n" .
            esc_html__( 'Mobile Number: ', 'listinger-ecommerce' ) . $mobile_number . "\n" .
            esc_html__( 'Message: ', 'listinger-ecommerce' ) . $message
        );

        wp_send_json_success( array( 'message' => esc_html__( 'Contact form submitted successfully!', 'listinger-ecommerce' ) ) );
    }

    public function handle_add_to_cart() {
        check_ajax_referer( 'listinger_ajax_nonce', 'nonce' );

        if ( ! isset( $_POST['product_id'] ) || ! isset( $_POST['quantity'] ) ) {
            wp_send_json_error( array( 'message' => esc_html__( 'Invalid product or quantity.', 'listinger-ecommerce' ) ) );
        }

        $product_id = intval( $_POST['product_id'] );
        $quantity = intval( $_POST['quantity'] );

        if ( ! $product_id || ! $quantity ) {
            wp_send_json_error( array( 'message' => esc_html__( 'Invalid product or quantity.', 'listinger-ecommerce' ) ) );
        }

        if ( ! isset( $_SESSION['listinger_cart'] ) ) {
            $_SESSION['listinger_cart'] = array();
        }

        if ( ! isset( $_SESSION['listinger_cart'][ $product_id ] ) ) {
            $_SESSION['listinger_cart'][ $product_id ] = 0;
        }

        $_SESSION['listinger_cart'][ $product_id ] += $quantity;

        wp_send_json_success( array( 'message' => esc_html__( 'Product added to cart.', 'listinger-ecommerce' ) ) );
    }

    public static function get_related_products( $post_id ) {
        $terms = get_the_terms( $post_id, 'listinger_product_category' );
        if ( ! $terms || is_wp_error( $terms ) ) {
            return new WP_Query(); // Return an empty query if no terms are found.
        }

        $term_ids = wp_list_pluck( $terms, 'term_id' );

        $related_products = new WP_QUERY( array(
            'post_type' => 'listinger_product',
            'post__not_in' => array( $post_id ),
            'posts_per_page' => 6,
            'tax_query' => array(
                array(
                    'taxonomy' => 'listinger_product_category',
                    'field'    => 'term_id',
                    'terms'    => $term_ids,
                ),
            ),
        ) );

        return $related_products;
    }

    // Add custom fields for product category.
    public function listinger_add_category_custom_field($taxonomy) { ?>
        <div class="form-field term-group">
            <label for="custom_slug"><?php esc_html_e('Custom Slug', 'listinger-ecommerce'); ?></label>
            <input type="text" id="custom_slug" name="custom_slug" value="">
            <p><?php esc_html_e('Enter a custom slug for the category URL.', 'listinger-ecommerce'); ?></p>
        </div>
    <?php }

    public function listinger_edit_category_custom_field($term, $taxonomy) {
        $custom_slug = get_term_meta($term->term_id, 'custom_slug', true); ?>
        <tr class="form-field term-group-wrap">
            <th scope="row"><label for="custom_slug"><?php esc_html_e('Custom Slug', 'listinger-ecommerce'); ?></label></th>
            <td><input type="text" id="custom_slug" name="custom_slug" value="<?php echo esc_attr($custom_slug); ?>"></td>
        </tr>
    <?php }

    public function listinger_save_category_custom_field($term_id, $tt_id) {
        if (isset($_POST['custom_slug']) && '' !== $_POST['custom_slug']) {
            $custom_slug = sanitize_title($_POST['custom_slug']);
            add_term_meta($term_id, 'custom_slug', $custom_slug, true);
        } else {
            delete_term_meta($term_id, 'custom_slug');
        }
    }
}
?>
