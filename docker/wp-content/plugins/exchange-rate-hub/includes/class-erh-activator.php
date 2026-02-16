<?php
// Handles plugin activation: create custom tables, schedule events, etc.
class ERH_Activator {
    public static function activate() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $rates_table = $wpdb->prefix . 'erh_rates';
        $history_table = $wpdb->prefix . 'erh_rates_history';
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        // Latest rates table
        $sql1 = "CREATE TABLE $rates_table (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            base_currency varchar(3) NOT NULL,
            rates text NOT NULL,
            last_updated datetime NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY base_currency (base_currency),
            KEY last_updated (last_updated)
        ) $charset_collate;";

        // History table
        $sql2 = "CREATE TABLE $history_table (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            base_currency varchar(3) NOT NULL,
            rates text NOT NULL,
            fetched_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY base_currency (base_currency),
            KEY fetched_at (fetched_at)
        ) $charset_collate;";

        dbDelta($sql1);
        dbDelta($sql2);

        // Set default options if not already set
        if (!get_option('erh_base_currency')) {
            add_option('erh_base_currency', 'USD');
        }

        if (!get_option('erh_enabled_currencies')) {
            add_option('erh_enabled_currencies', ['EUR', 'GBP', 'JPY', 'CAD', 'AUD']);
        }

        if (!get_option('erh_update_frequency')) {
            add_option('erh_update_frequency', 'hourly');
        }

        if (!get_option('erh_api_key')) {
            add_option('erh_api_key', '');
        }

        // Schedule cron event if not already scheduled
        $frequency = get_option('erh_update_frequency', 'hourly');
        if (!wp_next_scheduled('erh_cron_update_rates')) {
            wp_schedule_event(time(), $frequency, 'erh_cron_update_rates');
        }

        // Fetch initial rates
        require_once(ERH_PLUGIN_DIR . 'includes/class-erh-api.php');
        require_once(ERH_PLUGIN_DIR . 'includes/class-erh-data.php');
        require_once(ERH_PLUGIN_DIR . 'includes/class-erh-cron.php');

        ERH_Cron::update_rates();

        // Store activation timestamp
        update_option('erh_activated_at', current_time('mysql'));
    }
}
