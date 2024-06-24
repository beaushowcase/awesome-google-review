<?php

date_default_timezone_set('Asia/Kolkata');

$api_records = get_existing_api_key_data();

$api_record_recurrence = $api_records->recurrence;
$api_record_timeslot = $api_records->timeslot;
$timeslot_second = $api_records->timeslot_second;

$selected_recurrence = 86400;
if ($api_record_recurrence == 'hourly') {
    $selected_recurrence = 3600;
} else if ($api_record_recurrence == 'twicedaily') {
    $selected_recurrence = 43200;
} else if ($api_record_recurrence == 'weekly') {
    $selected_recurrence = 604800;
}

add_filter('cron_schedules', 'my_custom_cron_intervals');
function my_custom_cron_intervals($schedules)
{
    $schedules[$api_record_recurrence] = array(
        'interval' => $selected_recurrence,
        'display' => __('Review Update ' . $api_record_recurrence . '', 'textdomain'),
    );
    return $schedules;
}

if (!wp_next_scheduled('first_daily_data')) {
    $scheduled_time_main = strtotime($api_record_timeslot);
    if ($scheduled_time_main < time()) {
        $scheduled_time_main += $selected_recurrence;
    }
    wp_schedule_event($scheduled_time_main, $api_record_recurrence, 'first_daily_data');
}

add_action('first_daily_data', 'my_first_function');
function my_first_function()
{
    first_update();
    $api_records = get_existing_api_key_data();
    $timeslot_second = $api_records->timeslot_second;
    $scheduled_time_first = strtotime($timeslot_second);
    if ($scheduled_time_first < time()) {
        $scheduled_time_first += $selected_recurrence;
    }
    if (!wp_next_scheduled('second_daily_data')) {
        wp_schedule_single_event($scheduled_time_first, 'second_daily_data');
    }
}

add_action('second_daily_data', 'my_second_function');
function my_second_function()
{
    second_update();
}

function first_update()
{
    cron_step_1(1);
}
function second_update()
{
    cron_step_2(2);
    cron_step_3(3);
    cron_step_4(4);
}
function cron_step_1($step)
{

    update_option('cron1',1);

    $response = array(
        'success' => 0,
        'data' => array('jobID' => ''),
        'msg' => ''
    );
    $step1 = get_all_executed_firm_names($step);

    // ptr($all_executed_firm_datas);exit;
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
                    $updated = $wpdb->update($table_name_data, $data_to_update, $where);

                    if ($updated !== false) {
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
function cron_step_2($step)
{
    update_option('cron2',1);

    $response = array(
        'success' => 0,
        'data' => array('jobID' => ''),
        'msg' => ''
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
                    $updated = $wpdb->update($table_name_data, $data_to_update, $where);

                    if ($updated !== false) {
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
function cron_step_3($step)
{
    $response = array(
        'success' => 0,
        'data' => array('jobID' => ''),
        'msg' => ''
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
                    $updated = $wpdb->update($table_name_data, $data_to_update, $where);

                    if ($updated !== false) {
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
function cron_step_4($step)
{
    $response = array(
        'success' => 0,
        'data' => array('jobID' => ''),
        'msg' => ''
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