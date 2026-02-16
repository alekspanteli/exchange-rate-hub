<?php
// Main loader: loads admin, frontend, API, data, etc.
class ERH_Loader {
    public static function init() {
        // Load core classes
        require_once(ERH_PLUGIN_DIR . 'includes/class-erh-api.php');
        require_once(ERH_PLUGIN_DIR . 'includes/class-erh-data.php');
        require_once(ERH_PLUGIN_DIR . 'includes/class-erh-cron.php');

        // Load admin or frontend based on context
        if (is_admin()) {
            require_once(ERH_PLUGIN_DIR . 'includes/class-erh-admin.php');
            ERH_Admin::init();
        } else {
            require_once(ERH_PLUGIN_DIR . 'includes/class-erh-frontend.php');
            ERH_Frontend::init();
        }

        // Add custom page template
        add_filter('theme_page_templates', [__CLASS__, 'add_page_template']);
        add_filter('template_include', [__CLASS__, 'load_page_template']);
    }

    /**
     * Add custom page template to WordPress
     *
     * @param array $templates Existing templates
     * @return array Modified templates
     */
    public static function add_page_template($templates) {
        $templates['page-exchange-rates.php'] = __('Exchange Rates', 'exchange-rate-hub');
        return $templates;
    }

    /**
     * Load custom page template
     *
     * @param string $template Current template path
     * @return string Modified template path
     */
    public static function load_page_template($template) {
        if (is_page()) {
            $page_template = get_post_meta(get_the_ID(), '_wp_page_template', true);

            if ($page_template === 'page-exchange-rates.php') {
                $plugin_template = ERH_PLUGIN_DIR . 'templates/page-exchange-rates.php';

                if (file_exists($plugin_template)) {
                    return $plugin_template;
                }
            }
        }

        return $template;
    }
}
