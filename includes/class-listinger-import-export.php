<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Listinger_SPL_Product_Import_Export {

    public function __construct() {
        // Add import/export menu.
        add_action( 'admin_menu', array( $this, 'add_import_export_menu' ) );

        // Handle file upload.
        add_action( 'admin_post_listinger_import_products', array( $this, 'import_products' ) );
        add_action( 'admin_post_listinger_export_products', array( $this, 'export_products' ) );
    }

    public function add_import_export_menu() {
        add_submenu_page(
            'edit.php?post_type=listinger_product',
            esc_html__( 'Import/Export Products', 'listinger-ecommerce' ),
            esc_html__( 'Import/Export', 'listinger-ecommerce' ),
            'manage_options',
            'listinger-spl-import-export',
            array( $this, 'render_import_export_page' )
        );
    }

    public function render_import_export_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Import/Export Products', 'listinger-ecommerce' ); ?></h1>
            <form method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <input type="hidden" name="action" value="listinger_import_products" />
                <?php wp_nonce_field( 'listinger_import_products', 'listinger_import_products_nonce' ); ?>
                <p>
                    <label for="listinger_import_file"><?php esc_html_e( 'Import CSV:', 'listinger-ecommerce' ); ?></label>
                    <input type="file" id="listinger_import_file" name="listinger_import_file" />
                </p>
                <p>
                    <input type="submit" value="<?php esc_html_e( 'Import Products', 'listinger-ecommerce' ); ?>" class="button button-primary" />
                </p>
            </form>
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <input type="hidden" name="action" value="listinger_export_products" />
                <?php wp_nonce_field( 'listinger_export_products', 'listinger_export_products_nonce' ); ?>
                <p>
                    <input type="submit" value="<?php esc_html_e( 'Export Products', 'listinger-ecommerce' ); ?>" class="button button-primary" />
                </p>
            </form>
        </div>
        <?php
    }

    public function import_products() {
        if ( ! function_exists( 'WP_Filesystem' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        WP_Filesystem();
        global $wp_filesystem;

        // Sanitize and validate nonce
        if ( ! isset( $_POST['listinger_import_products_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['listinger_import_products_nonce'] ) ), 'listinger_import_products' ) ) {
            wp_die( esc_html__( 'Security check failed', 'listinger-ecommerce' ) );
        }

        // Verify user permissions
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'listinger-ecommerce' ) );
        }

        if ( isset( $_FILES['listinger_import_file'] ) && ! empty( $_FILES['listinger_import_file']['tmp_name'] ) ) {
            $file = sanitize_text_field( $_FILES['listinger_import_file']['tmp_name'] ); // Sanitizing the file path

            if ( $wp_filesystem->exists( $file ) ) {
                $file_contents = $wp_filesystem->get_contents( $file );
                $lines = explode( "\n", $file_contents );
                $header = str_getcsv( array_shift( $lines ) );

                foreach ( $lines as $line ) {
                    if ( empty( $line ) ) {
                        continue;
                    }
                    $data = array_map( 'sanitize_text_field', str_getcsv( $line ) ); // Sanitize all CSV data
                    $post_id = intval( $data[0] );
                    if ( $post_id > 0 ) {
                        // Update existing post
                        $post_data = array(
                            'ID'           => $post_id,
                            'post_title'   => sanitize_text_field( $data[1] ),
                            'post_content' => sanitize_textarea_field( $data[2] ),
                        );
                        wp_update_post( $post_data );
                    } else {
                        // Insert new post
                        $post_data = array(
                            'post_title'   => sanitize_text_field( $data[1] ),
                            'post_content' => sanitize_textarea_field( $data[2] ),
                            'post_type'    => 'listinger_product',
                            'post_status'  => 'publish',
                        );
                        $post_id = wp_insert_post( $post_data );
                    }

                    if ( ! is_wp_error( $post_id ) ) {
                        $meta_fields = array(
                            'price', 'min_order_qty', 'packaging_size', 'type', 'brand', 'country_of_origin', 
                            'description', 'image_url', 'fragrance', 'skin_type', 'packaging_type', 'face_wash_type', 
                            'purpose', 'key_ingredients', 'third_party_manufacturing', 'shelf_life', 'mobile_number', 
                            'address', 'age_group', 'gender', 'ingredient_type', 'features', 'type_of_cream', 'gas', 
                            'organic', 'shade', 'skin_tone', 'color', 'flavor', 'form', 'usage_application', 'flavor_base', 
                            'oil_type', 'hair_oil_type', 'soap_type', 'is_it_handmade', 'finish_type', 'prescription_non_prescription'
                        );

                        foreach ( $meta_fields as $index => $field ) {
                            update_post_meta( $post_id, '_listinger_' . $field, sanitize_text_field( $data[ $index + 3 ] ) );
                        }

                        // Handle categories
                        $categories = array_map( 'sanitize_text_field', array_map( 'trim', explode( '|', $data[ count( $meta_fields ) + 3 ] ) ) );
                        if ( ! empty( $categories ) ) {
                            wp_set_object_terms( $post_id, $categories, 'listinger_product_category', false );
                        }

                        // Handle featured image
                        $featured_image_url = esc_url_raw( $data[ count( $meta_fields ) + 4 ] );
                        if ( ! empty( $featured_image_url ) ) {
                            $featured_image_id = attachment_url_to_postid( $featured_image_url );
                            if ( $featured_image_id ) {
                                set_post_thumbnail( $post_id, $featured_image_id );
                            } else {
                                // Upload the image if not found
                                $featured_image_id = $this->upload_image_from_url( $featured_image_url );
                                set_post_thumbnail( $post_id, $featured_image_id );
                            }
                        }
                    }
                }
                wp_redirect( esc_url( admin_url( 'edit.php?post_type=listinger_product' ) ) );
                exit;
            }
        }
    }

    public function export_products() {
        if ( ! isset( $_POST['listinger_export_products_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['listinger_export_products_nonce'] ) ), 'listinger_export_products' ) ) {
            wp_die( esc_html__( 'Security check failed', 'listinger-ecommerce' ) );
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'listinger-ecommerce' ) );
        }

        $products = get_posts( array(
            'post_type'   => 'listinger_product',
            'post_status' => 'publish',
            'numberposts' => -1,
            'orderby'     => 'ID',
            'order'       => 'ASC',
        ) );

        header( 'Content-Type: text/csv' );
        header( 'Content-Disposition: attachment;filename=listinger_products.csv' );

        $output = fopen( 'php://output', 'w' );

        fputcsv( $output, array( 
            'Post ID', 'Title', 'Content', 'Price', 'Min Order Qty', 'Packaging Size', 'Type', 'Brand', 'Country of Origin', 
            'Description', 'Image URL', 'Fragrance', 'Skin Type', 'Packaging Type', 'Face Wash Type', 'Purpose', 'Key Ingredients', 
            'Third Party Manufacturing', 'Shelf Life', 'Mobile Number', 'Address', 'Age Group', 'Gender', 'Ingredient Type', 
            'Features', 'Type of Cream', 'Gas', 'Organic', 'Shade', 'Skin Tone', 'Color', 'Flavor', 'Form', 'Usage Application', 
            'Flavor Base', 'Oil Type', 'Hair Oil Type', 'Soap Type', 'Is It Handmade', 'Finish Type', 'Prescription Non-prescription', 'Categories', 'Featured Image URL'
        ));

        foreach ( $products as $product ) {
            $meta_fields = array(
                'price', 'min_order_qty', 'packaging_size', 'type', 'brand', 'country_of_origin', 
                'description', 'image_url', 'fragrance', 'skin_type', 'packaging_type', 'face_wash_type', 
                'purpose', 'key_ingredients', 'third_party_manufacturing', 'shelf_life', 'mobile_number', 
                'address', 'age_group', 'gender', 'ingredient_type', 'features', 'type_of_cream', 'gas', 
                'organic', 'shade', 'skin_tone', 'color', 'flavor', 'form', 'usage_application', 'flavor_base', 
                'oil_type', 'hair_oil_type', 'soap_type', 'is_it_handmade', 'finish_type', 'prescription_non_prescription'
            );

            $meta_values = array();
            foreach ( $meta_fields as $field ) {
                $meta_values[ $field ] = esc_html( get_post_meta( $product->ID, '_listinger_' . $field, true ) );
            }

            // Get the categories
            $categories = wp_get_post_terms( $product->ID, 'listinger_product_category', array( 'fields' => 'names' ) );
            $categories = implode( '|', array_map( 'esc_html', $categories ) );

            // Get the featured image URL
            $featured_image_id = get_post_thumbnail_id( $product->ID );
            $featured_image_url = esc_url( wp_get_attachment_url( $featured_image_id ) );

            fputcsv( $output, array_merge( array( $product->ID, $product->post_title, $product->post_content ), $meta_values, array( $categories, $featured_image_url ) ) );
        }

        exit;
    }

    private function upload_image_from_url( $image_url ) {
        if ( ! function_exists( 'WP_Filesystem' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        WP_Filesystem();
        global $wp_filesystem;

        $upload_dir = wp_upload_dir();
        $response = wp_remote_get( esc_url_raw( $image_url ) );
        
        if ( is_wp_error( $response ) ) {
            return;
        }
        
        $image_data = wp_remote_retrieve_body( $response );
        $filename = sanitize_file_name( basename( $image_url ) );
        
        if ( wp_mkdir_p( $upload_dir['path'] ) ) {
            $file = $upload_dir['path'] . '/' . $filename;
        } else {
            $file = $upload_dir['basedir'] . '/' . $filename;
        }

        $wp_filesystem->put_contents( $file, $image_data, FS_CHMOD_FILE );

        $wp_filetype = wp_check_filetype( $filename, null );
        $attachment = array(
            'post_mime_type' => $wp_filetype['type'],
            'post_title' => sanitize_file_name( $filename ),
            'post_content' => '',
            'post_status' => 'inherit'
        );
        
        $attach_id = wp_insert_attachment( $attachment, $file );
        require_once ABSPATH . 'wp-admin/includes/image.php';
        $attach_data = wp_generate_attachment_metadata( $attach_id, $file );
        wp_update_attachment_metadata( $attach_id, $attach_data );

        return $attach_id;
    }
}
