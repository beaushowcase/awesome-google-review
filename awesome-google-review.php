<?php
/*
 * Plugin Name:       Awesome Google Review
 * Plugin URI:        https://beardog.digital/
 * Description:       Impresses with top-notch service and skilled professionals. A 5-star destination for grooming excellence!
 * Version:           1.3.2
 * Requires PHP:      7.0
 * Author:            #beaubhavik
 * Author URI:        https://beardog.digital/
 * Text Domain:       awesome-google-review
 */

define('AGR_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('AGR_PLUGIN_URL', plugin_dir_url(__FILE__));

// define('CUSTOM_HOST_URL', 'http://localhost:3000');
define('CUSTOM_HOST_URL', 'https://api.spiderdunia.com:3000');

include_once(ABSPATH . 'wp-admin/includes/plugin.php');

register_deactivation_hook(__FILE__, 'agr_deactivation_cron_clear');

register_uninstall_hook(__FILE__, 'agr_uninstall_data');

function agr_uninstall_data(){
    remove_custom_tables();
    flush_rewrite_rules();
}

// if any changes in this plugin

function agr_deactivation_cron_clear()
{   
    unregister_post_type('agr_google_review');
    wp_clear_scheduled_hook('first_daily_data');
    wp_clear_scheduled_hook('second_daily_data');
    flush_rewrite_rules();
}

function remove_custom_tables()
{
    global $wpdb;

    // Define table names
    $table_names = [
        $wpdb->prefix . 'jobdata',
        $wpdb->prefix . 'jobapi'
    ];

    // Remove tables
    foreach ($table_names as $table_name) {
        $wpdb->query("DROP TABLE IF EXISTS $table_name");
    }
}


global $pagenow;

// PLUGIN CHECKER = START
require_once 'update-checker/update-checker.php';

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$myUpdateChecker = PucFactory::buildUpdateChecker(
    'https://github.com/beaushowcase/awesome-google-review/',
    __FILE__,
    'awesome-google-review'
);
$myUpdateChecker->setBranch('main');
$myUpdateChecker->getVcsApi()->enableReleaseAssets();
// PLUGIN CHECKER = STOP

// check cron enable disable query
function check_cron_enable_or_disable()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'jobapi';
    $query = $wpdb->get_row($wpdb->prepare(
        "SELECT cron_status FROM $table_name WHERE review_api_key_status = 1 AND review_api_key != ''",
        ARRAY_A
    ));
    $cron_status = '';
    if ($query) {
        $cron_status = $query->cron_status;
    }
    return $cron_status;
}

$check_cron = check_cron_enable_or_disable();

if ($check_cron == 1) {
    require_once __DIR__ . '/assets/inc/cron.php';
} else {
    // REMOVE CRON
    $timestamp1 = wp_next_scheduled('first_daily_data');
    if ($timestamp1) {
        wp_unschedule_event($timestamp1, 'first_daily_data');
    }
    $timestamp2 = wp_next_scheduled('second_daily_data');
    if ($timestamp2) {
        wp_unschedule_event($timestamp2, 'second_daily_data');
    }
}


function get_dynamic_version()
{
    return time(); // Using the current timestamp as the version number
}
// Enqueue = START
function our_load_admin_style()
{
    global $pagenow;
    if ($pagenow == 'admin.php' && isset($_GET['page']) && ($_GET['page'] == 'awesome-google-review' || $_GET['page'] == 'delete-review' || $_GET['page'] == 'review-cron-job')) {
        // Enqueue jQuery
        wp_enqueue_script('jquery');

        $dynamic_version = get_dynamic_version();

        // Enqueue Styles
        wp_register_style('agr_style_css', plugins_url('/assets/css/style.css', __FILE__), [], $dynamic_version);
        wp_enqueue_style('agr_style_css');

        // wp_register_style('agr-sweetalert2-mincss', plugins_url('/assets/css/sweetalert2.min.css', __FILE__), [], $dynamic_version);
        wp_register_style('agr-sweetalert2-mincss', plugins_url('/assets/css/dark.css', __FILE__), [], $dynamic_version);
        wp_enqueue_style('agr-sweetalert2-mincss');

        // Enqueue Scripts with Dependencies
        wp_enqueue_script('agr-sweetalert2-minjs', plugins_url('/assets/js/sweetalert2.min.js', __FILE__), ['jquery'], $dynamic_version, true);
        wp_enqueue_script('agr-ajax-script', plugins_url('/assets/js/agr_ajax.js', __FILE__), ['jquery'], $dynamic_version, true);

        // Localize Script
        wp_localize_script('agr-ajax-script', 'ajax_object', ['ajax_url' => admin_url('admin-ajax.php'), 'main_site_url' => site_url(), 'admin_plugin_main_url' => esc_url(get_admin_url(null, 'admin.php?page=awesome-google-review')), 'get_url_page' => $_GET['page'], 'plugin_url' => plugins_url('', __FILE__), 'review_api_key' => get_existing_api_key()]);

        // Enqueue Custom Script with Dependencies
        wp_register_script('agr_custom', plugins_url('/assets/js/custom.js', __FILE__), ['jquery'], $dynamic_version, true);
        wp_enqueue_script('agr_custom');
    }
}
add_action('admin_enqueue_scripts', 'our_load_admin_style');
// Enqueue = END

function get_existing_firm_data()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'jobapi';
    $table_name2 = $wpdb->prefix . 'jobdata';
    $firm_data = $wpdb->get_row($wpdb->prepare(
        "
        SELECT j.firm_name, j.jobID, j.term_id
        FROM $table_name2 AS j
        INNER JOIN $table_name AS s ON j.review_api_key = s.review_api_key
        WHERE s.review_api_key_status = %d AND j.term_id = 0
        ORDER BY j.created DESC
        LIMIT 1",
        1
    ), ARRAY_A);
    return $firm_data;
}



// function get_all_firms(){
//     global $wpdb;
//     $table_name = $wpdb->prefix . 'jobapi';
//     $table_name2 = $wpdb->prefix . 'jobdata';
//     $client_ip = $_SERVER['REMOTE_ADDR'];    

//     $firm_data = $wpdb->get_results($wpdb->prepare("
//         SELECT j.firm_name, j.jobID
//         FROM $table_name2 AS j
//         INNER JOIN $table_name AS s ON j.review_api_key = s.review_api_key
//         WHERE j.client_ip = %s 
//         AND s.review_api_key_status = %d
//         ORDER BY j.jobID DESC", 
//         $client_ip, 1), ARRAY_A);

//     return $firm_data;
// }

function get_all_firms()
{

    $terms = get_terms(array(
        'taxonomy' => 'business',
        'hide_empty' => false,
    ));
    $term_data = array();
    foreach ($terms as $term) {

        $posts = get_posts(array(
            'post_type' => 'agr_google_review',
            'tax_query' => array(
                array(
                    'taxonomy' => $term->taxonomy,
                    'field' => 'term_id',
                    'terms' => $term->term_id,
                ),
            ),
        ));
        if ($posts) {
            $term_data[] = array(
                'name' => $term->name,
                'id' => $term->term_id,
            );
        }
    }

    return $term_data;
}


// set at locatization
// function get_existing_api_key(){
//     global $wpdb;
//     $table_name = $wpdb->prefix . 'jobapi';   
//     $client_ip = $_SERVER['REMOTE_ADDR'];
//     $api_key = $wpdb->get_var($wpdb->prepare("SELECT review_api_key FROM $table_name WHERE client_ip = %s", $client_ip));
//     return $api_key;
// }

function get_existing_api_key()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'jobapi';
    $api_key = $wpdb->get_var("SELECT review_api_key FROM $table_name ORDER BY id DESC LIMIT 1");
    return $api_key;
}

// function get_api_key_status($get_existing_api_key){
//     global $wpdb;
//     $client_ip = $_SERVER['REMOTE_ADDR'];
//     $table_name = $wpdb->prefix . 'jobapi';
//     $status = $wpdb->get_var($wpdb->prepare("SELECT review_api_key_status FROM $table_name WHERE client_ip = %s AND review_api_key = %d", $client_ip, $get_existing_api_key));      
//     return $status;
// }

function get_api_key_status()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'jobapi';
    $status = $wpdb->get_var("SELECT review_api_key_status FROM $table_name ORDER BY id DESC LIMIT 1");
    return $status;
}


function get_existing_api_key_data()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'jobapi';
    $last_record = $wpdb->get_row("SELECT * FROM $table_name ORDER BY id DESC LIMIT 1");
    return $last_record;
}


//business check
function get_existing_business_data()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'jobapi';
    $table_name2 = $wpdb->prefix . 'jobdata';
    $last_firm_name = $wpdb->get_var(
        $wpdb->prepare("
        SELECT j.firm_name
        FROM $table_name2 AS j
        INNER JOIN $table_name AS s ON j.review_api_key = s.review_api_key
        s.review_api_key_status = %d
        ORDER BY j.jobID DESC
        LIMIT 1", 1)
    );
    return $last_firm_name;
}


// function get_business_by_client_ip($client_ip){
//     global $wpdb;
//     $table_name = $wpdb->prefix . 'jobapi';
//     $table_name2 = $wpdb->prefix . 'jobdata';

//     $last_firm_name = $wpdb->get_var($wpdb->prepare("
//         SELECT j.firm_name
//         FROM $table_name2 AS j
//         INNER JOIN $table_name AS s ON j.review_api_key = s.review_api_key
//         WHERE j.client_ip = %s 
//         AND s.review_api_key_status = %d
//         ORDER BY j.jobID DESC
//         LIMIT 1", 
//         $client_ip, 1)
//     );

//     return $last_firm_name;
// }

// Function to append message to a log file with bullet point prefix
function appendMessageToFile($message)
{
    if ($message) {
        $folder_path = plugin_dir_path(__FILE__);
        $file_path = $folder_path . 'logs.txt';
        $current = file_get_contents($file_path);
        // Append message with bullet point prefix
        $current .= '- ' . $message . PHP_EOL;
        file_put_contents($file_path, $current);
    }
    return true;
}

//display logs.txt
function displayMessagesFromFile()
{
    $folder_path = plugin_dir_path(__FILE__);
    $file_path = $folder_path . 'logs.txt';

    if (file_exists($file_path)) {
        $content = file_get_contents($file_path);
        $lines = explode(PHP_EOL, $content);
        foreach ($lines as $line) {
            echo "$line<br>"; // Add <br> tag after each line
        }
    } else {
        echo "<p>No messages found.</p>";
    }
}


function get_job_data($job_id)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'jobdata';
    $where = array(
        'jobID' => $job_id
    );    
    $row = $wpdb->get_row($wpdb->prepare("SELECT jobID_json, jobID_check, jobID_check_status, jobID_final FROM $table_name WHERE jobID = %d", $job_id), ARRAY_A);
    if ($row) {
        return $row;
    } else {
        return false;
    }
}



// Include admin panel files.
require_once AGR_PLUGIN_PATH . 'assets/inc/admin_panel.php';

add_action('wp_ajax_initial_check_api', 'initial_check_api_function');
add_action('wp_ajax_nopriv_initial_check_api', 'initial_check_api_function');

function initial_check_api_function()
{
    $response = array(
        'success' => false,
        'api' => false,
        'data' => array()
    );

    $nonce = sanitize_text_field($_POST['nonce']);
    if (!isset($nonce) || !wp_verify_nonce($nonce, 'review_api_key')) {
        $response['message'] = 'Invalid nonce !';
        wp_die();
    }

    // ptr(get_existing_api_key_data());exit;

    if (get_existing_api_key_data()->review_api_key_status == 1) {
        $response['api'] = true;
    }

    $current_job_id = isset($_POST['current_job_id']) ? sanitize_text_field($_POST['current_job_id']) : '';
    $get_job_data   = get_job_data($current_job_id);

    // ptr($current_job_id);exit;

    $btn_start = intval($get_job_data['jobID_json']);
    $btn_check = intval($get_job_data['jobID_check']);
    $btn_check_status = intval($get_job_data['jobID_check_status']);
    $btn_upload = intval($get_job_data['jobID_final']);

    if (isset($btn_start)) {
        $response['data']['btn_start'] = $btn_start;
    }

    if (isset($btn_check)) {
        $response['data']['btn_check'] = $btn_check;
    }

    if (isset($btn_check_status)) {
        $response['data']['btn_check_status'] = $btn_check_status;
    }

    if (isset($btn_upload)) {
        $response['data']['btn_upload'] = $btn_upload;
    }

    $response['success'] = true;
    wp_send_json($response);
    wp_die();
}


function set_table_required($tname)
{
    global $wpdb;
    $table_name = $wpdb->prefix . $tname;
    return $table_name;
}

function save_data_to_table($table_name, $data)
{
    global $wpdb;

    // Check if the table is empty
    $is_table_empty = $wpdb->get_var("SELECT COUNT(*) FROM $table_name") == 0;

    $data_array = [
        'review_api_key' => $data['review_api_key'],
        'review_api_key_status' => $data['review_api_key_status'],
    ];

    if ($is_table_empty) {
        $result = $wpdb->insert($table_name, $data_array);
        return $result !== false;
    } else {
        // Get the ID of the last row
        $last_row_id = $wpdb->get_var("SELECT MAX(id) FROM $table_name");

        // Construct the WHERE clause
        $where = ['id' => $last_row_id];

        // Perform the update
        $result = $wpdb->update($table_name, $data_array, $where);
        return $result !== false;
    }
}

add_action('wp_ajax_review_api_key_ajax_action', 'review_api_key_ajax_action_function');
add_action('wp_ajax_nopriv_review_api_key_ajax_action', 'review_api_key_ajax_action_function');

function review_api_key_ajax_action_function()
{
    $response = array(
        'success' => 0,
        'data'    => array('api' => ''),
        'msg'     => array('')
    );

    $nonce = sanitize_text_field($_POST['nonce']);
    $review_api_key = sanitize_text_field($_POST['review_api_key']);

    $table_name = set_table_required('jobapi');

    // $client_ip = $_SERVER['REMOTE_ADDR'];

    // $serialized_data = serialize($data);
    if (!empty($nonce) && wp_verify_nonce($nonce, 'review_api_key')) {
        $response_api_data = invalidApiKey($review_api_key);

        if ($response_api_data['success'] === 1) {

            $data = array(
                'review_api_key' => $review_api_key,
                'review_api_key_status' => 1,
                // 'client_ip' => $client_ip,
            );

            $success = save_data_to_table($table_name, $data);

            $response['data']['api'] = $response_api_data['data']['api'];
            $response['success'] = $response_api_data['success'];
            $response['msg'] = $response_api_data['msg'];
        } else {

            $data = array(
                'review_api_key' => $review_api_key,
                'review_api_key_status' => 0,
                // 'client_ip' => $client_ip,
            );
            $success = save_data_to_table($table_name, $data);

            $response['data']['api'] = $response_api_data['data']['api'];
            $response['success'] = $response_api_data['success'];
            $response['msg'] = $response_api_data['msg'];
        }
    } else {
        $response['msg'] = 'Invalid nonce.';
    }

    appendMessageToFile($response['msg']);
    wp_send_json($response);
    wp_die();
}



function invalidApiKey($review_api_key)
{
    $api_response = array(
        'success' => 0,
        'data'    => array('api' => 0),
        'msg'     => array('')
    );
    $api_endpoint = '/validateApiKey';
    $api_url = CUSTOM_HOST_URL . $api_endpoint;   
    $headers = array(
        'Content-Type' => 'application/json', // Update content type to JSON
    );
    $query_params = array(
        'api_key' => $review_api_key, // Pass the API key as a query parameter
    );
    $api_url = add_query_arg($query_params, $api_url); // Add the query parameter to the URL    

    // Make a GET request to the Express.js endpoint
    $response = wp_remote_get($api_url, array(
        'headers' => $headers,
        'timeout' => 20,
    ));



    if (is_wp_error($response)) {
        $api_response['data']['api'] = 0;
        $api_response['success'] = 0;
        $api_response['msg'] = $response->get_error_message();
    } else {
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        if (isset($data['success']) && $data['success']) {
            $api_response['data']['api'] = 1;
            $api_response['success'] = 1;
            $api_response['msg'] = 'API key is valid';
        } else {
            $api_response['data']['api'] = 0;
            $api_response['success'] = 0;
            $api_response['msg'] = isset($data['error']) ? $data['error'] : 'Invalid API key.';
        }
    }
    // appendMessageToFile($api_response['msg']);
    return $api_response;
}

function ptr($str)
{
    echo "<pre>";
    print_r($str);
}

// $table_name = $wpdb->prefix . 'jobdata';
// $client_ip = $_SERVER['REMOTE_ADDR'];
// $get_current_job_id = $wpdb->get_var($wpdb->prepare("SELECT jobID FROM $table_name WHERE review_api_key = %s AND jobID_json = %d AND jobID_check = %d AND client_ip = %s", $review_api_key, 1, 1, $client_ip));    

function check_verify_file($current_job_id, $review_api_key)
{
    global $wpdb;
    $parent_dir = plugin_dir_path(__FILE__);
    $folder_path = $parent_dir . 'jobdata';

    $file_path = $folder_path . '/' . $current_job_id . '.json';

    if (file_exists($file_path)) {
        $json_contents = file_get_contents($file_path);
        $json_array = json_decode($json_contents, true);
        if ($json_array !== null) {
            return $json_array;
        } else {
            return false;
        }
    } else {
        return false;
    }
}

function get_reviews_data($current_job_id, $review_api_key)
{
    if (empty($review_api_key)) {
        return;
    }
    $api_response['current_job_id'] = $current_job_id;
    $api_response['success'] = 0;
    $api_response['message'] = '';
    $api_response['reviews'] = array();

    $get_existing_api_key = get_existing_api_key();
    $status = get_api_key_status($get_existing_api_key);

    if ($status == 1) {
        $check_verify_file = check_verify_file($current_job_id, $review_api_key);

        if ($check_verify_file) {
            $api_response['message'] = 'Data verified successful..';
            $api_response['reviews'] = $check_verify_file;
            $api_response['success'] = 1;
        } else {
            $api_response['message'] = 'verified failed !';
        }
    } else {
        $api_response['message'] = 'Something went wrong with upload !';
    }

    return $api_response;
}


// Define AJAX action hooks
add_action('wp_ajax_job_start_ajax_action', 'job_start_ajax_action_function');
add_action('wp_ajax_nopriv_job_start_ajax_action', 'job_start_ajax_action_function');

// AJAX callback function
function job_start_ajax_action_function()
{
    global $wpdb;

    // Initialize response array
    $response = array(
        'success' => 0,
        'data'    => array('jobID' => ''),
        'msg'     => ''
    );

    // Sanitize input data
    $nonce         = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';
    $review_api_key = isset($_POST['review_api_key']) ? sanitize_text_field($_POST['review_api_key']) : '';
    $encoded_firm = $_POST['firm_name'];
    $decoded_firm_name = urldecode($_POST['firm_name']);    
    $firm_name     = isset($decoded_firm_name) ? $decoded_firm_name : '';

    // Verify nonce
    if (!empty($nonce) && wp_verify_nonce($nonce, 'get_set_trigger')) {
        // Call API to start job
        $response_api_data = job_start_at_api($review_api_key, $encoded_firm);

        // Check API response
        if ($response_api_data['success']) {
            $jobID = $response_api_data['data']['jobID'];

            // Retrieve client IP
            $table_name = $wpdb->prefix . 'jobapi';
            // $c_ip = $wpdb->get_var($wpdb->prepare("SELECT client_ip FROM $table_name WHERE review_api_key = %s AND review_api_key_status = %d", $review_api_key, 1));

            if ($wpdb->last_error) {
                $response['msg'] = "Database Error: " . $wpdb->last_error;
            } else {
                // Prepare data for insertion/update
                $data = array(
                    'jobID' => $jobID,
                    'jobID_json' => 1,
                    'jobID_check' => 0,
                    'jobID_check_status' => 0,
                    'jobID_final' => 0,
                    'term_id' => 0,
                    'review_api_key' => $review_api_key,
                    'firm_name' => $firm_name,
                    // 'client_ip' => $c_ip,
                    'created' => current_time('mysql')
                );

                $existing_jobID = $wpdb->get_var($wpdb->prepare("SELECT jobID FROM {$wpdb->prefix}jobdata WHERE jobID = %s", $jobID));

                // Insert/update job data
                if ($existing_jobID !== null) {
                    $where = array('jobID' => $jobID);
                    $result = $wpdb->update($wpdb->prefix . 'jobdata', $data, $where, array('%d'), array('%s'));
                } else {
                    $result = $wpdb->insert($wpdb->prefix . 'jobdata', $data, array('%s', '%d'));
                }

                // Check insertion/update result
                if ($result !== false) {
                    $response['data']['jobID'] = $jobID;
                    $response['success'] = 1;
                    $response['msg'] = $response_api_data['msg'];
                } else {
                    $response['msg'] = "Database Error: Failed to insert/update job data.";
                }
            }
        } else {
            $response['msg'] = "API Error: " . $response_api_data['msg'];
        }
    } else {
        $response['msg'] = 'Invalid nonce.';
    }

    // Send JSON response
    appendMessageToFile($response['msg']);
    wp_send_json($response);
    wp_die();
}

// Function to start job at API
function job_start_at_api($review_api_key, $firm_name)
{
    $api_response = array(
        'success' => 0,
        'data'    => array('jobID' => 0),
        'msg'     => ''
    );    
    $api_endpoint = '/scrape';
    $api_url = CUSTOM_HOST_URL . $api_endpoint;
    
    $headers = array(
        'Content-Type' => 'application/json',
    );
    $query_params = array(
        'api_key' => $review_api_key,
        'term' => $firm_name,
    );
    $api_url = add_query_arg($query_params, $api_url);

    // Make a GET request to the Express.js endpoint
    $response = wp_remote_get($api_url, array(
        'headers' => $headers,
        'timeout' => 20,
    ));

    if (is_wp_error($response)) {
        $api_response['msg'] = $response->get_error_message();
    } else {
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        if (isset($data['success']) && $data['success']) {
            $api_response['data']['jobID'] = $data['jobID'];
            $api_response['success'] = 1;
            $api_response['msg'] = $data['message'];
        } else {
            $api_response['msg'] = isset($data['error']) ? $data['error'] : 'Something went wrong!';
        }
    }

    return $api_response;
}



//job start to check job
function job_check_at_api($review_api_key, $current_job_id)
{
    $api_response = array(
        'success' => 0,
        'data'    => array('jobID' => 0),
        'msg'     => array('')
    );    
    $api_endpoint = '/events';
    $api_url = CUSTOM_HOST_URL . $api_endpoint;
    $headers = array(
        'Content-Type' => 'application/json', // Update content type to JSON
    );
    $query_params = array(
        'api_key' => $review_api_key,
        'id' => $current_job_id,
    );
    $api_url = add_query_arg($query_params, $api_url);

    // Make a GET request to the Express.js endpoint
    $response = wp_remote_get($api_url, array(
        'headers' => $headers,
        'timeout' => 20,
    ));

    if (is_wp_error($response)) {
        $api_response['data']['jobID'] = 0;
        $api_response['success'] = 0;
        $api_response['msg'] = $response->get_error_message();
    } else {
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        $file_save = save_json_response_to_file($current_job_id, $data);
        if (isset($file_save) && $file_save) {
            $status_flag = 1;
            $status_label = 'error';
            if ($data['error']) {
                $data['message'] = $data['error'];
                $status_label = 'success';
                $status_flag = 0;
            }
            $api_response['data']['jobID'] = $current_job_id;
            $api_response['success'] = $status_flag;
            $api_response['msg'] = $data['message'];
        } else {
            $api_response['data']['jobID'] = 0;
            $api_response['success'] = 0;
            $api_response['msg'] = isset($data['error']) ? $data['error'] : 'something went wrong !';
        }
    }

    // appendMessageToFile($api_response['msg']);
    return $api_response;
}


// Function to save JSON response to a file
function save_json_response_to_file($current_job_id, $data)
{
    $parent_dir = plugin_dir_path(__FILE__);
    $folder_path = $parent_dir . 'jobdata';
    if (!file_exists($folder_path) && !is_dir($folder_path)) {
        mkdir($folder_path, 0755, true);
    }
    $file_path = $folder_path . '/' . $current_job_id . '.json';
    $json_data = json_encode($data, JSON_PRETTY_PRINT);
    $saved = file_put_contents($file_path, $json_data);
    if ($saved !== false) {
        return true;
    } else {
        return false;
    }
}


//check job
add_action('wp_ajax_job_check_ajax_action', 'job_check_ajax_action_function');
add_action('wp_ajax_nopriv_job_check_ajax_action', 'job_check_ajax_action_function');

function job_check_ajax_action_function()
{
    global $wpdb;
    $response = array(
        'success' => 0,
        'data'    => array('jobID' => ''),
        'msg'     => ''
    );
    $nonce         = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';
    $review_api_key = isset($_POST['review_api_key']) ? sanitize_text_field($_POST['review_api_key']) : '';
    $current_job_id     = isset($_POST['current_job_id']) ? sanitize_text_field($_POST['current_job_id']) : '';

    if (!empty($nonce) && wp_verify_nonce($nonce, 'get_set_trigger')) {
        $response_api_data = job_check_at_api($review_api_key, $current_job_id);

        // ptr($response_api_data);exit;

        if ($response_api_data['success']) {

            $jobID = $response_api_data['data']['jobID'];

            $response['data']['jobID'] = $jobID;
            $response['success'] = 1;
            $response['msg'] = $response_api_data['msg'];

            $table_name = $wpdb->prefix . 'jobapi';
            // $c_ip = $wpdb->get_var($wpdb->prepare("SELECT client_ip FROM $table_name WHERE review_api_key = %s AND review_api_key_status = %d", $review_api_key, 1));

            if ($wpdb->last_error) {
                $response['msg'] = "Database Error: " . $wpdb->last_error;
            } else {
                $existing_jobID = $wpdb->get_var(
                    $wpdb->prepare(
                        "SELECT jobID FROM {$wpdb->prefix}jobdata WHERE jobID = %s",
                        $jobID
                    )
                );

                $data2 = array(
                    'jobID_check' => 1,
                    'jobID_check_status' => 1,
                    'created' => current_time('mysql')
                );

                if ($existing_jobID !== null) {
                    $where = array('jobID' => $jobID, 'jobID_json' => 1);
                    $result = $wpdb->update($wpdb->prefix . 'jobdata', $data2, $where);
                } else {
                    $data2['review_api_key'] = $review_api_key;
                    $data2['created'] = current_time('mysql');
                    $result = $wpdb->insert($wpdb->prefix . 'jobdata', $data2);
                }
                if ($result !== false) {
                    $response['data']['jobID'] = $jobID;
                    $response['success'] = 1;
                    $response['msg'] = $response_api_data['msg'];
                } else {
                    $response['msg'] = "Database Error: Failed to insert/update job data.";
                }
            }
        } else {

            $jobID = $response_api_data['data']['jobID'];

            $response['data']['jobID'] = $jobID;
            $response['success'] = 1;
            $response['msg'] = $response_api_data['msg'];

            $table_name = $wpdb->prefix . 'jobapi';
            // $c_ip = $wpdb->get_var($wpdb->prepare("SELECT client_ip FROM $table_name WHERE review_api_key = %s AND review_api_key_status = %d", $review_api_key, 1));           

            if ($wpdb->last_error) {
                $response['msg'] = "Database Error: " . $wpdb->last_error;
            } else {
                $existing_jobID = $wpdb->get_var(
                    $wpdb->prepare(
                        "SELECT jobID FROM {$wpdb->prefix}jobdata WHERE jobID = %s",
                        $jobID
                    )
                );

                $data2 = array(
                    'jobID_check' => 0,
                    'jobID_check_status' => 0,
                    'jobID_json' => 0,
                    'jobID_final' => 0,
                    'created' => current_time('mysql')
                );

                if ($existing_jobID !== null) {
                    $where = array('jobID' => $jobID);
                    $result = $wpdb->update($wpdb->prefix . 'jobdata', $data2, $where);
                } else {
                    $data2['review_api_key'] = $review_api_key;
                    $data2['created'] = current_time('mysql');
                    $result = $wpdb->insert($wpdb->prefix . 'jobdata', $data2);
                }

                if ($result !== false) {
                    $response['data']['jobID'] = $jobID;
                    $response['success'] = 0;
                    $response['msg'] = $response_api_data['msg'];
                } else {
                    $response['msg'] = "Database Error: Failed to insert/update job data.";
                }
            }

            // $response['msg'] = "API Error: " . $response_api_data['msg'];
        }
    } else {
        $response['msg'] = 'Invalid nonce.';
    }

    appendMessageToFile($response['msg']);

    wp_send_json($response);
    wp_die();
}

add_action('wp_ajax_review_get_set_ajax_action', 'review_get_set_ajax_action_function');
add_action('wp_ajax_nopriv_review_get_set_ajax_action', 'review_get_set_ajax_action_function');

function review_get_set_ajax_action_function()
{

    $response = [];
    $response['job_id'] = '';
    $response['success'] = 0;
    $response['message'] = '';
    $response['term_slug'] = '';
    $response['data'] = array();
    $nonce = sanitize_text_field($_POST['nonce']);
    $current_job_id = sanitize_text_field($_POST['current_job_id']);
    $review_api_key = sanitize_text_field($_POST['review_api_key']);

    if (!empty($nonce) && wp_verify_nonce($nonce, 'get_set_trigger')) {
        $reviews_array = get_reviews_data($current_job_id, $review_api_key);
        // $reviews_array = $reviews_array['reviews'];

        // echo "fdafadf";ptr($reviews_array);exit;


        if ($reviews_array['success'] == 0) {
            $response['job_id'] = 0;
            $response['message'] = $reviews_array['message'];
        } else {
            $reviews_data = $reviews_array['reviews'];
            $response['job_id'] = $current_job_id;
            $response['data'] = $reviews_data['reviews'];

            $post_type = 'agr_google_review';
            $taxonomy = 'business';

            $term_name = $reviews_array['reviews']['firm_name'];
            $term_slug = sanitize_title($reviews_array['reviews']['firm_name']);

            delete_reviews_data($term_slug);

            // upload all reviews
            $data_stored = store_data_into_reviews($current_job_id, $reviews_array, $term_name);

            if ($data_stored['status'] == 1) {
                update_flag('jobID_final', 1, $current_job_id);
                update_flag('term_id', $data_stored['term_id'], $current_job_id);
                $response['term_slug'] = $term_slug;
                $response['message'] = "Data upload successfully!";
                $response['success'] = 1;
            } else {
                update_flag('jobID_final', 0, $current_job_id);
                $response['message'] = "Failed to store data.";
            }

            // exit;
            // add_option('firm_name', $firm_name);
            // if (get_option('firm_name') !== false) {
            //     update_option('firm_name', $firm_name);
            // } 
            // add_option('business_valid', 1);          
            // if (get_option('firm_name') !== false) {
            //     update_option('business_valid', 1);
            // }
        }
    } else {
        $response['message'] = 'Nonce is not valid !';
    }

    appendMessageToFile($response['message']);

    wp_send_json($response);
    wp_die();
}


// delete all data from review post type
function delete_reviews_data($term_slug)
{

    $term = get_term_by('slug', $term_slug, 'business');

    if ($term) {
        $args = array(
            'post_type' => 'agr_google_review',
            'posts_per_page' => -1,
            'tax_query' => array(
                array(
                    'taxonomy' => 'business',
                    'field' => 'id',
                    'terms' => $term->term_id,
                ),
            ),
        );

        // Get posts to be deleted
        $posts_to_delete = get_posts($args);

        // Loop through each post and delete it
        foreach ($posts_to_delete as $post) {
            wp_delete_post($post->ID, true);

            // Delete associated post meta
            delete_post_meta($post->ID, 'job_id');
            delete_post_meta($post->ID, 'post_review_id');
            delete_post_meta($post->ID, 'reviewer_name');
            delete_post_meta($post->ID, 'reviewer_picture_url');
            delete_post_meta($post->ID, 'url');
            delete_post_meta($post->ID, 'rating');
            delete_post_meta($post->ID, 'text');
            delete_post_meta($post->ID, 'publish_date');
        }

        // Optionally, clean up any orphaned post meta
        global $wpdb;
        $wpdb->query($wpdb->prepare("DELETE FROM $wpdb->postmeta WHERE post_id NOT IN (SELECT ID FROM $wpdb->posts WHERE post_type = 'agr_google_review')"));

        return count($posts_to_delete); // Return the number of deleted posts
    } else {
        return 0; // Return 0 if the term does not exist
    }
}



function store_data_into_reviews($current_job_id, $reviews_array, $term_name)
{
    $success = [];
    $success['status'] = false; // Flag to track if data is stored successfully
    $success['term_id'] = '';

    $append = true;
    $taxonomy = 'business';
    $term = get_term_by('name', $term_name, $taxonomy);
    if ($term == false) {
        $term = wp_insert_term($term_name, $taxonomy);
        $term_id = $term['term_id'];
    } else {
        $term_id = $term->term_id;
    }

    $success['term_id'] = $term_id;



    $reviews_array_data = $reviews_array['reviews']['reviews'];

    if ($term_id) {
        foreach ($reviews_array_data as $get_review) {
            $id = $get_review['id'];
            $reviewer_name = $get_review['title'];
            $reviewer_picture_url = $get_review['reviewerPictureUrl'];
            $reviewer_read_more = $get_review['reviewerUrl'];
            $rating = $get_review['numericRatingCount'];
            $text = $get_review['description'];
            $published_at = $get_review['publicationDate'];

            // Check if the post with the given ID exists based on custom meta field
            $existing_post_id = get_post_id_by_meta('post_review_id', $id, 'agr_google_review');

            $post_data = array(
                'post_title'    => $reviewer_name,
                'post_type'     => 'agr_google_review',
                'post_status'   => 'publish',
                'orderby'        => 'date',
                'order'          => 'DESC',
            );

            // If post with ID exists, update the post
            if ($existing_post_id) {
                $post_data['ID'] = $existing_post_id;
                $new_post_id = wp_update_post($post_data);
            } else {
                // Otherwise, insert a new post
                $new_post_id = wp_insert_post($post_data);
            }

            // Update the post meta with the review ID
            if ($new_post_id) {
                update_post_meta($new_post_id, 'job_id', $current_job_id);
                update_post_meta($new_post_id, 'post_review_id', $id);
                update_post_meta($new_post_id, 'reviewer_name', $reviewer_name);
                update_post_meta($new_post_id, 'reviewer_picture_url', $reviewer_picture_url);
                update_post_meta($new_post_id, 'url', $reviewer_read_more);
                update_post_meta($new_post_id, 'rating', $rating);
                update_post_meta($new_post_id, 'text', $text);
                update_post_meta($new_post_id, 'publish_date', $published_at);

                // Assign the 'business' taxonomy term to the post
                // $term_taxonomy_ids = wp_set_object_terms($new_post_id, $term_id, 'business', true);
                $term_taxonomy_ids = wp_set_post_terms($new_post_id, $term_id, $taxonomy, $append);
                if (!is_wp_error($term_taxonomy_ids)) {
                    $success['status'] = true;
                }
            }
        }
    }

    if ($success['status'] == true) {
        $success['term_id'] == $term_id;
    }



    // Return 1 if data is stored successfully, otherwise return 0
    return $success;
}


function get_post_id_by_meta($meta_key, $meta_value, $post_type = 'agr_google_review')
{
    $args = array(
        'post_type'  => $post_type,
        'meta_key'   => $meta_key,
        'meta_value' => $meta_value,
        'fields'     => 'ids',
    );

    $posts = get_posts($args);

    if (!empty($posts)) {
        return $posts[0];
    }

    return 0;
}



// reset process action data

// job start
add_action('wp_ajax_job_reset_ajax_action', 'job_reset_ajax_action_function');
add_action('wp_ajax_nopriv_job_reset_ajax_action', 'job_reset_ajax_action_function');

function job_reset_ajax_action_function()
{
    global $wpdb;
    $response = array(
        'success' => 0,
        'data'    => array('jobID' => ''),
        'msg'     => ''
    );
    $current_job_id     = isset($_POST['current_job_id']) ? sanitize_text_field($_POST['current_job_id']) : '';
    $review_api_key     = isset($_POST['review_api_key']) ? sanitize_text_field($_POST['review_api_key']) : '';
    $firm_name     = isset($_POST['firm_name']) ? sanitize_text_field($_POST['firm_name']) : '';

    if (!empty($current_job_id)) {
        $jobID = $current_job_id;
        $table_name = $wpdb->prefix . 'jobapi';
        if ($wpdb->last_error) {
            $response['msg'] = "Database Error: " . $wpdb->last_error;
        } else {
            $existing_jobID = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT jobID FROM {$wpdb->prefix}jobdata WHERE jobID = %s",
                    $jobID
                )
            );
            if ($existing_jobID !== null) {
                delete_file($jobID);
                $where = array('jobID' => $jobID);
                $data = array('jobID_json' => 0, 'jobID_check' => 0, 'jobID_check_status' => 0, 'jobID_final' => 0);
                $result = $wpdb->update($wpdb->prefix . 'jobdata', $data, $where, array('%d', '%d'), array('%s', '%s'));

                $where_delete = array(
                    'jobID_json' => 0,
                    'jobID_check' => 0,
                    'jobID_check_status' => 0,
                    'jobID_final' => 0,
                    'term_id' => 0
                );
                $delete_result = $wpdb->delete($wpdb->prefix . 'jobdata', $where_delete, array('%d', '%d', '%d', '%d', '%d'));

                if ($result !== false) {
                    $response['msg'] = 'Reset and deleted data successfully ';
                    $response['data']['jobID'] = $jobID;
                    $response['success'] = 1;
                } else {
                    $response['msg'] = "Database Error: Failed to update job data.";
                }
            } else {
                $response['msg'] = "Database Error: No existing jobID found for the provided detail.";
            }
        }
    } else {
        $response['msg'] = 'Something went wrong !';
    }

    appendMessageToFile($response['msg']);
    // clearLogFile();


    wp_send_json($response);
    wp_die();
}

function delete_file($jobID)
{
    $parent_dir = plugin_dir_path(__FILE__);
    $folder_path = $parent_dir . 'jobdata';
    $file_path = $folder_path . '/' . $jobID . '.json';
    if (file_exists($file_path)) {
        unlink($file_path);
    }
    return true;
}



// clear logs
add_action('wp_ajax_job_reset_logs_ajax_action', 'job_reset_logs_ajax_action_function');
add_action('wp_ajax_nopriv_job_reset_logs_ajax_action', 'job_reset_logs_ajax_action_function');

function job_reset_logs_ajax_action_function()
{
    global $wpdb;
    $response = array(
        'success' => 0,
        'msg'     => ''
    );
    $review_api_key     = isset($_POST['review_api_key']) ? sanitize_text_field($_POST['review_api_key']) : '';

    if (!empty($review_api_key) && clearLogFile()) {
        $response['msg'] = 'Logs reset successfully !';
        $response['success'] = 1;
    } else {
        $response['msg'] = 'Something went wrong while resetting logs !';
    }
    appendMessageToFile($response['msg']);
    wp_send_json($response);
    wp_die();
}


// Function to clear the text file
function clearLogFile()
{
    $folder_path = plugin_dir_path(__FILE__);
    $file_path = $folder_path . 'logs.txt';
    if (file_exists($file_path)) {
        if (file_put_contents($file_path, '') !== false) {
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}


//upload jobs
add_action('wp_ajax_job_upload_ajax_action', 'job_upload_ajax_action_function');
add_action('wp_ajax_nopriv_job_upload_ajax_action', 'job_upload_ajax_action_function');

function job_upload_ajax_action_function()
{

    $response = array(
        'success' => 0,
        'data'    => array('jobID' => ''),
        'msg'     => 'final success'
    );

    $response['success'] = 1;
    appendMessageToFile($response['msg']);

    wp_send_json($response);
    wp_die();
}


function update_flag($column_name, $value, $job_id)
{
    global $wpdb;

    // Prepare the table name
    $table_name = $wpdb->prefix . 'jobdata';

    // Prepare the data to be updated
    $data = array(
        $column_name => $value
    );

    // Prepare the where clause
    $where = array(
        'jobID' => $job_id
    );

    // Update the row in the database
    $updated = $wpdb->update($table_name, $data, $where);

    // Check if the update was successful
    if ($updated !== false) {
        return true; // Updated successfully
    } else {
        return false; // Failed to update
    }
}


// delete requested reviewby term
add_action('wp_ajax_job_review_delete_ajax_action', 'job_review_delete_ajax_action_function');
add_action('wp_ajax_nopriv_job_review_delete_ajax_action', 'job_review_delete_ajax_action_function');

function job_review_delete_ajax_action_function()
{
    global $wpdb;
    $response = array(
        'success' => 0,
        'data'    => array('jobID' => ''),
        'msg'     => ''
    );
    $current_term_id     = isset($_POST['current_term_id']) ? sanitize_text_field($_POST['current_term_id']) : '';
    $review_api_key     = isset($_POST['review_api_key']) ? sanitize_text_field($_POST['review_api_key']) : '';

    if (!empty($current_term_id)) {

        $table_name = $wpdb->prefix . 'jobapi';
        // $c_ip = $wpdb->get_var($wpdb->prepare("SELECT client_ip FROM $table_name WHERE review_api_key = %s AND review_api_key_status = %d", $review_api_key, 1));

        if ($wpdb->last_error) {
            $response['msg'] = "Database Error: " . $wpdb->last_error;
        } else {
            $existing_termID = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT term_id FROM {$wpdb->prefix}jobdata WHERE term_id = %s",
                    $current_term_id,
                    $c_ip
                )
            );

            if ($existing_termID !== null) {
                $delete = delete_reviews_by_term_id($existing_termID);
                $firm = get_firm_name_by_term_id($existing_termID);
                $msg = 'Deleted data successfully';
                if ($firm) {
                    $msg = 'Deleted data of ' . $firm . '.';
                }
                if ($delete !== false) {
                    $wpdb->delete(
                        $wpdb->prefix . 'jobdata',
                        array('term_id' => $existing_termID),
                        array('%d')
                    );
                    $response['msg'] = $msg;
                    $response['data']['current_term_id'] = $current_term_id;
                    $response['success'] = 1;
                } else {
                    $response['msg'] = "Database Error: Failed to update job data.";
                }
            } else {
                $response['msg'] = "Database Error: No existing jobID found.";
            }
        }
    } else {
        $response['msg'] = 'Something went wrong !';
    }
    appendMessageToFile($response['msg']);
    wp_send_json($response);
    wp_die();
}



// delete reviews from backend by termid
function delete_reviews_by_term_id($existing_termID)
{
    global $wpdb;
    $post_type = 'agr_google_review';
    $taxonomy = 'business';
    $post_deletion_result = $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$wpdb->posts} WHERE post_type = %s AND ID IN (SELECT object_id FROM {$wpdb->term_relationships} WHERE term_taxonomy_id = %d)",
            $post_type,
            $existing_termID
        )
    );
    if ($post_deletion_result === false) {
        return false;
    }
    $term_deletion_result = wp_delete_term($existing_termID, $taxonomy);
    if (is_wp_error($term_deletion_result)) {
        return false;
    }
    $orphan_records_deletion_result = $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$wpdb->term_relationships} WHERE term_taxonomy_id NOT IN (SELECT term_taxonomy_id FROM {$wpdb->term_taxonomy}) AND object_id NOT IN (SELECT ID FROM {$wpdb->posts} WHERE post_type = %s)",
            $post_type
        )
    );
    if ($orphan_records_deletion_result === false) {
        return false;
    }
    return true;
}



// get_firm_name_by_term_id
function get_firm_name_by_term_id($current_term_id)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'jobdata';
    $query = $wpdb->prepare("
        SELECT firm_name 
        FROM $table_name 
        WHERE term_id = %d", $current_term_id);
    $result = $wpdb->get_var($query);
    return $result;
}


// Check status
add_action('wp_ajax_job_check_status_update_ajax_action', 'job_check_status_update_ajax_action_function');
add_action('wp_ajax_nopriv_job_check_status_update_ajax_action', 'job_check_status_update_ajax_action_function');

function job_check_status_update_ajax_action_function()
{

    global $wpdb;
    $response = array(
        'success' => 0,
        'data'    => array('jobID' => ''),
        'msg'     => ''
    );
    $nonce         = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';
    $review_api_key = isset($_POST['review_api_key']) ? sanitize_text_field($_POST['review_api_key']) : '';
    $current_job_id     = isset($_POST['current_job_id']) ? sanitize_text_field($_POST['current_job_id']) : '';

    if (!empty($nonce) && wp_verify_nonce($nonce, 'get_set_trigger')) {
        $response_api_data = job_check_status_at_api($review_api_key, $current_job_id);

        if ($response_api_data['success'] && $response_api_data['state'] == 1) {

            $jobID = $response_api_data['data']['jobID'];

            $response['data']['jobID'] = $jobID;
            $response['success'] = 1;
            $response['msg'] = $response_api_data['msg'];

            $table_name = $wpdb->prefix . 'jobapi';
            // $c_ip = $wpdb->get_var($wpdb->prepare("SELECT client_ip FROM $table_name WHERE review_api_key = %s AND review_api_key_status = %d", $review_api_key, 1));

            if ($wpdb->last_error) {
                $response['msg'] = "Database Error: " . $wpdb->last_error;
            } else {
                $existing_jobID = $wpdb->get_var(
                    $wpdb->prepare(
                        "SELECT jobID FROM {$wpdb->prefix}jobdata WHERE jobID = %s",
                        $jobID
                    )
                );

                $data2 = array(
                    'jobID_check_status' => 1,
                    'created' => current_time('mysql')
                );

                if ($existing_jobID !== null) {
                    $where = array('jobID' => $jobID, 'jobID_json' => 1);
                    $result = $wpdb->update($wpdb->prefix . 'jobdata', $data2, $where);
                } else {
                    $data2['review_api_key'] = $review_api_key;
                    $data2['created'] = current_time('mysql');
                    $result = $wpdb->insert($wpdb->prefix . 'jobdata', $data2);
                }
                if ($result !== false) {
                    $response['data']['jobID'] = $jobID;
                    $response['success'] = 1;
                    $response['msg'] = $response_api_data['msg'];
                } else {
                    $response['msg'] = "Database Error: Failed to insert/update job data.";
                }
            }
        } else {

            $jobID = $response_api_data['data']['jobID'];

            $response['data']['jobID'] = $jobID;
            $response['success'] = 1;
            $response['msg'] = $response_api_data['msg'];

            $table_name = $wpdb->prefix . 'jobapi';
            // $c_ip = $wpdb->get_var($wpdb->prepare("SELECT client_ip FROM $table_name WHERE review_api_key = %s AND review_api_key_status = %d", $review_api_key, 1));           

            if ($wpdb->last_error) {
                $response['msg'] = "Database Error: " . $wpdb->last_error;
            } else {
                $existing_jobID = $wpdb->get_var(
                    $wpdb->prepare(
                        "SELECT jobID FROM {$wpdb->prefix}jobdata WHERE jobID = %s",
                        $jobID
                    )
                );
                // $data2 = array(
                //     'jobID_check' => 0,
                //     'jobID_check_status' => 0,
                //     'jobID_json' => 0,
                //     'jobID_final' => 0,
                //     'created' => current_time('mysql')
                // );

                if ($existing_jobID !== null) {
                    $response['data']['jobID'] = $jobID;
                    $response['success'] = 0;
                    $response['msg'] = 'Check Again !';
                    // $where = array('jobID' => $jobID , 'client_ip' => $c_ip);
                    // $result = $wpdb->update($wpdb->prefix . 'jobdata', $data2, $where);

                } else {
                    $response['data']['jobID'] = $jobID;
                    $response['success'] = 0;
                    $response['msg'] = 'Check Again !';
                }
                //  else {
                //     $data2['review_api_key'] = $review_api_key;
                //     $data2['created'] = current_time('mysql');
                //     $result = $wpdb->insert($wpdb->prefix . 'jobdata', $data2);
                // }

                // if ($result !== false) {
                //     $response['data']['jobID'] = $jobID;
                //     $response['success'] = 0;
                //     $response['msg'] = $response_api_data['msg'];
                // } else {
                //     $response['msg'] = "Database Error: Failed to insert/update job data.";
                // }
            }
        }
    } else {
        $response['msg'] = 'Invalid nonce.';
    }

    appendMessageToFile($response['msg']);

    wp_send_json($response);
    wp_die();
}


//job start to check STATUS
function job_check_status_at_api($review_api_key, $current_job_id)
{
    $state_flag = 0;
    $api_response = array(
        'success' => 0,
        'data'    => array('jobID' => 0),
        'msg'     => array(''),
        'state'     => 0,
    );

    $api_endpoint = '/job/status';
    $api_url = CUSTOM_HOST_URL . $api_endpoint;

    $headers = array(
        'Content-Type' => 'application/json', // Update content type to JSON
    );
    $query_params = array(
        'api_key' => $review_api_key,
        'jobId' => $current_job_id,
    );
    $api_url = add_query_arg($query_params, $api_url);
    // Make a GET request to the Express.js endpoint
    $response = wp_remote_get($api_url, array(
        'headers' => $headers,
        'timeout' => 20,
    ));
    if (is_wp_error($response)) {
        $api_response['data']['jobID'] = 0;
        $api_response['success'] = 0;
        $api_response['state'] = 0;
        $api_response['msg'] = $response->get_error_message();
    } else {
        $body = wp_remote_retrieve_body($response);
        // ptr($body);exit;
        $data = json_decode($body, true);
        if (isset($data['state']) && $data['state'] == 'completed') {
            $state_flag = 1;
            if ($data['error']) {
                $data['message'] = $data['state'];
                $state_flag = 0;
            }
            $data['message'] = 'Checked successful ! Now go ahead with GET';
            $api_response['data']['jobID'] = $current_job_id;
            $api_response['success'] = 1;
            $api_response['state'] = $state_flag;
            $api_response['msg'] = $data['message'];
        } else {
            $api_response['data']['jobID'] = 0;
            $api_response['success'] = 0;
            $api_response['state'] = $state_flag;
            $api_response['msg'] = isset($data['error']) ? $data['error'] : 'something went wrong !';
        }
    }
    return $api_response;
}

// Display all 5 start reviews by second arguments will be true, otherwise all reviews by term_id
// usage : get_all_reviews_by_term($term_id,$review_flag = false)
function get_all_reviews_by_term($term_id, $review_flag = false){
    $args = array(
      'post_type'      => 'agr_google_review',
      'posts_per_page' => -1,
      'tax_query'      => array(
          array(
              'taxonomy' => 'business',
              'field'    => 'id',
              'terms'    => $term_id,
          ),
      ),
      'order'          => 'ASC',
    );
    $reviews_query = new WP_Query($args);
    $total_posts = 0;
    $all_reviews = array();
    $job_id = '';
    $review_type = 'All Reviews';  
    if ($reviews_query->have_posts()) {
      while ($reviews_query->have_posts()) {
          $reviews_query->the_post();
          $review_id = get_the_ID();
          $rating = get_post_meta($review_id, 'rating', true);
          
          $job_id = get_post_meta($review_id, 'job_id', true);            
          $reviewer_name = get_post_meta($review_id, 'reviewer_name', true);
          $reviewer_picture_url = get_post_meta($review_id, 'reviewer_picture_url', true);
          $url = get_post_meta($review_id, 'url', true);
          $text = get_post_meta($review_id, 'text', true);
          $publish_date = get_post_meta($review_id, 'publish_date', true);
          $review_data = array(
              'reviewer_name' => $reviewer_name,
              'reviewer_picture_url' => $reviewer_picture_url,
              'url' => $url,
              'text' => $text,
              'publish_date' => $publish_date,
          );
  
          
          if ($review_flag && $review_flag == true) {
            if ($rating == 5) {
              $review_type = '5 Start Reviews only';
              $all_reviews[] = $review_data;
            }          
          }
          else{
            $all_reviews[] = $review_data;
          }
      }
      $total_posts = count($all_reviews);
      wp_reset_postdata();
    }
    
    return array(
      'reviews_type' => $review_type,
      'total_posts' => $total_posts,
      'job_id' => $job_id,
      'all_reviews' => $all_reviews,
    );
  }


add_shortcode('display', 'display_fun');
function display_fun()
{
    $term_id = 42;
    ptr(get_all_reviews_by_term($term_id));
    exit;
}


//remove unused assets backend
if ($pagenow == 'admin.php' && isset($_GET['page']) && ($_GET['page'] == 'awesome-google-review' || $_GET['page'] == 'delete-review' || $_GET['page'] == 'review-cron-job')) {
    add_action('admin_menu', 'my_footer_shh');
    add_filter('admin_footer_text', 'remove_footer_admin');
}
function remove_footer_admin()
{
    return '';
}
function my_footer_shh()
{
    remove_filter('update_footer', 'core_update_footer');
}


function get_all_executed_firm_names($step)
{
    global $wpdb;
    $conditions = array();
    if ($step == 1) {
        $conditions = array(
            'jobID_json' => 1,
            'jobID_check_status' => 1,
            'jobID_check' => 1,
            'jobID_final' => 1,
            'term_id' => array('!=', 0)
        );
    }
    if ($step == 2) {
        $conditions = array(
            'jobID_json' => 1,
            'jobID_check_status' => 0,
            'jobID_check' => 0,
            'jobID_final' => 0,
            'term_id' => array('!=', 0)
        );
    }
    if ($step == 3) {
        $conditions = array(
            'jobID_json' => 1,
            'jobID_check_status' => 1,
            'jobID_check' => 0,
            'jobID_final' => 0,
            'term_id' => array('!=', 0)
        );
    }

    if ($step == 4) {
        $conditions = array(
            'jobID_json' => 1,
            'jobID_check_status' => 1,
            'jobID_check' => 1,
            'jobID_final' => 0,
            'term_id' => array('!=', 0)
        );
    }

    $where_conditions = array();
    foreach ($conditions as $key => $value) {
        if (is_array($value)) {
            $where_conditions[] = "{$key} {$value[0]} '{$value[1]}'";
        } else {
            $where_conditions[] = "{$key} = '{$value}'";
        }
    }
    $where_clause = implode(' AND ', $where_conditions);
    $query = "SELECT term_id,firm_name,jobID FROM {$wpdb->prefix}jobdata WHERE {$where_clause}";
    $results = $wpdb->get_results($query, ARRAY_A);
    $firm_names = array();
    foreach ($results as $key => $result) {
        $firm_names[$key]['firm_name'] = $result['firm_name'];
        $firm_names[$key]['jobID'] = $result['jobID'];
        $firm_names[$key]['term_id'] = $result['term_id'];
    }

    return $firm_names;
}

// function get_all_executed_firm_names_by_check()
// {
//     global $wpdb;
//     $conditions = array(
//         'jobID_json' => 1,
//         'jobID_check' => 0,
//         'jobID_check_status' => 0,
//         'jobID_final' => 0,
//         'term_id' => array('!=', 0)
//     );
//     $where_conditions = array();
//     foreach ($conditions as $key => $value) {
//         if (is_array($value)) {
//             $where_conditions[] = "{$key} {$value[0]} '{$value[1]}'";
//         } else {
//             $where_conditions[] = "{$key} = '{$value}'";
//         }
//     }
//     $where_clause = implode(' AND ', $where_conditions);
//     $query = "SELECT term_id,firm_name,jobID FROM {$wpdb->prefix}jobdata WHERE {$where_clause}";
//     $results = $wpdb->get_results($query, ARRAY_A);
//     $firm_names = array();
//     foreach ($results as $key => $result) {
//         $firm_names[$key]['firm_name'] = $result['firm_name'];
//         $firm_names[$key]['jobID'] = $result['jobID'];
//         $firm_names[$key]['term_id'] = $result['term_id'];
//     }

//     return $firm_names;
// }


function first_cron()
{
    $next_first_event_timestamp = wp_next_scheduled('first_daily_data');
    $output = array('scheduled' => false, 'timestamp' => 0);
    if ($next_first_event_timestamp) {
        $date = gmdate('d-M-Y', $next_first_event_timestamp);
        $time = gmdate('h:i:s A', $next_first_event_timestamp);
        $output = array(
            'scheduled' => true,
            'date' => $date,
            'time' => $time,
            'timestamp' => $next_first_event_timestamp
        );
    }
    return json_encode($output);
}

function second_cron()
{
    $next_second_event_timestamp = wp_next_scheduled('second_daily_data');
    $output = array('scheduled' => false, 'timestamp' => 0);
    if ($next_second_event_timestamp) {
        $date = gmdate('d-M-Y', $next_second_event_timestamp);
        $time = gmdate('h:i:s A', $next_second_event_timestamp);
        $output = array(
            'scheduled' => true,
            'date' => $date,
            'time' => $time,
            'timestamp' => $next_second_event_timestamp
        );
    }
    return json_encode($output);
}

function display_countdown_timer()
{
    $cron_data = first_cron();
    $cron_data = json_decode($cron_data, true);

    ob_start();
?>
    <div id="countdown-timer" data-timestamp="<?php echo esc_attr($cron_data['timestamp']); ?>">
        <?php if ($cron_data['scheduled']) : ?>
            <div class="timer"><span id="time-remaining"></span></div>
        <?php else : ?>
            <p>first_daily_data is not scheduled.</p>
        <?php endif; ?>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var countdownElement = document.getElementById('countdown-timer');
            var timestamp = countdownElement.getAttribute('data-timestamp');
            var countdownDisplay = document.getElementById('time-remaining');

            if (timestamp) {
                function updateCountdown() {
                    var now = Math.floor(Date.now() / 1000);
                    var secondsRemaining = timestamp - now;

                    if (secondsRemaining > 0) {
                        var hours = Math.floor(secondsRemaining / 3600);
                        var minutes = Math.floor((secondsRemaining % 3600) / 60);
                        var seconds = secondsRemaining % 60;

                        countdownDisplay.innerText = hours + 'h ' + minutes + 'm ' + seconds + 's';
                    } else {
                        countdownDisplay.innerText = 'The event has started or ended.';
                    }
                }

                updateCountdown();
                setInterval(updateCountdown, 1000);
            }
        });
    </script>
<?php
    return ob_get_clean();
}


function display_second_countdown_timer()
{
    $cron_data = second_cron();
    $cron_data = json_decode($cron_data, true);

    ob_start();
?>
    <div id="second-countdown-timer" data-timestamp="<?php echo esc_attr($cron_data['timestamp']); ?>">
        <?php if ($cron_data['scheduled']) : ?>
            <p>Next event is scheduled for: <?php echo esc_html($cron_data['date'] . ' ' . $cron_data['time']); ?></p>
            <p>Time remaining: <span id="second-time-remaining"></span></p>
        <?php else : ?>
            <p>second_daily_data is not scheduled.</p>
        <?php endif; ?>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var countdownElement = document.getElementById('second-countdown-timer');
            var timestamp = countdownElement.getAttribute('data-timestamp');
            var countdownDisplay = document.getElementById('second-time-remaining');

            if (timestamp) {
                function updateCountdown() {
                    var now = Math.floor(Date.now() / 1000);
                    var secondsRemaining = timestamp - now;

                    if (secondsRemaining > 0) {
                        var hours = Math.floor(secondsRemaining / 3600);
                        var minutes = Math.floor((secondsRemaining % 3600) / 60);
                        var seconds = secondsRemaining % 60;

                        countdownDisplay.innerText = hours + 'h ' + minutes + 'm ' + seconds + 's';
                    } else {
                        countdownDisplay.innerText = 'The event has started or ended.';
                    }
                }

                updateCountdown();
                setInterval(updateCountdown, 1000);
            }
        });
    </script>
<?php
    return ob_get_clean();
}



// cron is checked action
add_action('wp_ajax_cron_is_checked_ajax_action', 'cron_is_checked_ajax_action_function');
add_action('wp_ajax_nopriv_cron_is_checked_ajax_action', 'cron_is_checked_ajax_action_function');

function cron_is_checked_ajax_action_function()
{
    $response = array(
        'success' => 0,
        'msg'     => array('')
    );
    $is_checked = isset($_POST['is_checked']) ? sanitize_text_field($_POST['is_checked']) : '';

    if (!empty($is_checked)) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'jobapi';
        $cron_status = ($is_checked === 'true') ? 1 : 0;

        $result = $wpdb->query($wpdb->prepare(
            "UPDATE $table_name SET cron_status = $cron_status WHERE review_api_key_status = 1 AND review_api_key != ''"
        ));
        if ($result !== false) {
            $response['success'] = 1;
            $response['msg'] = ($cron_status === 1) ? 'Updated to enabled cron!' : 'Updated to disabled cron!';
        } else {
            $response['msg'] = 'Failed to update cron status!';
        }
    }
    wp_send_json($response);
    wp_die();
}

add_action('wp_ajax_schedule_second_daily_data_ajax_action', 'schedule_second_daily_data_callback');
function schedule_second_daily_data_callback()
{
    wp_send_json_success("Second daily data scheduled successfully.");
}
