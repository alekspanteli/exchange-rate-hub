<?php
// Handles admin UI, settings, and options
class ERH_Admin {
    public static function init() {
        add_action('admin_menu', [__CLASS__, 'add_menu']);
        add_action('admin_init', [__CLASS__, 'register_settings']);
        add_action('admin_init', [__CLASS__, 'handle_settings_save']);
        add_action('admin_notices', [__CLASS__, 'display_admin_notices']);
        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_admin_styles']);
    }

    public static function enqueue_admin_styles($hook) {
        if ($hook !== 'toplevel_page_erh-admin') return;
        wp_enqueue_style('erh-admin-css', ERH_PLUGIN_URL . 'assets/css/admin.css', [], '1.0.0');
    }

    public static function add_menu() {
        add_menu_page(
            'Exchange Rate Hub',
            'Exchange Rates',
            'manage_options',
            'erh-admin',
            [__CLASS__, 'render_page'],
            'dashicons-money-alt',
            80
        );
    }

    public static function register_settings() {
        register_setting('erh_options', 'erh_base_currency', [
            'sanitize_callback' => [__CLASS__, 'sanitize_currency_code']
        ]);
        register_setting('erh_options', 'erh_enabled_currencies', [
            'sanitize_callback' => [__CLASS__, 'sanitize_currencies_array']
        ]);
        register_setting('erh_options', 'erh_update_frequency', [
            'sanitize_callback' => 'sanitize_text_field'
        ]);
        register_setting('erh_options', 'erh_api_key', [
            'sanitize_callback' => 'sanitize_text_field'
        ]);
    }

    public static function sanitize_currency_code($value) {
        $value = strtoupper(sanitize_text_field($value));
        return preg_match('/^[A-Z]{3}$/', $value) ? $value : 'USD';
    }

    public static function sanitize_currencies_array($value) {
        if (is_string($value)) {
            $value = explode(',', $value);
        }
        if (!is_array($value)) return ['EUR', 'GBP', 'JPY'];

        $sanitized = [];
        foreach ($value as $currency) {
            $currency = strtoupper(trim(sanitize_text_field($currency)));
            if (preg_match('/^[A-Z]{3}$/', $currency)) {
                $sanitized[] = $currency;
            }
        }
        return !empty($sanitized) ? $sanitized : ['EUR', 'GBP', 'JPY'];
    }

    public static function handle_settings_save() {
        if (!isset($_POST['erh_save_settings'])) return;

        // Security check
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to access this page.'));
        }

        check_admin_referer('erh_settings_nonce', 'erh_settings_nonce_field');

        // Save settings
        $base_currency = self::sanitize_currency_code($_POST['erh_base_currency'] ?? 'USD');
        $enabled_currencies = isset($_POST['erh_enabled_currencies'])
            ? self::sanitize_currencies_array($_POST['erh_enabled_currencies'])
            : ['EUR', 'GBP', 'JPY'];
        $update_frequency = sanitize_text_field($_POST['erh_update_frequency'] ?? 'hourly');
        $api_key = sanitize_text_field($_POST['erh_api_key'] ?? '');

        update_option('erh_base_currency', $base_currency);
        update_option('erh_enabled_currencies', $enabled_currencies);
        update_option('erh_update_frequency', $update_frequency);
        update_option('erh_api_key', $api_key);

        // Update cron schedule if frequency changed
        self::update_cron_schedule($update_frequency);

        // Trigger immediate update
        ERH_Cron::update_rates();

        set_transient('erh_admin_notice', 'Settings saved and rates updated successfully!', 30);

        wp_redirect(admin_url('admin.php?page=erh-admin'));
        exit;
    }

    public static function update_cron_schedule($frequency) {
        $timestamp = wp_next_scheduled('erh_cron_update_rates');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'erh_cron_update_rates');
        }
        wp_schedule_event(time(), $frequency, 'erh_cron_update_rates');
    }

    public static function display_admin_notices() {
        $notice = get_transient('erh_admin_notice');
        if ($notice) {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($notice) . '</p></div>';
            delete_transient('erh_admin_notice');
        }

        $error = get_transient('erh_admin_error');
        if ($error) {
            echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($error) . '</p></div>';
            delete_transient('erh_admin_error');
        }
    }

    public static function render_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to access this page.'));
        }

        // Get current settings
        $base_currency = get_option('erh_base_currency', 'USD');
        $enabled_currencies = get_option('erh_enabled_currencies', ['EUR', 'GBP', 'JPY']);
        $update_frequency = get_option('erh_update_frequency', 'hourly');
        $api_key = get_option('erh_api_key', '');

        // Get latest rates
        $rates_data = ERH_Data::get_latest_rates($base_currency);

        ?>
        <div class="wrap erh-admin-wrap">
            <h1><?php echo esc_html__('Exchange Rate Hub', 'exchange-rate-hub'); ?></h1>

            <div class="erh-admin-container">
                <div class="erh-settings-section">
                    <h2><?php echo esc_html__('Settings', 'exchange-rate-hub'); ?></h2>

                    <form method="post" action="">
                        <?php wp_nonce_field('erh_settings_nonce', 'erh_settings_nonce_field'); ?>

                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="erh_base_currency"><?php echo esc_html__('Base Currency', 'exchange-rate-hub'); ?></label>
                                </th>
                                <td>
                                    <input type="text"
                                           id="erh_base_currency"
                                           name="erh_base_currency"
                                           value="<?php echo esc_attr($base_currency); ?>"
                                           maxlength="3"
                                           pattern="[A-Z]{3}"
                                           class="regular-text"
                                           required />
                                    <p class="description"><?php echo esc_html__('3-letter currency code (e.g., USD, EUR, GBP)', 'exchange-rate-hub'); ?></p>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row">
                                    <label for="erh_enabled_currencies"><?php echo esc_html__('Enabled Currencies', 'exchange-rate-hub'); ?></label>
                                </th>
                                <td>
                                    <input type="text"
                                           id="erh_enabled_currencies"
                                           name="erh_enabled_currencies"
                                           value="<?php echo esc_attr(implode(', ', $enabled_currencies)); ?>"
                                           class="regular-text"
                                           required />
                                    <p class="description"><?php echo esc_html__('Comma-separated currency codes (e.g., EUR, GBP, JPY, CAD)', 'exchange-rate-hub'); ?></p>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row">
                                    <label for="erh_update_frequency"><?php echo esc_html__('Update Frequency', 'exchange-rate-hub'); ?></label>
                                </th>
                                <td>
                                    <select id="erh_update_frequency" name="erh_update_frequency" class="regular-text">
                                        <option value="hourly" <?php selected($update_frequency, 'hourly'); ?>><?php echo esc_html__('Hourly', 'exchange-rate-hub'); ?></option>
                                        <option value="twicedaily" <?php selected($update_frequency, 'twicedaily'); ?>><?php echo esc_html__('Twice Daily', 'exchange-rate-hub'); ?></option>
                                        <option value="daily" <?php selected($update_frequency, 'daily'); ?>><?php echo esc_html__('Daily', 'exchange-rate-hub'); ?></option>
                                    </select>
                                    <p class="description"><?php echo esc_html__('How often to fetch new rates from the API', 'exchange-rate-hub'); ?></p>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row">
                                    <label for="erh_api_key"><?php echo esc_html__('API Key', 'exchange-rate-hub'); ?></label>
                                </th>
                                <td>
                                    <input type="text"
                                           id="erh_api_key"
                                           name="erh_api_key"
                                           value="<?php echo esc_attr($api_key); ?>"
                                           class="regular-text" />
                                    <p class="description"><?php echo esc_html__('Optional: API key for premium providers', 'exchange-rate-hub'); ?></p>
                                </td>
                            </tr>
                        </table>

                        <p class="submit">
                            <input type="submit"
                                   name="erh_save_settings"
                                   class="button button-primary"
                                   value="<?php echo esc_attr__('Save Settings & Update Rates', 'exchange-rate-hub'); ?>" />
                        </p>
                    </form>
                </div>

                <div class="erh-rates-section">
                    <h2><?php echo esc_html__('Current Exchange Rates', 'exchange-rate-hub'); ?></h2>

                    <?php if ($rates_data && !empty($rates_data->rates)): ?>
                        <?php
                        $rates = maybe_unserialize($rates_data->rates);
                        $last_updated = $rates_data->last_updated;
                        ?>

                        <p class="erh-last-update">
                            <strong><?php echo esc_html__('Last Updated:', 'exchange-rate-hub'); ?></strong>
                            <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($last_updated))); ?>
                        </p>

                        <table class="widefat striped erh-rates-table">
                            <thead>
                                <tr>
                                    <th><?php echo esc_html__('Currency', 'exchange-rate-hub'); ?></th>
                                    <th><?php echo esc_html__('Rate (1 ' . $base_currency . ' =)', 'exchange-rate-hub'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rates as $currency => $rate): ?>
                                    <tr>
                                        <td><strong><?php echo esc_html($currency); ?></strong></td>
                                        <td><?php echo esc_html(number_format($rate, 4)); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="notice notice-warning inline">
                            <p><?php echo esc_html__('No rates available yet. Save settings to fetch initial rates.', 'exchange-rate-hub'); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }
}
