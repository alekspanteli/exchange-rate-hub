<?php
// Handles API integration (e.g., exchangerate.host)
class ERH_API {
    /**
     * Fetch exchange rates from API
     *
     * @param string $base Base currency code
     * @param array $symbols Array of currency codes to fetch
     * @return array|false Rates array or false on failure
     */
    public static function fetch_rates($base = 'USD', $symbols = []) {
        $base = strtoupper(sanitize_text_field($base));
        $url = 'https://api.fxratesapi.com/latest?base=' . urlencode($base);

        if (!empty($symbols)) {
            $symbols = array_map('strtoupper', array_map('sanitize_text_field', $symbols));
            $url .= '&currencies=' . urlencode(implode(',', $symbols));
        }

        // Make API request with timeout
        $response = wp_remote_get($url, [
            'timeout' => 15,
            'user-agent' => 'WordPress Exchange Rate Hub Plugin/1.0'
        ]);

        // Check for errors
        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            self::log_error('API Request Failed: ' . $error_message);
            set_transient('erh_admin_error', 'API request failed: ' . $error_message, 300);
            return false;
        }

        // Check response code
        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code !== 200) {
            self::log_error('API returned non-200 status code: ' . $response_code);
            set_transient('erh_admin_error', 'API error: HTTP ' . $response_code, 300);
            return false;
        }

        // Parse response
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        // Validate response data
        if (json_last_error() !== JSON_ERROR_NONE) {
            self::log_error('Invalid JSON response from API');
            set_transient('erh_admin_error', 'Invalid API response format', 300);
            return false;
        }

        if (!isset($data['rates']) || empty($data['rates'])) {
            self::log_error('No rates found in API response');
            set_transient('erh_admin_error', 'No rates found in API response', 300);
            return false;
        }

        // Validate rates are numeric
        foreach ($data['rates'] as $currency => $rate) {
            if (!is_numeric($rate)) {
                self::log_error('Invalid rate value for currency: ' . $currency);
                unset($data['rates'][$currency]);
            }
        }

        if (empty($data['rates'])) {
            self::log_error('No valid rates after validation');
            return false;
        }

        return $data['rates'];
    }

    /**
     * Log error message
     *
     * @param string $message Error message
     * @return void
     */
    private static function log_error($message) {
        if (defined('WP_DEBUG') && WP_DEBUG === true) {
            error_log('[Exchange Rate Hub] ' . $message);
        }

        // Store last error in option
        update_option('erh_last_error', [
            'message' => $message,
            'timestamp' => current_time('mysql')
        ]);
    }

    /**
     * Get last error message
     *
     * @return array|false Error data or false if no error
     */
    public static function get_last_error() {
        return get_option('erh_last_error', false);
    }
}
