<?php
// Handles scheduled updates (cron)
class ERH_Cron {
    /**
     * Initialize cron hooks
     */
    public static function init() {
        add_action('erh_cron_update_rates', [__CLASS__, 'update_rates']);
    }

    /**
     * Update exchange rates from API
     *
     * @return bool Success status
     */
    public static function update_rates() {
        $base = get_option('erh_base_currency', 'USD');
        $symbols = get_option('erh_enabled_currencies', ['EUR', 'GBP', 'JPY']);

        // Fetch rates from API
        $rates = ERH_API::fetch_rates($base, $symbols);

        if ($rates && is_array($rates) && !empty($rates)) {
            // Save to database
            $saved = ERH_Data::save_latest_rates($base, $rates);
            $history_saved = ERH_Data::save_history($base, $rates);

            if ($saved && $history_saved) {
                update_option('erh_last_successful_update', current_time('mysql'));

                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log('[Exchange Rate Hub] Successfully updated ' . count($rates) . ' rates for ' . $base);
                }

                return true;
            } else {
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log('[Exchange Rate Hub] Failed to save rates to database');
                }
            }
        } else {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('[Exchange Rate Hub] Failed to fetch rates from API');
            }
        }

        return false;
    }
}

// Initialize cron hooks
ERH_Cron::init();
