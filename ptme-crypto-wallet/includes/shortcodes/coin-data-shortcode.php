<?php

function display_coin_data_in_cpt($atts) {
    $post = get_post();
    $coin_data = CoinBridge::get_coin($post->post_name);

    if (!$coin_data) {
        return '<p>Error: Coin data not available for this post.</p>';
    }

    $last_cached_timestamp = get_transient('global_coins_last_cached');
    $last_cached = $last_cached_timestamp
        ? date('F j, Y, g:i a', $last_cached_timestamp)
        : 'Not available';

    $output = '<div class="coin-data" style="border: 1px solid #ddd; border-radius: 5px; padding: 15px; background-color: #f9f9f9;">';
    $output .= '<h3>' . esc_html($coin_data['name']) . ' (' . esc_html(strtoupper($coin_data['symbol'])) . ')</h3>';
    $output .= '<img src="' . esc_url($coin_data['image']) . '" alt="' . esc_attr($coin_data['name']) . ' logo" style="width: 50px; height: 50px; margin-bottom: 10px;">';

    $fields = [
        'Current Price' => 'current_price',
        'Market Cap' => 'market_cap',
        '24h High' => 'high_24h',
        '24h Low' => 'low_24h',
        'Price Change (24h)' => 'price_change_24h',
        'Price Change Percentage (24h)' => 'price_change_percentage_24h',
        'Circulating Supply' => 'circulating_supply',
        'Total Supply' => 'total_supply',
        'Max Supply' => 'max_supply',
    ];

    $output .= '<ul style="list-style: none; padding: 0;">';
    foreach ($fields as $label => $field) {
        $value = $coin_data[$field] ?? 'N/A';

        if (is_numeric($value)) {
            $value = number_format($value, 2);
        }

        if (str_contains($field, 'percentage')) {
            $color = $coin_data[$field] >= 0 ? 'green' : 'red';
            $value = '<span style="color: ' . $color . ';">' . ($coin_data[$field] >= 0 ? '+' : '') . $value . '%</span>';
        }

        $output .= '<li><strong>' . esc_html($label) . ':</strong> ' . $value . '</li>';
    }
    $output .= '</ul>';

    $output .= '<div style="display: flex; justify-content: space-between; align-items: center; font-size: 14px; color: #666;">';
    $output .= '<a href="' . esc_url(home_url('/coin-dashboard')) . '" style="color: #0073aa; text-decoration: none;">View Coin Dashboard</a>';
    $output .= '<p><strong>Last Updated:</strong> ' . esc_html($last_cached) . '</p>';
    $output .= '</div>';
    $output .= '</div>';

    return $output;
}

add_shortcode('coin_data', 'display_coin_data_in_cpt');

// Append coin data automatically to CPT posts
function append_coin_data_to_cpt($content) {
    if (is_singular('ptme_wallet_coin')) {
        $content .= do_shortcode('[coin_data]');
    }
    return $content;
}

add_filter('the_content', 'append_coin_data_to_cpt');
