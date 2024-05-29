<?php

date_default_timezone_set('Asia/Kolkata');

//On plugin activation schedule our daily database backup
// function myprefix_custom_cron_schedule( $schedules ) {
//     $schedules['daily'] = array(
//         'interval' => 10, // Every 24 hours
//         'display'  => __( '10 seconds' ),
//     );
//     return $schedules;
// }
// add_filter( 'cron_schedules', 'myprefix_custom_cron_schedule' );

// //Schedule an action if it's not already scheduled
// if ( ! wp_next_scheduled( 'mxpbiz_cron_hook2' ) ) {
//     wp_schedule_event( time(), 'daily', 'mxpbiz_cron_hook2' );
// }

// ///Hook into that action that'll fire every six hours
//  add_action( 'mxpbiz_cron_hook2', 'myprefix_cron_check_expired' );

// //create your function, that runs on cron
// function myprefix_cron_check_expired() {
//     $g_op = get_option('my_custom_option');
//     $g_op = $g_op+1;
//     $st = update_option('my_custom_option', $g_op);
//     // return $st;   
// }


// $timestamp = wp_next_scheduled( 'second_daily_event' );
// if ( $timestamp ) {
//     wp_unschedule_event( $timestamp, 'second_daily_event' );
// }


// asdlkjfadjf

// Add the custom cron interval
// add_filter('cron_schedules', 'add_one_minute_interval');
// function add_one_minute_interval($schedules) {
//     $schedules['one_minute'] = array(
//         'interval' => 60, // 1 minute in seconds
//         'display' => __('Once Every Minute')
//     );
//     return $schedules;
// }

// Schedule the cron event
// if (!wp_next_scheduled('first_daily_event')) {
//     wp_schedule_event(time(), 'daily', 'first_daily_event');
// }

// // Function to be executed by the cron event
// add_action('first_daily_event', 'first_daily_event_function');
// function first_daily_event_function() {

//     first_update();

//     wp_schedule_single_event( time() + 30, 'second_daily_event' );
// }

// add_action( 'second_daily_event', 'handle_second_daily_event' );

// function handle_second_daily_event() {
//     second_update();
// }

// function first_update(){
//     $g_op = get_option('myfirst');
//     $g_op = $g_op+1;
//     $st = update_option('myfirst', $g_op);
//     return $st;
// }

// function second_update(){
//     $g_op = get_option('mysecond');
//     $g_op = $g_op+1;
//     $st = update_option('mysecond', $g_op);
//     return $st;
// }

// $timestamp = wp_next_scheduled( 'my_one_minute_cron_hook' );
// if ( $timestamp ) {
//     wp_unschedule_event( $timestamp, 'my_one_minute_cron_hook' );
// }

// cron new

// if ( ! wp_next_scheduled( 'first_daily_event' ) ) {
//     wp_schedule_event( time(), 'daily', 'first_daily_event' );
// }

// add_action( 'first_daily_event', 'handle_first_daily_event' );

// function handle_first_daily_event() {
//     $g_op = get_option('myfirst');
//     $g_op = $g_op+1;
//     $st = update_option('myfirst', $g_op);

//     wp_schedule_single_event( time() + 60, 'second_daily_event' );
// }

// add_action( 'second_daily_event', 'handle_second_daily_event' );

// function handle_second_daily_event() {
//     $g_op = get_option('mysecond');
//     $g_op = $g_op+1;
//     $st = update_option('mysecond', $g_op);
// }

// $timestamp = wp_next_scheduled( 'first_daily_event' );
// if ( $timestamp ) {
//     wp_unschedule_event( $timestamp, 'first_daily_event' );
// }


// REMOVE CRON 

// $timestamp = wp_next_scheduled( 'first_daily_data' );
// if ( $timestamp ) {
//     wp_unschedule_event( $timestamp, 'first_daily_data' );
// }
// $timestamp = wp_next_scheduled( 'second_daily_data' );
// if ( $timestamp ) {
//     wp_unschedule_event( $timestamp, 'second_daily_data' );
// }



// working code

add_filter('cron_schedules', 'my_custom_cron_intervals');
function my_custom_cron_intervals($schedules) {
    $schedules['every_minute'] = array(
        'interval' => 60,
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
    wp_schedule_single_event(time() + 90, 'second_daily_data');
}

add_action('second_daily_data', 'handle_second_daily_data');
function handle_second_daily_data() {
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



function my_cron_status_page() {
    $first_function_next_run = wp_next_scheduled( 'first_daily_data' );
    $second_function_next_run = wp_next_scheduled( 'second_daily_data' );
    ?>
    <div class="wrap">
        <h1>Cron Job Status</h1>
        <table class="widefat">
            <thead>
                <tr>
                    <th>Function</th>
                    <th>Next Run</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Scrapping Cron</td>
                    <td><?php echo $first_function_next_run ? date( 'Y-m-d H:i:s', $first_function_next_run ) : 'Not scheduled'; ?></td>
                </tr>
                <tr>
                    <td>Uploading Cron</td>
                    <td><?php echo $second_function_next_run ? date( 'Y-m-d H:i:s', $second_function_next_run ) : 'Not scheduled'; ?></td>
                </tr>
            </tbody>
        </table>
    </div>
    <?php
}

// Add the cron status page to the admin menu
function my_cron_status_menu() {
    add_management_page( 'Cron Job Status', 'Cron Job Status', 'manage_options', 'my-cron-status', 'my_cron_status_page' );
}
add_action( 'admin_menu', 'my_cron_status_menu' );