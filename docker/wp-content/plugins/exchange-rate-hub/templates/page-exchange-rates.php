<?php
/**
 * Template Name: Exchange Rates
 * Description: A custom template for displaying exchange rates
 */

get_header();
?>

<main id="primary" class="site-main">
    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

        <header class="entry-header">
            <h1 class="entry-title"><?php the_title(); ?></h1>
        </header>

        <div class="entry-content">
            <?php
            // Display page content if any
            the_content();
            ?>

            <?php
            // Display exchange rates
            $base_currency = get_option('erh_base_currency', 'USD');
            $rates_data = ERH_Data::get_formatted_rates($base_currency);

            if ($rates_data && !empty($rates_data['rates'])):
                $rates = $rates_data['rates'];
                $last_updated = $rates_data['last_updated'];
            ?>
                <div class="erh-page-template">
                    <div class="erh-rates-container">
                        <h2>Current Exchange Rates (1 <?php echo esc_html($base_currency); ?> =)</h2>

                        <div class="erh-rates-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin: 30px 0;">
                            <?php foreach ($rates as $currency => $rate): ?>
                                <div class="erh-rate-card" style="padding: 20px; background: #f7f7f7; border-radius: 8px; text-align: center;">
                                    <div class="erh-currency" style="font-size: 24px; font-weight: bold; color: #0073aa; margin-bottom: 10px;">
                                        <?php echo esc_html($currency); ?>
                                    </div>
                                    <div class="erh-rate" style="font-size: 20px; font-family: 'Courier New', monospace;">
                                        <?php echo esc_html(number_format($rate, 4)); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <p class="erh-last-updated" style="text-align: center; color: #666; font-size: 14px; margin-top: 20px;">
                            Last updated: <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($last_updated))); ?>
                        </p>
                    </div>
                </div>

            <?php else: ?>
                <div class="erh-no-rates" style="padding: 40px; text-align: center; background: #fff3cd; border: 1px solid #ffc107; border-radius: 8px;">
                    <p>Exchange rates are currently unavailable. Please check back later.</p>
                </div>
            <?php endif; ?>

        </div><!-- .entry-content -->

    </article>
</main><!-- #primary -->

<?php
get_sidebar();
get_footer();
