<?php
// Disable canonical redirects
remove_action('template_redirect', 'redirect_canonical');

// Force correct URL in all contexts
add_filter('option_home', function() {
    return 'https://zany-space-yodel-4vw6w4qppwr3j9rv-8000.app.github.dev';
});

add_filter('option_siteurl', function() {
    return 'https://zany-space-yodel-4vw6w4qppwr3j9rv-8000.app.github.dev';
});
