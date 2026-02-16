<?php
// Handles plugin deactivation: unschedule events, etc.
class ERH_Deactivator {
    public static function deactivate() {
        // Unschedule cron event
        $timestamp = wp_next_scheduled('erh_cron_update_rates');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'erh_cron_update_rates');
        }
    }
}
