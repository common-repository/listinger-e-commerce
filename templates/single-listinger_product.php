<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Template for single product view.
get_header();

if ( have_posts() ) :
    while ( have_posts() ) : the_post();
        $meta_fields = array(
            'price', 'stock', 'min_order_qty', 'packaging_size', 'type', 'brand', 'country_of_origin', 
            'description', 'image_url', 'fragrance', 'skin_type', 'packaging_type', 'face_wash_type', 
            'purpose', 'key_ingredients', 'third_party_manufacturing', 'shelf_life', 'mobile_number', 
            'address', 'age_group', 'gender', 'ingredient_type', 'features', 'type_of_cream', 'gas', 
            'organic', 'shade', 'skin_tone', 'color', 'flavor', 'form', 'usage_application', 'flavor_base', 
            'oil_type', 'hair_oil_type', 'soap_type', 'is_it_handmade', 'finish_type', 'prescription_non_prescription'
        );

        $meta_values = array();
        foreach ( $meta_fields as $field ) {
            $meta_values[$field] = get_post_meta( get_the_ID(), '_listinger_' . $field, true );
        }

        $images = get_post_meta( get_the_ID(), '_listinger_product_images', true );
        $video = get_post_meta( get_the_ID(), '_listinger_product_video', true );
        ?>
        <div class="listinger-product">
            <div class="listinger-product-columns">
                <div class="listinger-product-image">
                    <?php 
                    if ( has_post_thumbnail() ) {
                        the_post_thumbnail('medium');
                    } elseif ( $images ) {
                        $images = explode(',', $images);
                        foreach ( $images as $image_id ) {
                            $image_url = wp_get_attachment_url( $image_id );
                            if ( $image_url ) {
                                echo '<img src="' . esc_url( $image_url ) . '" style="max-height: 400px; margin-bottom: 10px;">';
                            }
                        }
                    }
                    if ( $video ) {
                        echo "<div class='listinger-product-video' style='margin-bottom: 10px;'>";
                        echo wp_kses_post( wp_oembed_get( esc_url( $video ) ) );
                        echo "</div>";
                    }
                    ?>
                </div>
                <div class="listinger-product-details">
                    <h1><?php the_title(); ?></h1>
                    <div class="listinger-product-meta">
                        <?php foreach ( $meta_values as $field => $value ) : ?>
                            <?php if ( ! empty( $value ) ) : ?>
                                <p><?php echo esc_html( ucfirst( str_replace('_', ' ', $field) ) ) . ': ' . esc_html( $value ); ?></p>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                    <div class="listinger-product-actions">
                        <?php if ( ! empty( $meta_values['mobile_number'] ) ) : ?>
                            <button class="listinger-button listinger-mobile-number" style="background-color: #ff5722;"><?php esc_html_e('+91 ', 'listinger-ecommerce'); ?><?php echo esc_html( $meta_values['mobile_number'] ); ?></button>
                        <?php endif; ?>
                        <button class="listinger-button listinger-contact-supplier" data-product-name="<?php echo esc_attr( get_the_title() ); ?>"><?php esc_html_e('Contact Supplier', 'listinger-ecommerce'); ?></button>
                        <button class="listinger-button listinger-add-to-cart" data-product-id="<?php echo esc_attr( get_the_ID() ); ?>"><?php esc_html_e('Add to Cart', 'listinger-ecommerce'); ?></button>
                        <button class="listinger-button listinger-whatsapp" onclick="window.open('https://wa.me/?text=<?php echo esc_js( urlencode( get_the_title() ) ); ?>', '_blank');">
                            <?php esc_html_e('WhatsApp', 'listinger-ecommerce'); ?>
                        </button>
                        <?php echo esc_html( str_replace('Proceed to Checkout', __('Checkout', 'listinger-ecommerce'), do_shortcode('[listinger_checkout_button]')) ); ?>
                    </div>
                    <div class="listinger-product-description">
                        <h2><?php esc_html_e('Product Description', 'listinger-ecommerce'); ?></h2>
                        <?php the_content(); ?>
                    </div>
                </div>
            </div>
            <div class="listinger-related-products">
                <h2><?php esc_html_e('Related Products', 'listinger-ecommerce'); ?></h2>
                <div class="listinger-related-products-slider">
                    <?php
                    $related_products = Listinger_Simple_Product_Listing::get_related_products( get_the_ID() );
                    if ( $related_products->have_posts() ) :
                        while ( $related_products->have_posts() ) : $related_products->the_post();
                            ?>
                            <div class="listinger-related-product-item">
                                <?php if ( has_post_thumbnail() ) : ?>
                                    <div class="listinger-product-thumbnail">
                                        <a href="<?php the_permalink(); ?>"><?php the_post_thumbnail('medium'); ?></a>
                                    </div>
                                <?php endif; ?>
                                <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                            </div>
                            <?php
                        endwhile;
                        wp_reset_postdata();
                    else :
                        echo '<p>' . esc_html__('No related products found.', 'listinger-ecommerce') . '</p>';
                    endif;
                    ?>
                </div>
            </div>
        </div>
        <div id="listinger-contact-form" style="display:none;">
            <h2><?php esc_html_e('Contact Supplier', 'listinger-ecommerce'); ?></h2>
            <form>
                <p>
                    <label for="listinger_contact_name"><?php esc_html_e('Name:', 'listinger-ecommerce'); ?></label>
                    <input type="text" id="listinger_contact_name" name="listinger_contact_name" />
                </p>
                <p>
                    <label for="listinger_contact_mobile"><?php esc_html_e('Mobile Number:', 'listinger-ecommerce'); ?></label>
                    <input type="text" id="listinger_contact_mobile" name="listinger_contact_mobile" />
                </p>
                <p>
                    <label for="listinger_contact_message"><?php esc_html_e('Message:', 'listinger-ecommerce'); ?></label>
                    <textarea id="listinger_contact_message" name="listinger_contact_message"></textarea>
                </p>
                <p>
                    <input type="submit" value="<?php esc_html_e('Send', 'listinger-ecommerce'); ?>" class="listinger-button" />
                </p>
            </form>
        </div>
        <?php
    endwhile;
endif;
get_footer();
?>
