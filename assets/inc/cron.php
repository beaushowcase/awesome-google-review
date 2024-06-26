<?php

date_default_timezone_set('Asia/Kolkata');

define('HOURLY_SECONDS', 3600);
define('TWICEDAILY_SECONDS', 43200);
define('DAILY_SECONDS', 86400);
define('WEEKLY_SECONDS', 604800);

$api_records = get_existing_api_key_data();
$api_record_recurrence = $api_records->recurrence;
$api_record_timeslot = $api_records->timeslot;
$timeslot_second = $api_records->timeslot_second;

$recurrence_intervals = [
    'hourly' => HOURLY_SECONDS,
    'twicedaily' => TWICEDAILY_SECONDS,
    'daily' => DAILY_SECONDS,
    'weekly' => WEEKLY_SECONDS
];
$selected_recurrence = $recurrence_intervals[$api_record_recurrence] ?? DAILY_SECONDS;

add_filter('cron_schedules', 'add_custom_cron_interval');
function add_custom_cron_interval($schedules)
{
    global $api_record_recurrence, $selected_recurrence;
    $schedules[$api_record_recurrence] = [
        'interval' => $selected_recurrence,
        'display' => sprintf('Review Update %s', $api_record_recurrence),
    ];
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
    global $api_records, $selected_recurrence;
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
    update_cron_step(1);
}

function second_update()
{
    update_cron_step(2);
    update_cron_step(3);
    update_cron_step(4);
}

function update_cron_step($step)
{
    update_option("cron{$step}", 1);
    $response = [
        'success' => 0,
        'data' => ['jobID' => ''],
        'msg' => ''
    ];
    $step_data = get_all_executed_firm_names($step);
    $review_api_key = function_exists('get_existing_api_key') ? get_existing_api_key() : '';

    foreach ($step_data as $firm_data) {
        $result = process_firm_data($step, $firm_data, $review_api_key);
        if ($result['success']) {
            $response = $result;
        } else {
            $response['msg'] .= $result['msg'] . ' ';
        }
    }
    return $response;
}

function process_firm_data($step, $firm_data, $review_api_key)
{
    global $wpdb;
    $firm_name = sanitize_text_field($firm_data['firm_name']);
    $firm_name_jobID = sanitize_text_field($firm_data['jobID']);
    $term_id = sanitize_text_field($firm_data['term_id']);

    if (empty($firm_name)) {
        return handle_error("Empty firm name");
    }

    try {
        switch ($step) {
            case 1:
                $response_api_data = job_start_at_api($review_api_key, $firm_name);
                break;
            case 2:
                $response_api_data = job_check_status_at_api($review_api_key, $firm_name_jobID);
                break;
            case 3:
                $response_api_data = job_check_at_api($review_api_key, $firm_name_jobID);
                break;
            case 4:
                return process_step_4($firm_name, $firm_name_jobID, $review_api_key);
            default:
                return handle_error("Invalid step");
        }

        if (!$response_api_data['success']) {
            return handle_error("API Error: " . $response_api_data['msg']);
        }

        $jobID = $response_api_data['data']['jobID'];
        $table_name_data = $wpdb->prefix . 'jobdata';
        
        $data = [
            'jobID' => $jobID,
            'jobID_json' => 1,
            'jobID_check_status' => $step >= 2 ? 1 : 0,
            'jobID_check' => $step >= 3 ? 1 : 0,
            'jobID_final' => 0
        ];

        $where = ['term_id' => $term_id];
        $updated = $wpdb->update($table_name_data, $data, $where);

        if ($updated === false) {
            return handle_error("Database Error: Failed to update job data.");
        }

        return [
            'success' => 1,
            'data' => ['jobID' => $firm_name_jobID],
            'msg' => $response_api_data['msg']
        ];

    } catch (Exception $e) {
        return handle_error("Error: " . $e->getMessage());
    }
}

function process_step_4($firm_name, $firm_name_jobID, $review_api_key)
{
    $reviews_array = get_reviews_data($firm_name_jobID, $review_api_key);
    if ($reviews_array['success'] == 0) {
        return handle_error($reviews_array['message']);
    }

    $reviews_data = $reviews_array['reviews'];
    $term_name = $reviews_data['firm_name'];
    $term_slug = sanitize_title($term_name);

    delete_reviews_data($term_slug);
    $data_stored = store_data_into_reviews($firm_name_jobID, $reviews_array, $term_name);

    if ($data_stored['status'] == 1) {
        update_flag('jobID_final', 1, $firm_name_jobID);
        update_flag('term_id', $data_stored['term_id'], $firm_name_jobID);
        return [
            'success' => 1,
            'data' => ['jobID' => $firm_name_jobID],
            'term_slug' => $term_slug,
            'msg' => "Data uploaded successfully!"
        ];
    } else {
        update_flag('jobID_final', 0, $firm_name_jobID);
        return handle_error("Failed to store data.");
    }
}

function handle_error($message)
{
    error_log($message);
    return ['success' => 0, 'msg' => $message];
}

// Assuming these functions are defined elsewhere in your codebase:
// get_existing_api_key_data(), get_all_executed_firm_names(), job_start_at_api(), 
// job_check_status_at_api(), job_check_at_api(), get_reviews_data(), delete_reviews_data(),
// store_data_into_reviews(), update_flag()