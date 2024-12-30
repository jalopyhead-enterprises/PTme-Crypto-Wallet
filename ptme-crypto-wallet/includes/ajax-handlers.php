<?php

// Save Coin Selections
function save_user_coin_selector() {
    check_ajax_referer('papertrademe_nonce', 'nonce');

    $user_id = get_current_user_id();
    if (!$user_id) {
        wp_send_json_error('User not authenticated.');
    }

    $coins = isset($_POST['coins']) ? array_map('sanitize_text_field', $_POST['coins']) : [];

    if (update_user_meta($user_id, 'user_coin_dashboard', $coins)) {
        wp_send_json_success('Your coins have been updated successfully!');
    } else {
        wp_send_json_error('Failed to update coin selection.');
    }
}

add_action('wp_ajax_save_user_coin_selector', 'save_user_coin_selector');

function ajax_sort_coin_dashboard() {
    check_ajax_referer('papertrademe_nonce', 'nonce');

    $sort_type = sanitize_text_field($_POST['sort_type']);
    $sort_order = sanitize_text_field($_POST['sort_order']);

    $user_id = get_current_user_id();
    $user_coins = get_user_meta($user_id, 'user_coin_dashboard', true);

    if (!is_array($user_coins) || empty($user_coins)) {
        wp_send_json_error('No coins selected.');
    }

    $coins_data = CoinBridge::get_all_coins();
    $filtered_coins = array_filter($coins_data, function ($coin) use ($user_coins) {
        return in_array($coin['id'], $user_coins, true);
    });

    usort($filtered_coins, function ($a, $b) use ($sort_type, $sort_order) {
        $result = 0;

        switch ($sort_type) {
            case 'alphabetical':
                $result = strcasecmp($a['name'], $b['name']);
                break;
            case 'price_change':
                $result = abs($b['price_change_percentage_24h']) <=> abs($a['price_change_percentage_24h']);
                break;
            case 'market_cap':
                $result = $b['market_cap'] <=> $a['market_cap'];
                break;
        }

        return $sort_order === 'asc' ? $result : -$result;
    });

    ob_start();
    foreach ($filtered_coins as $coin) {
        ?>
        <li class="coin-dashboard-item">
            <a href="<?php echo esc_url(get_post_type_archive_link('ptme_wallet_coin') . $coin['id'] . '/'); ?>" class="coin-dashboard-link">
                <img src="<?php echo esc_url($coin['image']); ?>" alt="<?php echo esc_attr($coin['name']); ?> logo" class="coin-logo">
                <h3 class="coin-name"><?php echo esc_html($coin['name']); ?> (<?php echo strtoupper(esc_html($coin['symbol'])); ?>)</h3>
                <p class="coin-price">Price: $<?php echo number_format($coin['current_price'], 2); ?></p>
                <p class="coin-market-cap">Market Cap: <?php echo number_format($coin['market_cap']); ?></p>
                <p class="coin-change" style="color:<?php echo ($coin['price_change_percentage_24h'] >= 0 ? 'green' : 'red'); ?>;">
                    24h Change: <?php echo number_format($coin['price_change_percentage_24h'], 2); ?>%
                </p>
            </a>
        </li>
        <?php
    }
    $coins_html = ob_get_clean();

    wp_send_json_success(['coins_html' => $coins_html]);
}

add_action('wp_ajax_sort_coin_dashboard', 'ajax_sort_coin_dashboard');

function poll_coin_data() {
    check_ajax_referer('papertrademe_nonce', 'nonce');

    // Get current cache timestamp
    $last_cached_timestamp = get_transient('global_coins_last_cached');
    $cache_duration = CoinBridge::CACHE_EXPIRATION;
    $next_update = $last_cached_timestamp ? $last_cached_timestamp + $cache_duration : time();

    // Check if the cache has expired
    $is_expired = $next_update <= time();

    wp_send_json_success([
        'is_expired' => $is_expired,
        'next_update' => $next_update,
    ]);
}
add_action('wp_ajax_poll_coin_data', 'poll_coin_data');

function fetch_updated_coin_data() {
    check_ajax_referer('papertrademe_nonce', 'nonce');

    $coins_data = CoinBridge::fetch_and_cache_coins();
    $last_cached_timestamp = get_transient('global_coins_last_cached');
    $cache_duration = CoinBridge::CACHE_EXPIRATION;
    $is_cache_ready = $last_cached_timestamp && (time() - $last_cached_timestamp < $cache_duration);

    if (!$coins_data || !$is_cache_ready) {
        wp_send_json_error(['cache_ready' => false, 'message' => 'Cache is not ready yet.']);
    }

    // Add formatted data for consistency
    foreach ($coins_data as &$coin) {
        $coin['formatted_price'] = number_format($coin['current_price'], 2);
        $coin['formatted_market_cap'] = number_format($coin['market_cap']);
        $coin['formatted_price_change'] = number_format($coin['price_change_percentage_24h'], 2);
    }

    wp_send_json_success([
        'cache_ready' => true,
        'coins' => $coins_data,
        'last_updated' => $last_cached_timestamp,
    ]);
}

add_action('wp_ajax_fetch_updated_coin_data', 'fetch_updated_coin_data');
