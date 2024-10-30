<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Template for product archive view.
get_header();
if ( have_posts() ) :
    ?>
    <div class="listinger-products">
        <h1><?php post_type_archive_title(); ?></h1>
        <ul class="listinger-product-list">
        <?php
        while ( have_posts() ) : the_post();
            $meta_values = get_post_meta( get_the_ID() ); // Retrieve all meta values once
            ?>
            <li class="listinger-product-item">
                <div class="listinger-product">
                    <?php if ( has_post_thumbnail() ) : ?>
                        <div class="listinger-product-thumbnail">
                            <?php
                            $thumbnail_id = get_post_thumbnail_id();
                            $thumbnail_url = wp_get_attachment_image_url( $thumbnail_id, 'medium' );
                            ?>
                            <img src="<?php echo esc_url( $thumbnail_url ); ?>" alt="<?php the_title_attribute(); ?>">
                        </div>
                    <?php else : ?>
                        <div class="listinger-product-thumbnail">
                            <img src="<?php echo esc_url( 'path-to-placeholder-image.jpg' ); ?>" alt="<?php esc_attr_e( 'Placeholder', 'listinger-ecommerce' ); ?>">
                        </div>
                    <?php endif; ?>
                    <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                    <div class="listinger-product-meta">
                        <p><?php esc_html_e( 'Price:', 'listinger-ecommerce' ); ?> <?php echo esc_html( get_post_meta( get_the_ID(), '_listinger_price', true ) ); ?></p>
                    </div>
                    <div class="listinger-product-actions">
                        <?php if ( ! empty( $meta_values['_listinger_mobile_number'][0] ) ) : ?>
                            <button class="listinger-button listinger-mobile-number" style="background-color: #ff5722;"><?php esc_html_e( '+91 ', 'listinger-ecommerce' ); ?><?php echo esc_html( $meta_values['_listinger_mobile_number'][0] ); ?></button>
                        <?php endif; ?>
                        <button class="listinger-button listinger-contact-supplier" data-product-name="<?php echo esc_attr( the_title_attribute( 'echo=0' ) ); ?>"><?php esc_html_e( 'Contact Supplier', 'listinger-ecommerce' ); ?></button>
                        <button class="listinger-button listinger-add-to-wishlist" data-product-id="<?php echo esc_attr( get_the_ID() ); ?>">
                            <span class="dashicons dashicons-heart"></span>
                        </button>
                        <button class="listinger-button listinger-whatsapp" data-product-name="<?php echo esc_attr( the_title_attribute( 'echo=0' ) ); ?>"><?php esc_html_e('WhatsApp', 'listinger-ecommerce'); ?></button>
                        <button class="listinger-button listinger-add-to-cart" data-product-id="<?php echo esc_attr( get_the_ID() ); ?>"><?php esc_html_e( 'Add to Cart', 'listinger-ecommerce' ); ?></button>
                        <?php echo do_shortcode('[listinger_checkout_button]'); ?>
                    </div>
                </div>
            </li>
            <?php
        endwhile;
        ?>
        </ul>
    </div>
    <?php
else :
    ?>
    <p><?php esc_html_e( 'No products found.', 'listinger-ecommerce' ); ?></p>
    <?php
endif;
get_footer();
?>
