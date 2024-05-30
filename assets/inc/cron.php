<?php

date_default_timezone_set('Asia/Kolkata');

add_filter('cron_schedules', 'my_custom_cron_intervals');
function my_custom_cron_intervals($schedules) {
    $schedules['every_minute'] = array(
        'interval' => 180,
        'display' => __('Every Minute', 'textdomain'),
    );
    return $schedules;
}

if (!wp_next_scheduled('first_daily_data')) {
    wp_schedule_event(time(), 'every_minute', 'first_daily_data');
}

add_action('first_daily_data', 'my_first_function');
function my_first_function() {
    first_update();
    // Schedule the second event 30 seconds after the first event    
    // Log the scheduling of the second event   
    if (!wp_next_scheduled('second_daily_data')) {
        wp_schedule_single_event(time() + 30, 'second_daily_data');
    }    
}

add_action('second_daily_data', 'my_second_function');
function my_second_function() {
    second_update();
}

function first_update()
{
    $g_op = get_option('myfirst');
    $g_op = $g_op + 1;
    $st = update_option('myfirst', $g_op);
    return $st;
}

function second_update()
{
    $sss = get_option('mysecond');
    $g_fdfdf = $sss + 1;
    $stss = update_option('mysecond', $g_fdfdf);
    return $stss;
}

