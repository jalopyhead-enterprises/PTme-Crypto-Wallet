<?php
// Register the Custom Post Type (CPT) for Coins
function ptme_wallet_register_coin_cpt() {
    $args = [
        'labels' => [
            'name' => 'Coins',
            'singular_name' => 'Coin',
            'add_new' => 'Add New Coin',
            'add_new_item' => 'Add New Coin',
            'edit_item' => 'Edit Coin',
            'new_item' => 'New Coin',
            'view_item' => 'View Coin',
            'search_items' => 'Search Coins',
            'not_found' => 'No Coins Found',
            'not_found_in_trash' => 'No Coins Found in Trash',
            'all_items' => 'All Coins',
            'menu_name' => 'Coins',
            'name_admin_bar' => 'Coin',
        ],
        'public' => true,
        'has_archive' => true,
        'rewrite' => ['slug' => 'coin'],
        'supports' => ['title', 'editor', 'thumbnail'],
        'show_in_rest' => true,
        'menu_icon' => 'dashicons-money-alt',
    ];

    register_post_type('ptme_wallet_coin', $args);
}
add_action('init', 'ptme_wallet_register_coin_cpt');
