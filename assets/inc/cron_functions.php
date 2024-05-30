<?php

// AUTO CRON RUN

// STEP 1 = START JOB
$step = 0;
function cron_step_1()
{
    $step = 1;
    $response = array(
        'success' => 0,
        'data'    => array('jobID' => ''),
        'msg'     => ''
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
function cron_step_2()
{
    $step = 2;
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
                    $updated = $wpdb->update($table_name_data, $data_to_update, $where);

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
function cron_step_3()
{
    $step = 3;
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