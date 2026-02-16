<?php
/**
 * Manual rate fetch script
 * Access this file directly to fetch rates immediately
 * Example: http://localhost:8000/wp-content/plugins/exchange-rate-hub/fetch-rates-now.php
 */

// Load WordPress
require_once('../../../wp-load.php');

// Check if user is admin
if (!current_user_can('manage_options')) {
    wp_die('You do not have permission to access this page.');
}

// Load required classes
require_once(dirname(__FILE__) . '/includes/class-erh-api.php');
require_once(dirname(__FILE__) . '/includes/class-erh-data.php');
require_once(dirname(__FILE__) . '/includes/class-erh-cron.php');

echo '<h1>Exchange Rate Hub - Manual Fetch</h1>';
echo '<hr>';

// Fetch rates
echo '<p><strong>Fetching exchange rates...</strong></p>';

$base = get_option('erh_base_currency', 'USD');
$symbols = get_option('erh_enabled_currencies', ['EUR', 'GBP', 'JPY']);

echo '<p>Base Currency: ' . esc_html($base) . '</p>';
echo '<p>Target Currencies: ' . esc_html(implode(', ', $symbols)) . '</p>';
echo '<hr>';

$rates = ERH_API::fetch_rates($base, $symbols);

if ($rates && is_array($rates) && !empty($rates)) {
    echo '<p style="color: green;"><strong>✓ Successfully fetched ' . count($rates) . ' rates!</strong></p>';
    echo '<pre>' . print_r($rates, true) . '</pre>';

    // Save to database
    $saved = ERH_Data::save_latest_rates($base, $rates);
    $history_saved = ERH_Data::save_history($base, $rates);

    if ($saved && $history_saved) {
        echo '<p style="color: green;"><strong>✓ Rates saved to database!</strong></p>';
        update_option('erh_last_successful_update', current_time('mysql'));
        echo '<p><a href="/wp-admin/admin.php?page=exchange-rate-hub">View in Admin</a></p>';
        echo '<p><strong>Now you can test your shortcode: [exchange_rates]</strong></p>';
    } else {
        echo '<p style="color: red;"><strong>✗ Failed to save rates to database</strong></p>';
    }
} else {
    echo '<p style="color: red;"><strong>✗ Failed to fetch rates from API</strong></p>';

    // Check for errors
    $error = ERH_API::get_last_error();
    if ($error) {
        echo '<p><strong>Error:</strong> ' . esc_html($error['message']) . '</p>';
        echo '<p><strong>Time:</strong> ' . esc_html($error['timestamp']) . '</p>';
    }
}

echo '<hr>';
echo '<p><a href="' . admin_url() . '">Back to Admin</a></p>';
