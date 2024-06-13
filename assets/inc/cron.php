<?php

add_action('plugins_loaded', 'awesome_google_review_init');

function awesome_google_review_init()
{
    load_plugin_textdomain('awesome-google-review', false, dirname(plugin_basename(__FILE__)) . '/languages');
}

wp_timezone_override_offset('Asia/Kolkata');

// Add a custom cron interval for daily schedules
add_filter('cron_schedules', 'my_custom_cron_intervals');
function my_custom_cron_intervals($schedules)
{
    $schedules['daily'] = array(
        'interval' => 86400,
        'display' => __('Review Update Once Daily', 'textdomain'),
    );
    return $schedules;
}

if (!wp_next_scheduled('first_daily_data')) {
    $scheduled_time_main = strtotime('15:00:00');
    if ($scheduled_time_main < time()) {
        $scheduled_time_main += 86400;
    }
    wp_schedule_event($scheduled_time_main, 'daily', 'first_daily_data');
}

// Add action hook for the first daily data event
add_action('first_daily_data', 'my_first_function');
function my_first_function()
{
    first_update();
    $scheduled_time_first = strtotime('15:30:00');
    if ($scheduled_time_first < time()) {
        $scheduled_time_first += 86400;
    }
    if (!wp_next_scheduled('second_daily_data')) {
        wp_schedule_single_event($scheduled_time_first, 'second_daily_data');
    }
}

// Add action hook for the second daily data event
add_action('second_daily_data', 'my_second_function');
function my_second_function()
{
    second_update();
}

// Define the first update function
function first_update()
{
    $g_op = get_option('myfirst');
    $g_op = $g_op + 1;
    $st = update_option('myfirst', $g_op);
    cron_step_1(1);
    return $st;
}

// Define the second update function
function second_update()
{
    $sss = get_option('mysecond');
    $g_fdfdf = $sss + 1;
    update_option('mysecond', $g_fdfdf);

    cron_step_2(2);

    $third = get_option('mythird');
    $g_third = $third + 1;
    $sthird = update_option('mythird', $g_third);

    cron_step_3(3);

    $myfourth = get_option('myfourth');
    $g_myfourth = $myfourth + 1;
    $sg_myfourth = update_option('myfourth', $g_myfourth);

    cron_step_4(4);

    return $sg_myfourth;
}

// BELOW FUNCTIONS WHICH WILL AUTOMATICALLY CALL BY CRON JOBS RUN

// STEP 1 = START JOB
// 1. jobID_json = job_start_ajax_action
function cron_step_1($step)
{
    $response = array(
        'success' => 0,
        'data'    => array('jobID' => ''),
        'msg'     => ''
    );
    $step1 = get_all_executed_firm_names($step);
    global $wpdb;
    $review_api_key = function_exists('get_existing_api_key') ? get_existing_api_key() : '';

    foreach ($step1 as $firm_data) {
        $firm_name = sanitize_text_field($firm_data['firm_name']);
        $firm_name_jobID = sanitize_text_field($firm_data['jobID']);

        if (!empty($firm_name)) {
            $response_api_data = job_start_at_api($review_api_key, $firm_name);
            $jobID = $response_api_data['data']['jobID'];
            if ($response_api_data['success']) {
                $table_name_data = $wpdb->prefix . 'jobdata';
                if ($wpdb->last_error) {
                    $response['msg'] = "Database Error: " . $wpdb->last_error;
                } else {
                    $data = array(
                        'jobID' => $jobID,
                        'jobID_json' => 1,
                        'jobID_check_status' => 0,
                        'jobID_check' => 0,
                        'jobID_final' => 0
                    );
                    $data_to_update = array();
                    foreach ($data as $column => $value) {
                        $data_to_update[$column] = $value;
                    }

                    $where = array('jobID' => $firm_name_jobID);
                    // @codingStandardsIgnoreStart
                    $updated = $wpdb->update($table_name_data, $data_to_update, $where);
                    // @codingStandardsIgnoreEnd

                    if ($updated  !== false) {
                        $response['data']['jobID'] = $firm_name_jobID;
                        $response['success'] = 1;
                        $response['msg'] = $response_api_data['msg'];
                    } else {
                        $response['msg'] = "Database Error: Failed to insert/update job data.";
                    }
                }
            } else {
                $response['msg'] = "API Error: " . $response_api_data['msg'];
            }
        }
    }
    return $response;
}


// STEP 2 = CHECK STATUS OF JOB
// 2. jobID_check_status = job_check_status_update_ajax_action
function cron_step_2($step)
{
    $response = array(
        'success' => 0,
        'data'    => array('jobID' => ''),
        'msg'     => ''
    );
    $step2 = get_all_executed_firm_names($step);
    global $wpdb;
    $review_api_key = function_exists('get_existing_api_key') ? get_existing_api_key() : '';

    foreach ($step2 as $firm_data) {
        $firm_name = sanitize_text_field($firm_data['firm_name']);
        $firm_name_jobID = sanitize_text_field($firm_data['jobID']);

        if (!empty($firm_name)) {
            $response_api_data = job_check_status_at_api($review_api_key, $firm_name_jobID);
            $jobID = $response_api_data['data']['jobID'];

            if ($response_api_data['success']) {
                $table_name_data = $wpdb->prefix . 'jobdata';
                if ($wpdb->last_error) {
                    $response['msg'] = "Database Error: " . $wpdb->last_error;
                } else {
                    $data = array(
                        'jobID' => $jobID,
                        'jobID_json' => 1,
                        'jobID_check_status' => 1,
                        'jobID_check' => 0,
                        'jobID_final' => 0
                    );
                    $data_to_update = array();
                    foreach ($data as $column => $value) {
                        $data_to_update[$column] = $value;
                    }

                    $where = array('jobID' => $firm_name_jobID);
                    // @codingStandardsIgnoreStart
                    $updated = $wpdb->update($table_name_data, $data_to_update, $where);
                    // @codingStandardsIgnoreEnd

                    if ($updated  !== false) {
                        $response['data']['jobID'] = $firm_name_jobID;
                        $response['success'] = 1;
                        $response['msg'] = $response_api_data['msg'];
                    } else {
                        $response['msg'] = "Database Error: Failed to insert/update job data.";
                    }
                }
            } else {
                $response['msg'] = "API Error: " . $response_api_data['msg'];
            }
        }
    }
    return $response;
}

// STEP 3 = GET REVIEWS FROM SERVER
// 3. jobID_check = job_check_ajax_action
function cron_step_3($step)
{
    $response = array(
        'success' => 0,
        'data'    => array('jobID' => ''),
        'msg'     => ''
    );
    $step3 = get_all_executed_firm_names($step);
    global $wpdb;
    $review_api_key = function_exists('get_existing_api_key') ? get_existing_api_key() : '';

    foreach ($step3 as $firm_data) {
        $firm_name = sanitize_text_field($firm_data['firm_name']);
        $firm_name_jobID = sanitize_text_field($firm_data['jobID']);

        if (!empty($firm_name)) {
            $response_api_data = job_check_at_api($review_api_key, $firm_name_jobID);

            if ($response_api_data['success']) {
                $table_name_data = $wpdb->prefix . 'jobdata';
                $jobID = $response_api_data['data']['jobID'];
                if ($wpdb->last_error) {
                    $response['msg'] = "Database Error: " . $wpdb->last_error;
                } else {
                    $data = array(
                        'jobID' => $jobID,
                        'jobID_json' => 1,
                        'jobID_check_status' => 1,
                        'jobID_check' => 1,
                        'jobID_final' => 0
                    );
                    $data_to_update = array();
                    foreach ($data as $column => $value) {
                        $data_to_update[$column] = $value;
                    }

                    $where = array('jobID' => $firm_name_jobID);
                    // @codingStandardsIgnoreStart
                    $updated = $wpdb->update($table_name_data, $data_to_update, $where);
                    // @codingStandardsIgnoreEnd

                    if ($updated  !== false) {
                        $response['data']['jobID'] = $firm_name_jobID;
                        $response['success'] = 1;
                        $response['msg'] = $response_api_data['msg'];
                    } else {
                        $response['msg'] = "Database Error: Failed to insert/update job data.";
                    }
                }
            } else {
                $response['msg'] = "API Error: " . $response_api_data['msg'];
            }
        }
    }
    return $response;
}

// STEP 4 = UPLOAD REVIEWS
// 4. jobID_final = review_get_set_ajax_action
function cron_step_4($step)
{
    $response = array(
        'success' => 0,
        'data'    => array('jobID' => ''),
        'msg'     => ''
    );
    $step4 = get_all_executed_firm_names($step);
    global $wpdb;
    $review_api_key = function_exists('get_existing_api_key') ? get_existing_api_key() : '';
    foreach ($step4 as $firm_data) {
        $firm_name = sanitize_text_field($firm_data['firm_name']);
        $firm_name_jobID = sanitize_text_field($firm_data['jobID']);
        if (!empty($firm_name)) {
            $reviews_array = get_reviews_data($firm_name_jobID, $review_api_key);
            if ($reviews_array['success'] == 0) {
                $response['job_id'] = 0;
                $response['message'] = $reviews_array['message'];
            } else {
                $reviews_data = $reviews_array['reviews'];
                $response['job_id'] = $firm_name_jobID;
                $response['data'] = $reviews_data['reviews'];
                $term_name = $reviews_array['reviews']['firm_name'];
                $term_slug = sanitize_title($reviews_array['reviews']['firm_name']);
                delete_reviews_data($term_slug);
                $data_stored = store_data_into_reviews($firm_name_jobID, $reviews_array, $term_name);

                if ($data_stored['status'] == 1) {
                    update_flag('jobID_final', 1, $firm_name_jobID);
                    update_flag('term_id', $data_stored['term_id'], $firm_name_jobID);
                    $response['term_slug'] = $term_slug;
                    $response['message'] = "Data upload successfully!";
                    $response['success'] = 1;
                } else {
                    update_flag('jobID_final', 0, $firm_name_jobID);
                    $response['message'] = "Failed to store data.";
                }
            }
        }
    }
    return $response;
}
