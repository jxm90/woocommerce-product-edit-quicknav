<?php
/**
 * Plugin Name: WooCommerce Product Navigation with Save and Continue Editing
 * Plugin URI: https://computerguyjoe.com
 * Description: Adds Previous product navigation, a "Save & Continue" button, and a 3-column product list with previews in the WooCommerce product edit screen.
 * Version: 1.3
 * Author: Joe
 * Author URI: https://computerguyjoe.com
 * License: GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Add the "Previous Product" and "Save & Continue" buttons
function add_navigation_buttons_to_product_edit_page() {
    global $post;

    // Get all products, ordered by ID
    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => -1, // Get all products
        'orderby'        => 'ID',
        'order'          => 'ASC',
        'fields'         => 'ids' // Only get the IDs of products
    );
    $product_ids = get_posts($args);

    // Find the current product's position in the list of product IDs
    $current_product_index = array_search($post->ID, $product_ids);

    echo '<div class="product-navigation-buttons" style="padding-top: 10px;">';

    // Check if there's a previous product and create a button
    if ( isset($product_ids[$current_product_index - 1]) ) {
        $previous_product_id = $product_ids[$current_product_index - 1];
        $previous_product_url = get_edit_post_link($previous_product_id);
        
        // Add the "Previous Product" button
        echo '<a href="' . esc_url($previous_product_url) . '" class="button button-primary" style="margin-right: 10px;">Previous Product</a>';
    }

    // Check if there's a next product and create the "Save & Continue" button
    if ( isset($product_ids[$current_product_index + 1]) ) {
        $next_product_id = $product_ids[$current_product_index + 1];
        $next_product_url = get_edit_post_link($next_product_id);

        // Add the "Save & Continue" button
        echo '<button type="submit" name="save_and_continue" class="button button-primary">Save & Continue</button>';
        echo '<input type="hidden" name="next_product_url" value="' . esc_url($next_product_url) . '">';
    }

    echo '</div>';
}
add_action('woocommerce_product_options_general_product_data', 'add_navigation_buttons_to_product_edit_page');

// Redirect to the next product after saving if "Save & Continue" was clicked
function handle_save_and_continue_redirect( $post_id ) {
    // Check if the form submission was triggered by the "Save & Continue" button
    if ( isset($_POST['save_and_continue']) && !empty($_POST['next_product_url']) ) {
        // Redirect to the next product edit page
        wp_redirect( esc_url_raw( $_POST['next_product_url'] ) );
        exit;
    }
}
add_action('save_post', 'handle_save_and_continue_redirect');

// Add the product list with images below the buttons
function add_filtered_product_list() {
    global $post;

    // Get all products, ordered by ID
    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => -1, // Get all products
        'orderby'        => 'ID',
        'order'          => 'ASC',
        'post_status'    => 'publish', // Only published (active) products
    );

    $products = get_posts($args);

    // Add the product list with images in a 3-column layout
    echo '<div class="product-list">';
    echo '<h3>Quick Navigation</h3>';
    echo '<table style="width:100%;">'; // Use a table to create columns

    $column_count = 0; // Keep track of columns

    foreach ( $products as $product ) {
        $product_edit_url = get_edit_post_link($product->ID);
        $thumbnail = get_the_post_thumbnail($product->ID, array(50, 50)); // Get thumbnail image (size 50x50)

        // Open a new row if starting a new set of columns
        if ($column_count % 3 == 0) {
            echo '<tr>';
        }

        // Display product in a table cell
        echo '<td style="width:33%; padding: 10px;">';
        echo '<a href="' . esc_url($product_edit_url) . '">';
        if ($thumbnail) {
            echo $thumbnail . ' '; // Display thumbnail
        }
        echo esc_html($product->post_title);
        echo '</a></td>';

        // Close the row after the third column
        if ($column_count % 3 == 2) {
            echo '</tr>';
        }

        $column_count++;
    }

    // Close any unclosed row
    if ($column_count % 3 != 0) {
        echo '</tr>';
    }

    echo '</table>';
    echo '</div>';
}
add_action('woocommerce_product_options_general_product_data', 'add_filtered_product_list');
