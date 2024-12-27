<?php
class CoinBridge {
    const CACHE_KEY = 'global_coins_data';
    const CACHE_EXPIRATION = 300; // 5 minutes

    public static function fetch_and_cache_coins() {
        $cached_data = get_transient(self::CACHE_KEY);

        if ($cached_data === false) {
            $coin_slugs = self::get_coin_slugs_from_cpt();
            if (empty($coin_slugs)) return false;

            $url = 'https://api.coingecko.com/api/v3/coins/markets?vs_currency=usd&ids=' . urlencode(implode(',', $coin_slugs));
            $response = wp_remote_get($url);

            if (is_wp_error($response)) return false;

            $data = json_decode(wp_remote_retrieve_body($response), true);
            if (empty($data)) return false;

            set_transient(self::CACHE_KEY, $data, self::CACHE_EXPIRATION);
            set_transient('global_coins_last_cached', time(), self::CACHE_EXPIRATION);
            return $data;
        }

        return $cached_data;
    }

    public static function get_all_coins() {
        return self::fetch_and_cache_coins() ?: [];
    }

    public static function get_coin($coin_id) {
        $coins = self::get_all_coins();
        foreach ($coins as $coin) {
            if ($coin['id'] === $coin_id) return $coin;
        }
        return null;
    }

    public static function get_user_coins($user_id) {
        $user_coins = get_user_meta($user_id, 'user_coin_dashboard', true) ?: [];
        $all_coins = self::get_all_coins();
        return array_filter($all_coins, fn($coin) => in_array($coin['id'], $user_coins));
    }

    private static function get_coin_slugs_from_cpt() {
        $args = [
            'post_type' => 'ptme_wallet_coin',
            'posts_per_page' => -1,
            'fields' => 'ids',
        ];
        $query = new WP_Query($args);
        $slugs = array_map(fn($post_id) => get_post_field('post_name', $post_id), $query->posts);
        wp_reset_postdata();
        return $slugs;
    }
}
?>