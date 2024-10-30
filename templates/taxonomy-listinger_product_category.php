<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

get_header();

if ( have_posts() ) : ?>
    <ul class="listinger-product-list">
        <?php while ( have_posts() ) : the_post(); 
            $meta_fields = array(
                'price', 'min_order_qty', 'packaging_size', 'brand', 'mobile_number', 
                'address', 'image_url', 'description'
            );
            $meta_values = array();
            foreach ( $meta_fields as $field ) {
                $meta_values[$field] = get_post_meta( get_the_ID(), '_listinger_' . $field, true );
            }
            ?>
            <li class="listinger-product-item">
                <div class="listinger-product-thumbnail">
                    <?php 
                    if ( has_post_thumbnail() ) {
                        the_post_thumbnail( 'medium' );
                    } elseif ( ! empty( $meta_values['image_url'] ) ) {
                        echo '<img src="' . esc_url( $meta_values['image_url'] ) . '" alt="' . esc_attr( get_the_title() ) . '">';
                    }
                    ?>
                </div>
                <div class="listinger-product-details">
                    <h3><a href="<?php the_permalink(); ?>"><?php echo esc_html( get_the_title() ); ?></a></h3>
                    <?php foreach ( $meta_fields as $field ) : ?>
                        <?php if ( ! empty( $meta_values[$field] ) && $field != 'image_url' ) : ?>
                            <?php
                            // Translators: %s represents the label for product meta fields like "Price", "Min Order Qty", etc.
                            ?>
                            <p><?php printf( esc_html__( '%s:', 'listinger-ecommerce' ), esc_html( ucwords( str_replace( '_', ' ', $field ) ) ) ); ?> <?php echo esc_html( $meta_values[$field] ); ?></p>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <p>
                        <?php
                        $description = get_the_content();
                        if ( strlen( $description ) > 150 ) {
                            echo esc_html( substr( $description, 0, 150 ) ) . '... ';
                            // Translators: This link text is used for truncating long descriptions and leading users to the full product details.
                            echo '<a href="' . esc_url( get_the_permalink() ) . '">' . esc_html__( 'View More', 'listinger-ecommerce' ) . '</a>';
                        } else {
                            echo esc_html( $description );
                        }
                        ?>
                    </p>
                    <div class="listinger-product-actions">
                        <?php if ( ! empty( $meta_values['mobile_number'] ) ) : ?>
                            <button class="listinger-button listinger-mobile-number" style="background-color: #ff5722;"><?php esc_html_e( '+91 ', 'listinger-ecommerce' ); ?><?php echo esc_html( $meta_values['mobile_number'] ); ?></button>
                        <?php endif; ?>
                        <button class="listinger-button listinger-contact-supplier" data-product-name="<?php echo esc_attr( get_the_title() ); ?>"><?php esc_html_e( 'Contact Supplier', 'listinger-ecommerce' ); ?></button>
                        <button class="listinger-button listinger-add-to-wishlist" data-product-id="<?php echo esc_attr( get_the_ID() ); ?>">
                            <span class="dashicons dashicons-heart"></span>
                        </button>
                        <button class="listinger-button listinger-add-to-cart" data-product-id="<?php echo esc_attr( get_the_ID() ); ?>"><?php esc_html_e( 'Add to Cart', 'listinger-ecommerce' ); ?></button>
                    </div>
                </div>
            </li>
        <?php endwhile; ?>
    </ul>
<?php else : ?>
    <p><?php esc_html_e( 'No products found.', 'listinger-ecommerce' ); ?></p>
<?php endif;

get_footer();
?>
