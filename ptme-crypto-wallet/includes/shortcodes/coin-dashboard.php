<?php

function coin_dashboard_list_simple_shortcode() {
    $coins_data = CoinBridge::get_all_coins();  // Use the CoinBridge system

    if (!$coins_data) {
        return '<p>Error: No cached data available. Please refresh the cache or try again later.</p>';
    }

    $user_id = get_current_user_id();
    $user_coins = get_user_meta($user_id, 'user_coin_dashboard', true);

    if (!is_array($user_coins) || empty($user_coins)) {
        return '<p>No coins selected in your wallet. <a href="' . esc_url(site_url('/wallet-settings')) . '">Manage your coins</a>.</p>';
    }

    $filtered_coins = array_filter($coins_data, function ($coin) use ($user_coins) {
        return in_array($coin['id'], $user_coins, true); 
    });

    // Cache handling with centralized duration
    $last_cached_timestamp = get_transient('global_coins_last_cached');
    $cache_duration = CoinBridge::CACHE_EXPIRATION; // Single source of truth
    $next_update = $last_cached_timestamp 
        ? $last_cached_timestamp + $cache_duration 
        : time() + $cache_duration;
		$last_cached = $last_cached_timestamp 
		    ? date('F j, Y, g:i:s a', $last_cached_timestamp) // Include seconds and lowercase am/pm
		    : 'Not available';


    $output = '<div id="coin-dashboard-container" class="coin-dashboard" data-default-sort="alphabetical" data-default-order="asc" data-next-update="' . $next_update . '">';

    // **Sorting Links Section**
    $output .= '<div id="coin-sort-links" class="coin-sort-links">';
    $output .= '<a href="#" class="sort-link" data-sort="alphabetical" data-order="desc">';
    $output .= 'A-Z <span class="dashicons dashicons-sort"></span></a> | ';
    $output .= '<a href="#" class="sort-link" data-sort="price_change" data-order="desc">';
    $output .= 'Price % <span class="dashicons dashicons-sort"></span></a> | ';
    $output .= '<a href="#" class="sort-link" data-sort="market_cap" data-order="desc">';
    $output .= 'Market Cap <span class="dashicons dashicons-sort"></span></a>';
    $output .= '</div>';

    $output .= '<ul id="coin-dashboard-grid" class="coin-dashboard-grid">';
foreach ($filtered_coins as $coin) {
    $coin_url = esc_url(get_post_type_archive_link('ptme_wallet_coin') . $coin['id'] . '/');

    $output .= '<li class="coin-dashboard-item" data-name="' . esc_attr($coin['name']) . '" ';
    $output .= 'data-price="' . esc_attr($coin['current_price']) . '" ';
    $output .= 'data-market-cap="' . esc_attr($coin['market_cap']) . '" ';
    $output .= 'data-price-change="' . esc_attr(abs($coin['price_change_percentage_24h'])) . '">';
    $output .= '<a href="' . $coin_url . '" class="coin-dashboard-link">';
    $output .= '<img src="' . esc_url($coin['image']) . '" alt="' . esc_attr($coin['name']) . ' logo" class="coin-logo">';
    $output .= '<h3 class="coin-name">' . esc_html($coin['name']) . ' (' . strtoupper(esc_html($coin['symbol'])) . ')</h3>';
    $output .= '<p class="coin-price">Price: $' . esc_html($coin['current_price']) . '</p>';
    $output .= '<p class="coin-market-cap">Market Cap: ' . esc_html($coin['market_cap']) . '</p>';
    $output .= '<p class="coin-change" style="color:' . ($coin['price_change_percentage_24h'] >= 0 ? 'green' : 'red') . ';">';
    $output .= '24h Change: ' . esc_html($coin['price_change_percentage_24h']) . '%</p>';
    $output .= '</a>';
    $output .= '</li>';
}

    $output .= '</ul>';

    $output .= '<div class="coin-dashboard-footer">';
	$output .= '<p><strong>Last Updated:</strong> <span id="last-updated-time">' . esc_html($last_cached) . '</span></p>'; // Add span with ID
	$output .= '<form action="' . esc_url(site_url('/wallet-settings')) . '" method="get">';
	$output .= '<button type="submit" class="manage-wallet-btn">Manage Coin Dashboard</button>';
	$output .= '</form>';
	$output .= '</div>';

    $output .= '</div>'; 

    return $output;
}

add_shortcode('coin_dashboard_list_simple', 'coin_dashboard_list_simple_shortcode');
