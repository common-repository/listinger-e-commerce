=== Listinger E-commerce ===
Contributors: Listinger Team
Tags: products, ecommerce, import, export
Requires at least: 5.0
Tested up to: 6.6
Stable tag: 1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A comprehensive e-commerce product listing plugin with import/export options.


== Description ==

A comprehensive e-commerce product listing plugin for WordPress, featuring categories like daycare, deodorant, and more. This plugin helps manage and display various products with custom fields, facilitating a smooth shopping experience for customers.

Demo : https://cheapfmcgwholesale.com



== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Use the Products menu to add and manage your products.
4. Use the Import/Export submenu to import or export products.

== Frequently Asked Questions ==

= What is Listinger E-commerce? =

Listinger E-commerce is a WordPress plugin designed for listing and managing products with advanced custom fields. It enables product categories, AJAX-based cart and checkout functionality, custom templates, and seamless product import/export features.

= How do I add products to my site using Listinger E-commerce? =

Once the plugin is installed and activated:

* Navigate to the Listinger menu in the WordPress dashboard.
* Click Add New Product.
* Fill out the product details, including title, description, price, images, and custom fields (such as brand, packaging size, etc.).
* Click Publish to make the product live on your website.

= What shortcodes are available in the Listinger E-commerce plugin? =

The plugin provides the following shortcodes:

* [listinger_product_list]: Displays a list of products.
* [listinger_product]: Displays a single product's details.
* [listinger_product_urls]: Displays a list of product URLs.
* [listinger_cart]: Shows the cart contents.
* [listinger_checkout]: Displays the checkout form.
* [listinger_checkout_button]: Adds a "Proceed to Checkout" button.

= How can I display product URLs in columns? =

Use the [listinger_product_urls] shortcode to list product URLs. You can customize the layout using these attributes:

* category: To filter by category.
* columns: Number of columns (default is 1).
* links: Number of links to show (default is 9).

[listinger_product_urls category="skincare" columns="3" links="6"]


=  Can I create custom product categories? = 

Yes, you can create custom product categories. The plugin provides a dedicated taxonomy for product categories. You can:

Navigate to Products > Categories.
Add new categories or manage existing ones.
Organize products into these categories for easier navigation.

= How do I display a list of products on my site? = 

You can display a list of products using the shortcode [listinger_product_list]. To display products from a specific category, you can pass a category attribute.

= How can customers add products to their cart? =

Customers can add products to their cart using the Add to Cart button, which is automatically rendered with each product listing. The cart functionality is handled via AJAX, providing a smooth, page-refresh-free experience.

= Does Listinger E-commerce support product import and export? =

Yes, the plugin includes a product import and export system, allowing you to manage your product data in bulk. You can easily import product lists from a CSV file or export the current product list for backup or transfer to another site.

= How do I customize the product pages? =

Listinger E-commerce allows for customization of product templates. The following templates can be overridden:

single-listinger_product.php for single product pages.
archive-listinger_product.php for product archive pages.
taxonomy-listinger_product_category.php for product category pages.

= How can I view all orders placed through the plugin? =

To manage orders, the plugin includes a custom post type for orders. You can:

Navigate to Orders under the Listinger menu in the WordPress dashboard.
View, manage, and update the status of orders placed through your site.

= Can I use this plugin with WooCommerce? =

Listinger E-commerce is designed to work independently from WooCommerce, providing its own custom product listing and cart functionality. However, it can be used alongside WooCommerce if needed, although it might require additional configuration to avoid conflicts.

= Does Listinger E-commerce support product variations? =

At this stage, the plugin does not support complex product variations (like size or color). However, you can manually create separate products for different variations or customize the plugin to add such features.

= Is it possible to add custom fields to product categories? =

Yes, the plugin provides the ability to add custom fields to product categories, such as a Custom Slug for SEO-friendly URLs. You can manage this from the category creation or editing screens.

= How do I enable checkout functionality? =

The plugin includes a shortcode for displaying the checkout form: [listinger_checkout]. You can add this to a dedicated page to create a checkout process. The form handles customer details like name, email, and address.

= Can I add custom slugs to product categories for better SEO? =

Yes, the plugin allows adding custom slugs to product categories. This feature enables you to define SEO-friendly URLs for each category.

= Is it support elementor, wp bakery, gutenberg block editor or any other builder?  =

Yes, plugin support on all page builder and default classic editor.




== Screenshots ==

1. Home page
2. Product Listing 
3. Single product view
3. Checkout
4. Thank you
5. Product List - backend
6. Add new product
7. Product categories
8. orders
9. import export

== Changelog ==

= 1.0 =
* Initial release.

== Upgrade Notice ==

= 1.0 =
Initial release.
