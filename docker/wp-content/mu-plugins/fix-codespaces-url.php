<?php
/**
 * Plugin Name: Fix Codespaces URL
 */

// Override WordPress redirect to prevent port changes
add_filter('wp_redirect', function($location) {
    $location = str_replace(':443/', '/', $location);
    $location = str_replace('-443.app.github.dev', '-8000.app.github.dev', $location);
    return $location;
}, 10, 1);
