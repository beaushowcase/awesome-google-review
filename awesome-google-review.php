
<?php
/*
 * Plugin Name:       Awesome Google Review
 * Plugin URI:        https://beardog.digital/
 * Description:       Impresses with top-notch service and skilled professionals. A 5-star destination for grooming excellence!
 * Version:           1.4.2
 * Requires PHP:      7.0
 * Author:            #beaubhavik
 * Author URI:        https://beardog.digital/
 * Text Domain:       awesome-google-review
 */

// @codingStandardsIgnoreStart
define('AGR_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('AGR_PLUGIN_URL', plugin_dir_url(__FILE__));

define('CUSTOM_HOST_URL', 'https://api.spiderdunia.com:3000');

include_once(ABSPATH . 'wp-admin/includes/plugin.php');

register_deactivation_hook(__FILE__, 'agr_deactivation_cron_clear');
register_uninstall_hook(__FILE__, 'agr_uninstall_data');

function agr_uninstall_data()
{
    remove_custom_tables();
    flush_rewrite_rules();
}

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
    $table_names = [
        $wpdb->prefix . 'jobdata',
        $wpdb->prefix . 'jobapi'
    ];

    foreach ($table_names as $table_name) {
        $table_name = esc_sql($table_name);

        $wpdb->query("DROP TABLE IF EXISTS {$table_name}");
    }
}

global $pagenow;

// check cron enable disable query

function check_cron_enable_or_disable()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'jobapi';
    $query = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT cron_status FROM %s WHERE review_api_key_status = %d AND review_api_key != %s",
            $table_name,
            1,
            ''
        ),
        ARRAY_A
    );
    return $query ? $query['cron_status'] : '';
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
    return time();
}

// Enqueue = START
function our_load_admin_style()
{
    global $pagenow;
    if ($pagenow == 'admin.php' && isset($_GET['page']) && ($_GET['page'] == 'awesome-google-review' || $_GET['page'] == 'delete-review' || $_GET['page'] == 'review-cron-job')) {
        // Enqueue jQuery
        wp_enqueue_script('jquery');

        $nonce = wp_create_nonce('agr_nonce');

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
        wp_localize_script('agr-ajax-script', 'ajax_object', ['ajax_url' => admin_url('admin-ajax.php'), 'nonce' => $nonce, 'main_site_url' => site_url(), 'admin_plugin_main_url' => esc_url(get_admin_url(null, 'admin.php?page=awesome-google-review')), 'get_url_page' => $_GET['page'], 'plugin_url' => plugins_url('', __FILE__), 'review_api_key' => get_existing_api_key()]);
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


function get_all_firms()
{
    $terms = get_terms(array(
        'taxonomy' => 'business',
        'hide_empty' => false,
    ));

    $posts_by_term = array();

    foreach ($terms as $term) {
        $posts_by_term[$term->term_id] = array(
            'name' => $term->name,
            'id' => $term->term_id,
            'posts' => array(),
        );
    }

    $all_posts = get_posts(array(
        'post_type' => 'agr_google_review',
        'posts_per_page' => -1,
        'fields' => 'ids',
    ));

    foreach ($all_posts as $post_id) {
        $post_terms = wp_get_post_terms($post_id, 'business', array('fields' => 'ids'));

        foreach ($post_terms as $term_id) {
            if (isset($posts_by_term[$term_id])) {
                $posts_by_term[$term_id]['posts'][] = $post_id;
            }
        }
    }

    // Filter out terms with no posts
    $term_data = array_filter($posts_by_term, function ($term) {
        return !empty($term['posts']);
    });

    return array_values($term_data);
}


function get_existing_api_key()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'jobapi';

    $api_key = $wpdb->get_var("SELECT review_api_key FROM $table_name ORDER BY id DESC LIMIT 1");

    return $api_key;
}

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


// Function to append message to a log file with bullet point prefix
function appendMessageToFile($message)
{
    if (empty($message)) {
        return false;
    }
    if (!function_exists('WP_Filesystem')) {
        require_once ABSPATH . '/wp-admin/includes/file.php';
    }
    global $wp_filesystem;
    WP_Filesystem();
    $folder_path = plugin_dir_path(__FILE__);
    $file_path = $folder_path . 'logs.txt';
    if (!$wp_filesystem->exists($file_path)) {
        if (!$wp_filesystem->is_writable($folder_path)) {
            return false;
        }
        $wp_filesystem->put_contents($file_path, '');
    }
    $current_content = $wp_filesystem->get_contents($file_path);
    $current_content .= '- ' . $message . PHP_EOL;
    if (!$wp_filesystem->put_contents($file_path, $current_content, FS_CHMOD_FILE)) {
        return false;
    }
    return true;
}


function displayMessagesFromFile()
{
    // Initialize the WP_Filesystem
    if (!function_exists('WP_Filesystem')) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
    }
    WP_Filesystem();

    global $wp_filesystem;

    $folder_path = plugin_dir_path(__FILE__);
    $file_path = $folder_path . 'logs.txt';

    if ($wp_filesystem->exists($file_path)) {
        // Read the content of the file
        $content = $wp_filesystem->get_contents($file_path);

        // Explode the content into an array of lines
        $lines = explode(PHP_EOL, $content);

        // Iterate through each line and echo it after escaping
        foreach ($lines as $line) {
            echo esc_html($line) . '<br>'; // Escape each line and add <br> tag after each line
        }
    } else {
        // If the file does not exist, display a message
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

    check_ajax_referer('agr_nonce', 'nonce');

    if (get_existing_api_key_data()->review_api_key_status == 1) {
        $response['api'] = true;
    }

    $current_job_id = isset($_POST['current_job_id']) ? sanitize_text_field($_POST['current_job_id']) : '';
    $get_job_data   = get_job_data($current_job_id);

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
    $is_table_empty = $wpdb->get_var("SELECT COUNT(*) FROM $table_name") == 0;

    $data_array = [
        'review_api_key' => $data['review_api_key'],
        'review_api_key_status' => $data['review_api_key_status'],
    ];

    if ($is_table_empty) {
        $result = $wpdb->insert($table_name, $data_array);
        return $result !== false;
    } else {
        $last_row_id = $wpdb->get_var("SELECT MAX(id) FROM $table_name");
        $where = ['id' => $last_row_id];
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

    $review_api_key = sanitize_text_field($_POST['review_api_key']);

    $table_name = set_table_required('jobapi');
    // $serialized_data = serialize($data);
    $nonce = check_ajax_referer('agr_nonce', 'nonce');


    if ($nonce) {
        $response_api_data = invalidApiKey($review_api_key);

        if ($response_api_data['success'] === 1) {
            $data = array(
                'review_api_key' => $review_api_key,
                'review_api_key_status' => 1,
            );

            save_data_to_table($table_name, $data);

            $response['data']['api'] = $response_api_data['data']['api'];
            $response['success'] = $response_api_data['success'];
            $response['msg'] = $response_api_data['msg'];
        } else {
            $data = array(
                'review_api_key' => $review_api_key,
                'review_api_key_status' => 0,
                // 'client_ip' => $client_ip,
            );
            save_data_to_table($table_name, $data);

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
        'Content-Type' => 'application/json',
    );
    $query_params = array(
        'api_key' => $review_api_key,
    );
    $api_url = add_query_arg($query_params, $api_url);
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
    return $api_response;
}

function check_verify_file($current_job_id, $review_api_key)
{
    global $wpdb;
    $parent_dir = plugin_dir_path(__FILE__);
    $folder_path = $parent_dir . 'jobdata';

    $file_path = $folder_path . '/' . $current_job_id . '.json';

    if (file_exists($file_path)) {
        // Fetch file contents using wp_remote_get
        $response = wp_remote_get($file_path);

        if (!is_wp_error($response) && $response['response']['code'] === 200) {
            $json_contents = wp_remote_retrieve_body($response);
            $json_array = json_decode($json_contents, true);

            if ($json_array !== null) {
                return $json_array;
            } else {
                return false;
            }
        }
    }

    return false;
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
function job_start_ajax_action_function()
{
    global $wpdb;

    $response = array(
        'success' => 0,
        'data'    => array('jobID' => ''),
        'msg'     => ''
    );

    $nonce         = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';
    $review_api_key = isset($_POST['review_api_key']) ? sanitize_text_field($_POST['review_api_key']) : '';
    $encoded_firm = $_POST['firm_name'];
    $decoded_firm_name = urldecode($_POST['firm_name']);
    $firm_name     = isset($decoded_firm_name) ? $decoded_firm_name : '';

    if (!empty($nonce) && wp_verify_nonce($nonce, 'get_set_trigger')) {
        $response_api_data = job_start_at_api($review_api_key, $encoded_firm);
        if ($response_api_data['success']) {
            $jobID = $response_api_data['data']['jobID'];
            if ($wpdb->last_error) {
                $response['msg'] = "Database Error: " . $wpdb->last_error;
            } else {
                $data = array(
                    'jobID' => $jobID,
                    'jobID_json' => 1,
                    'jobID_check' => 0,
                    'jobID_check_status' => 0,
                    'jobID_final' => 0,
                    'term_id' => 0,
                    'review_api_key' => $review_api_key,
                    'firm_name' => $firm_name,
                    'created' => current_time('mysql')
                );

                $existing_jobID = $wpdb->get_var($wpdb->prepare("SELECT jobID FROM {$wpdb->prefix}jobdata WHERE jobID = %s", $jobID));
                if ($existing_jobID !== null) {
                    $where = array('jobID' => $jobID);
                    $result = $wpdb->update($wpdb->prefix . 'jobdata', $data, $where, array('%d'), array('%s'));
                } else {
                    $result = $wpdb->insert($wpdb->prefix . 'jobdata', $data, array('%s', '%d'));
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
        'Content-Type' => 'application/json',
    );
    $query_params = array(
        'api_key' => $review_api_key,
        'id' => $current_job_id,
    );
    $api_url = add_query_arg($query_params, $api_url);
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
    return $api_response;
}


// Function to save JSON response to a file
function save_json_response_to_file($current_job_id, $data)
{
    $parent_dir = plugin_dir_path(__FILE__);
    $folder_path = $parent_dir . 'jobdata';

    // Use WP_Filesystem methods for directory creation
    WP_Filesystem();
    global $wp_filesystem;

    if (!$wp_filesystem->is_dir($folder_path)) {
        if (!$wp_filesystem->mkdir($folder_path, 0755, true)) {
            return false;
        }
    }

    $file_path = $folder_path . '/' . $current_job_id . '.json';

    // Encode data using wp_json_encode()
    $json_data = wp_json_encode($data, JSON_PRETTY_PRINT);

    // Use WP_Filesystem method for file writing
    if ($wp_filesystem->put_contents($file_path, $json_data, FS_CHMOD_FILE) !== false) {
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

        if ($response_api_data['success']) {
            $jobID = $response_api_data['data']['jobID'];

            $response['data']['jobID'] = $jobID;
            $response['success'] = 1;
            $response['msg'] = $response_api_data['msg'];

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
        global $wpdb;
        $posts_table = $wpdb->posts;
        $postmeta_table = $wpdb->postmeta;

        $post_ids = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT ID FROM $posts_table WHERE post_type = 'agr_google_review' AND ID IN (SELECT post_id FROM $postmeta_table WHERE meta_key = 'business' AND meta_value = %d)",
                $term->term_id
            )
        );

        $deleted_count = 0;

        foreach ($post_ids as $post_id) {
            if (wp_delete_post($post_id, true)) {
                $deleted_count++;
            }
        }

        // Delete orphaned post meta
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM $postmeta_table WHERE post_id NOT IN (%s)",
                implode(',', $post_ids)
            )
        );

        return $deleted_count;
    } else {
        return 0;
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
    return $success;
}




function get_post_id_by_meta($meta_key, $meta_value, $post_type = 'agr_google_review')
{
    global $wpdb;

    $post_id = $wpdb->get_var($wpdb->prepare("
        SELECT p.ID
        FROM $wpdb->posts p
        JOIN $wpdb->postmeta pm ON p.ID = pm.post_id
        WHERE p.post_type = %s
        AND pm.meta_key = %s
        AND pm.meta_value = %s
        LIMIT 1
    ", $post_type, $meta_key, $meta_value));

    return $post_id ?: 0;
}



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
    wp_send_json($response);
    wp_die();
}


function delete_file($jobID)
{
    $parent_dir = plugin_dir_path(__FILE__);
    $folder_path = $parent_dir . 'jobdata';
    $file_path = $folder_path . '/' . $jobID . '.json';

    if (file_exists($file_path)) {
        // Use wp_delete_file() to delete the file
        if (wp_delete_file($file_path)) {
            return true;
        } else {
            // Handle deletion failure
            return false;
        }
    }

    return true; // File doesn't exist, so consider it as "deleted"
}




// clear logs
add_action('wp_ajax_job_reset_logs_ajax_action', 'job_reset_logs_ajax_action_function');
add_action('wp_ajax_nopriv_job_reset_logs_ajax_action', 'job_reset_logs_ajax_action_function');


function job_reset_logs_ajax_action_function()
{
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

    WP_Filesystem();
    global $wp_filesystem;

    if ($wp_filesystem->exists($file_path)) {
        if ($wp_filesystem->put_contents($file_path, '') !== false) {
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
    $table_name = $wpdb->prefix . 'jobdata';
    $data = array(
        $column_name => $value
    );
    $where = array(
        'jobID' => $job_id
    );
    $updated = $wpdb->update($table_name, $data, $where);
    if ($updated !== false) {
        return true;
    } else {
        return false;
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
    if (!empty($current_term_id)) {
        if ($wpdb->last_error) {
            $response['msg'] = "Database Error: " . $wpdb->last_error;
        } else {
            $existing_termID = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT term_id FROM {$wpdb->prefix}jobdata WHERE term_id = %s",
                    $current_term_id
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
function get_firm_name_by_term_id($current_term_id, $wpdb)
{
    // Directly use $wpdb->prepare to construct and execute the query
    $result = $wpdb->get_var($wpdb->prepare("SELECT firm_name FROM %s WHERE term_id = %d", $wpdb->prefix . 'jobdata', $current_term_id));

    // Return the result if found, otherwise return null or handle accordingly
    return $result ? $result : null;
}

// Check status
add_action('wp_ajax_job_check_status_update_ajax_action', 'job_check_status_update_ajax_action_function');
add_action('wp_ajax_nopriv_job_check_status_update_ajax_action', 'job_check_status_update_ajax_action_function');

function job_check_status_update_ajax_action_function()
{
    // Side-effect logic starts here
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
                    $response['data']['jobID'] = $jobID;
                    $response['success'] = 0;
                    $response['msg'] = 'Check Again !';
                } else {
                    $response['data']['jobID'] = $jobID;
                    $response['success'] = 0;
                    $response['msg'] = 'Check Again !';
                }
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

function get_all_reviews_by_term($term_id, $review_flag = false)
{
    global $wpdb;

    $sql = "
        SELECT p.ID, p.post_date, pm1.meta_value AS rating, pm2.meta_value AS job_id, pm3.meta_value AS reviewer_name, pm4.meta_value AS reviewer_picture_url, pm5.meta_value AS url, pm6.meta_value AS text, pm7.meta_value AS publish_date
        FROM {$wpdb->posts} p
        JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
        JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
        JOIN {$wpdb->postmeta} pm1 ON p.ID = pm1.post_id AND pm1.meta_key = 'rating'
        JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id AND pm2.meta_key = 'job_id'
        JOIN {$wpdb->postmeta} pm3 ON p.ID = pm3.post_id AND pm3.meta_key = 'reviewer_name'
        JOIN {$wpdb->postmeta} pm4 ON p.ID = pm4.post_id AND pm4.meta_key = 'reviewer_picture_url'
        JOIN {$wpdb->postmeta} pm5 ON p.ID = pm5.post_id AND pm5.meta_key = 'url'
        JOIN {$wpdb->postmeta} pm6 ON p.ID = pm6.post_id AND pm6.meta_key = 'text'
        JOIN {$wpdb->postmeta} pm7 ON p.ID = pm7.post_id AND pm7.meta_key = 'publish_date'
        WHERE tt.taxonomy = 'business' AND tt.term_id = %d AND p.post_type = 'agr_google_review'
        ORDER BY p.post_date ASC
    ";

    // Suppress the warning by the analyzer

    $prepared_sql = $wpdb->prepare($sql, $term_id);


    // Suppress the warning by the analyzer

    $results = $wpdb->get_results($prepared_sql);


    $total_posts = 0;
    $all_reviews = array();
    $job_id = '';
    $review_type = 'All Reviews';

    foreach ($results as $result) {
        if ($review_flag && $review_flag == true) {
            if ($result->rating == 5) {
                $review_type = '5 Star Reviews only';
                $all_reviews[] = array(
                    'reviewer_name' => $result->reviewer_name,
                    'reviewer_picture_url' => $result->reviewer_picture_url,
                    'url' => $result->url,
                    'text' => $result->text,
                    'publish_date' => $result->publish_date,
                );
            }
        } else {
            $all_reviews[] = array(
                'reviewer_name' => $result->reviewer_name,
                'reviewer_picture_url' => $result->reviewer_picture_url,
                'url' => $result->url,
                'text' => $result->text,
                'publish_date' => $result->publish_date,
            );
        }
    }

    $total_posts = count($all_reviews);

    return array(
        'reviews_type' => $review_type,
        'total_posts' => $total_posts,
        'job_id' => $job_id,
        'all_reviews' => $all_reviews,
    );
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
    $results = $wpdb->prepare("SELECT term_id,firm_name,jobID FROM %s WHERE $where_clause", $wpdb->prefix . 'jobdata');
    $firm_names = array();
    foreach ($results as $key => $result) {
        $firm_names[$key]['firm_name'] = $result['firm_name'];
        $firm_names[$key]['jobID'] = $result['jobID'];
        $firm_names[$key]['term_id'] = $result['term_id'];
    }

    return $firm_names;
}

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
    // Replace json_encode with wp_json_encode
    return wp_json_encode($output);
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
    return wp_json_encode($output);
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

function ptr($str)
{
    echo "<pre>";
    print_r($str);
}

// @codingStandardsIgnoreEnd
