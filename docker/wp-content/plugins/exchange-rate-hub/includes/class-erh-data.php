<?php
// Handles data storage and retrieval
class ERH_Data {
    /**
     * Save latest rates to database and clear cache
     *
     * @param string $base Base currency code
     * @param array $rates Rates array
     * @return bool Success status
     */
    public static function save_latest_rates($base, $rates) {
        global $wpdb;
        $table = $wpdb->prefix . 'erh_rates';

        $result = $wpdb->replace($table, [
            'base_currency' => sanitize_text_field($base),
            'rates' => maybe_serialize($rates),
            'last_updated' => current_time('mysql', 1)
        ]);

        // Clear cache when new data is saved
        if ($result) {
            delete_transient('erh_rates_' . $base);
            delete_transient('erh_rates_formatted_' . $base);
        }

        return (bool) $result;
    }

    /**
     * Save rates to history table
     *
     * @param string $base Base currency code
     * @param array $rates Rates array
     * @return bool Success status
     */
    public static function save_history($base, $rates) {
        global $wpdb;
        $table = $wpdb->prefix . 'erh_rates_history';

        $result = $wpdb->insert($table, [
            'base_currency' => sanitize_text_field($base),
            'rates' => maybe_serialize($rates),
            'fetched_at' => current_time('mysql', 1)
        ]);

        return (bool) $result;
    }

    /**
     * Get latest rates with caching
     *
     * @param string $base Base currency code
     * @return object|null Rates data or null
     */
    public static function get_latest_rates($base) {
        $cache_key = 'erh_rates_' . $base;

        // Try to get from cache first
        $cached = get_transient($cache_key);
        if ($cached !== false) {
            return $cached;
        }

        // Fetch from database
        global $wpdb;
        $table = $wpdb->prefix . 'erh_rates';
        $result = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE base_currency = %s", $base));

        // Cache for 1 hour
        if ($result) {
            set_transient($cache_key, $result, HOUR_IN_SECONDS);
        }

        return $result;
    }

    /**
     * Get formatted rates array for display
     *
     * @param string $base Base currency code
     * @return array|false Formatted rates or false on failure
     */
    public static function get_formatted_rates($base) {
        $cache_key = 'erh_rates_formatted_' . $base;

        // Try cache first
        $cached = get_transient($cache_key);
        if ($cached !== false) {
            return $cached;
        }

        $rates_data = self::get_latest_rates($base);

        if (!$rates_data || empty($rates_data->rates)) {
            return false;
        }

        $rates = maybe_unserialize($rates_data->rates);
        $formatted = [
            'base' => $base,
            'rates' => $rates,
            'last_updated' => $rates_data->last_updated,
            'timestamp' => strtotime($rates_data->last_updated)
        ];

        // Cache for 1 hour
        set_transient($cache_key, $formatted, HOUR_IN_SECONDS);

        return $formatted;
    }

    /**
     * Clear all rate caches
     *
     * @return void
     */
    public static function clear_all_caches() {
        global $wpdb;
        $table = $wpdb->prefix . 'erh_rates';

        $bases = $wpdb->get_col("SELECT DISTINCT base_currency FROM $table");

        foreach ($bases as $base) {
            delete_transient('erh_rates_' . $base);
            delete_transient('erh_rates_formatted_' . $base);
        }
    }
}
