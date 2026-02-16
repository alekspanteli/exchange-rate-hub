<?php
// Handles frontend rendering (shortcode/block)
class ERH_Frontend {
    /**
     * Initialize frontend hooks
     */
    public static function init() {
        add_shortcode('exchange_rates', [__CLASS__, 'render_shortcode']);
        add_action('wp_enqueue_scripts', [__CLASS__, 'enqueue_styles']);
    }

    /**
     * Enqueue frontend styles
     */
    public static function enqueue_styles() {
        wp_enqueue_style(
            'erh-frontend-css',
            ERH_PLUGIN_URL . 'assets/css/frontend.css',
            [],
            '1.0.0'
        );
    }

    /**
     * Render exchange rates shortcode
     *
     * Shortcode attributes:
     * - base: Base currency (default: from settings)
     * - show_base: Show base currency in title (default: true)
     * - columns: Number of columns (1-4, default: 2)
     *
     * @param array $atts Shortcode attributes
     * @return string HTML output
     */
    public static function render_shortcode($atts) {
        // Parse attributes
        $atts = shortcode_atts([
            'base' => get_option('erh_base_currency', 'USD'),
            'show_base' => 'true',
            'columns' => '2'
        ], $atts, 'exchange_rates');

        $base = strtoupper(sanitize_text_field($atts['base']));
        $show_base = filter_var($atts['show_base'], FILTER_VALIDATE_BOOLEAN);
        $columns = absint($atts['columns']);
        $columns = max(1, min(4, $columns)); // Limit 1-4

        // Get rates data
        $rates_data = ERH_Data::get_formatted_rates($base);

        // Start output buffering
        ob_start();

        if (!$rates_data || empty($rates_data['rates'])) {
            ?>
            <div class="erh-exchange-rates erh-no-rates">
                <div class="erh-error-message">
                    <p><?php echo esc_html__('Exchange rates are currently unavailable. Please check back later.', 'exchange-rate-hub'); ?></p>
                </div>
            </div>
            <?php
            return ob_get_clean();
        }

        $rates = $rates_data['rates'];
        $last_updated = $rates_data['last_updated'];

        ?>
        <div class="erh-exchange-rates" data-base="<?php echo esc_attr($base); ?>">
            <?php if ($show_base): ?>
                <div class="erh-header">
                    <h3 class="erh-title">
                        <?php echo esc_html(sprintf(__('Exchange Rates (1 %s =)', 'exchange-rate-hub'), $base)); ?>
                    </h3>
                </div>
            <?php endif; ?>

            <div class="erh-rates-grid erh-cols-<?php echo esc_attr($columns); ?>">
                <?php foreach ($rates as $currency => $rate): ?>
                    <div class="erh-rate-item">
                        <div class="erh-currency-code">
                            <strong><?php echo esc_html($currency); ?></strong>
                        </div>
                        <div class="erh-rate-value">
                            <?php echo esc_html(number_format($rate, 4)); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="erh-footer">
                <p class="erh-last-updated">
                    <small>
                        <?php
                        echo esc_html(
                            sprintf(
                                __('Last updated: %s', 'exchange-rate-hub'),
                                date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($last_updated))
                            )
                        );
                        ?>
                    </small>
                </p>
            </div>
        </div>
        <?php

        return ob_get_clean();
    }

    /**
     * Render rates table (alternative layout)
     *
     * @param string $base Base currency
     * @return string HTML output
     */
    public static function render_table($base = null) {
        if (!$base) {
            $base = get_option('erh_base_currency', 'USD');
        }

        $rates_data = ERH_Data::get_formatted_rates($base);

        if (!$rates_data) {
            return '<p>' . esc_html__('No exchange rates available.', 'exchange-rate-hub') . '</p>';
        }

        ob_start();
        ?>
        <table class="erh-rates-table">
            <thead>
                <tr>
                    <th><?php echo esc_html__('Currency', 'exchange-rate-hub'); ?></th>
                    <th><?php echo esc_html__('Rate', 'exchange-rate-hub'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rates_data['rates'] as $currency => $rate): ?>
                    <tr>
                        <td><strong><?php echo esc_html($currency); ?></strong></td>
                        <td><?php echo esc_html(number_format($rate, 4)); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php
        return ob_get_clean();
    }
}
