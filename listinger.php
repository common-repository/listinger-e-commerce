<?php
/**
 * Plugin Name: Listinger E-commerce
 * Description: A comprehensive e-commerce product listing plugin for WordPress, featuring categories like daycare, deodorant, and more. Easily manage and display a variety of products with custom fields, and facilitate a smooth shopping experience for your customers.
 * Version: 1.0
 * Author: Weblogix Team
 * Author URI: https://weblogixsoft.com
 * Plugin URI: https://weblogixsoft.com/listinger-ecommerce/
 * Text Domain: listinger-ecommerce
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

define( 'LISTINGER_ECOMMERCE_VERSION', '1.0' );
define( 'LISTINGER_ECOMMERCE_DIR', plugin_dir_path( __FILE__ ) );
define( 'LISTINGER_ECOMMERCE_URL', plugin_dir_url( __FILE__ ) );

// Include necessary files
require_once LISTINGER_ECOMMERCE_DIR . 'includes/class-listinger-main.php';
require_once LISTINGER_ECOMMERCE_DIR . 'includes/class-listinger-import-export.php';
require_once LISTINGER_ECOMMERCE_DIR . 'includes/class-listinger-orders.php';

// Initialize the plugin
new Listinger_Simple_Product_Listing();
new Listinger_SPL_Product_Import_Export();
new Listinger_Orders();

function listinger_enqueue_scripts() {
    // Register and enqueue JS files
    wp_register_script( 'listinger-cart', plugins_url( 'assets/js/listinger-cart.js', __FILE__ ), array( 'jquery' ), null, true );
    wp_register_script( 'listinger-scripts', plugins_url( 'assets/js/scripts.js', __FILE__ ), array( 'jquery' ), null, true );
    
    wp_enqueue_script( 'listinger-cart' );
    wp_enqueue_script( 'listinger-scripts' );

    // Register and enqueue CSS files
    wp_register_style( 'listinger-styles', plugins_url( 'assets/css/styles.css', __FILE__ ) );
    wp_enqueue_style( 'listinger-styles' );

    // Localize script for AJAX with sanitized and validated data
    wp_localize_script( 'listinger-cart', 'listinger_ajax', array(
        'ajax_url' => esc_url( admin_url( 'admin-ajax.php' ) ),
        'nonce' => wp_create_nonce( 'listinger_ajax_nonce' ),
        'checkout_url' => esc_url( home_url('/listinger-checkout') ),
    ));
}
add_action( 'wp_enqueue_scripts', 'listinger_enqueue_scripts' );
