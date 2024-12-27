<?php

function render_coin_selector_page() {
    // Fetch cached coins data from the CoinBridge
    $coins_data = CoinBridge::get_all_coins();

    if (!$coins_data || !is_array($coins_data)) {
        return '<p>Error loading wallet settings. Please try again later.</p>';
    }

    // Sort coins alphabetically by name
    usort($coins_data, function($a, $b) {
        return strcasecmp($a['name'], $b['name']);
    });

    // Get the user's saved coins
    $user_id = get_current_user_id();
    $user_coins = get_user_meta($user_id, 'user_coin_dashboard', true);
    if (!is_array($user_coins)) {
        $user_coins = [];
    }

    // Build the form interface
    $output = '<form id="coin-selector-form">';
    $output .= '<ul id="coin-selector-list" class="coin-selector-list">';

    foreach ($coins_data as $coin) {
        $coin_id = $coin['id'];         
        $coin_name = $coin['name'];     
        $coin_symbol = strtoupper($coin['symbol']);  

        $display_text = esc_html("{$coin_name} ({$coin_symbol})");
        $checked = in_array($coin_id, $user_coins) ? 'checked' : '';

        $output .= '<li data-coin-id="' . esc_attr($coin_id) . '" class="coin-selector-item">';
        $output .= '<label>';
        $output .= '<input type="checkbox" class="coin-selector-checkbox" ' . $checked . '>';
        $output .= $display_text;
        $output .= '</label>';
        $output .= '</li>';
    }

    $output .= '</ul>';
    $output .= '<button type="submit" id="save-coin-selector" class="coin-selector-btn">Save</button>';
    $output .= '<div id="coin-selector-message" class="coin-selector-message" style="display: none;"></div>';
    $output .= '</form>';

    return $output;
}

add_shortcode('coin_selector', 'render_coin_selector_page');
