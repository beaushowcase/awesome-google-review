<?php
/*
 * Plugin Name:       Awesome Google Review
 * Plugin URI:        https://beardog.digital/
 * Description:       Impresses with top-notch service and skilled professionals. A 5-star destination for grooming excellence!
 * Version:           1.1
 * Requires PHP:      7.2
 * Author:            #beaubhavik
 * Author URI:        https://beardog.digital/
 * Text Domain:       awesome-google-review
 */

define('AGR_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('AGR_PLUGIN_URL', plugin_dir_url(__FILE__));

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

function get_dynamic_version()
{
    return time(); // Using the current timestamp as the version number
}
// Enqueue = START
function our_load_admin_style()
{
    global $pagenow;

    if ($pagenow == 'admin.php' && isset($_GET['page']) && $_GET['page'] == 'awesome-google-review') {
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
        wp_localize_script('agr-ajax-script', 'ajax_object', ['ajax_url' => admin_url('admin-ajax.php'), 'plugin_url' => plugins_url('', __FILE__), 'review_api_key' => get_existing_api_key()]);

        // Enqueue Custom Script with Dependencies
        wp_register_script('agr_custom', plugins_url('/assets/js/custom.js', __FILE__), ['jquery'], $dynamic_version, true);
        wp_enqueue_script('agr_custom');
    }
}
add_action('admin_enqueue_scripts', 'our_load_admin_style');
// Enqueue = END


function get_existing_firm_data(){
    global $wpdb;
    $table_name = $wpdb->prefix . 'jobapi';
    $table_name2 = $wpdb->prefix . 'jobdata';
    $client_ip = $_SERVER['REMOTE_ADDR'];    
    
    $firm_data = $wpdb->get_row($wpdb->prepare("
        SELECT j.firm_name, j.jobID
        FROM $table_name2 AS j
        INNER JOIN $table_name AS s ON j.review_api_key = s.review_api_key
        WHERE j.client_ip = %s 
        AND s.review_api_key_status = %d
        ORDER BY j.jobID DESC
        LIMIT 1", 
        $client_ip, 1), ARRAY_A);

    return $firm_data;
}

// set at locatization
function get_existing_api_key(){
    global $wpdb;
    $table_name = $wpdb->prefix . 'jobapi';   
    $client_ip = $_SERVER['REMOTE_ADDR'];
    $api_key = $wpdb->get_var($wpdb->prepare("SELECT review_api_key FROM $table_name WHERE client_ip = %s", $client_ip));
    return $api_key;
}


// api check
function get_api_key_by_client_ip($client_ip){
    global $wpdb;
    $table_name = $wpdb->prefix . 'jobapi';
    $api_key = $wpdb->get_var($wpdb->prepare("SELECT review_api_key FROM $table_name WHERE client_ip = %s AND review_api_key_status = %d", $client_ip, 1));  
    return $api_key;
}

function get_existing_api_key_data(){
    $client_ip = $_SERVER['REMOTE_ADDR'];
    $api_key = get_api_key_by_client_ip($client_ip);   
    return $api_key;
}

//business check
function get_existing_business_data(){
    $client_ip = $_SERVER['REMOTE_ADDR'];
    $last_firm_name = get_business_by_client_ip($client_ip);   
    return $last_firm_name;
}
function get_business_by_client_ip($client_ip){
    global $wpdb;
    $table_name = $wpdb->prefix . 'jobapi';
    $table_name2 = $wpdb->prefix . 'jobdata';
    
    $last_firm_name = $wpdb->get_var($wpdb->prepare("
        SELECT j.firm_name
        FROM $table_name2 AS j
        INNER JOIN $table_name AS s ON j.review_api_key = s.review_api_key
        WHERE j.client_ip = %s 
        AND s.review_api_key_status = %d
        ORDER BY j.jobID DESC
        LIMIT 1", 
        $client_ip, 1)
    );

    return $last_firm_name;
}

// Function to append message to a log file with bullet point prefix
function appendMessageToFile($message) {
    if ($message){        
        $folder_path = plugin_dir_path(__FILE__);
        $file_path = $folder_path . 'logs.txt';
        $current = file_get_contents($file_path);
        $current .= $message . PHP_EOL;
        file_put_contents($file_path, $current); 
    }
    return true;
}

//display logs.txt
function displayMessagesFromFile() {
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



// Include admin panel files.
require_once AGR_PLUGIN_PATH . 'assets/inc/admin_panel.php';

add_action('wp_ajax_initial_check_api', 'initial_check_api_function');
add_action('wp_ajax_nopriv_initial_check_api', 'initial_check_api_function');

function initial_check_api_function()
{
    $response = array(
        'success_api' => 0,
        'msg_api' => "",
        'success_business' => 0,
        'msg_business' => "",
        'success_check' => 0,
        'msg_check' => "", 
    );
    if (get_existing_api_key_data()) {        
        $response['success_api'] = 1; 
        $response['msg_api'] = 'API Verified !';       
    }


    // if (get_existing_business_data()) {        
    //     $response['success_business'] = 1; 
    //     $response['msg_business'] = 'Business Verified !';       
    // }


    $client_ip = $_SERVER['REMOTE_ADDR'];    
    
    $check_job_status = check_job_status($client_ip);
    $check_upload_job_status = check_upload_job_status($client_ip);

    if ($check_job_status) {        
        $response['success_business'] = $check_job_status; 
        $response['msg_business'] = 'Business verified !';
    }
    else{
        $response['success_business'] = $check_job_status; 
        $response['msg_business'] = 'Business NOT verified !';
    }


    if ($check_upload_job_status) {        
        $response['success_check'] = $check_upload_job_status; 
        $response['msg_check'] = 'Ready to UPLOAD !';       
    }
    else{
        $response['success_check'] = $check_upload_job_status; 
        $response['msg_check'] = 'Need to CHECK !';
    }    
    wp_send_json($response);
    wp_die();
}


function set_table_required($tname){
    global $wpdb;
    $table_name = $wpdb->prefix . $tname;
    return $table_name;
}

function save_data_to_table($table_name, $data) {
    global $wpdb;
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        return false;
    }    

    // Check if client_ip and client_mac are present in the data array
    if (isset($data['client_ip'])) {        
        $data['client_ip'] = sanitize_text_field($data['client_ip']);
        
    } else {        
        $data['client_ip'] = null;      
    }

    $existing_api_key = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE client_ip = %s", $data['client_ip']), ARRAY_A);    
    if ($existing_api_key) {      
        $result = $wpdb->update(
            $table_name,
            $data,
            array('client_ip' => $data['client_ip'])
        );
        return $result !== false;
    } else {
        $result = $wpdb->insert($table_name, $data);
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

    $client_ip = $_SERVER['REMOTE_ADDR'];
  
    // $serialized_data = serialize($data);
    if (!empty($nonce) && wp_verify_nonce($nonce, 'review_api_key')) {
        $response_api_data = invalidApiKey($review_api_key);         
       
        if ($response_api_data['success'] === 1) {
            
            $data = array(
                'review_api_key' => $review_api_key,
                'review_api_key_status' => 1,
                'client_ip' => $client_ip,
            );

            $success = save_data_to_table($table_name, $data);

            $response['data']['api'] = $response_api_data['data']['api'];
            $response['success'] = $response_api_data['success'];
            $response['msg'] = $response_api_data['msg'];
        } else {

            $data = array(
                'review_api_key' => $review_api_key,
                'review_api_key_status' => 0,
                'client_ip' => $client_ip,
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
    $api_url = 'http://localhost:3000/validateApiKey'; // Assuming your Express.js server is running locally on port 3000
    // $api_url = 'https://api.spiderdunia.com:3001/api/free-google-reviews'; // Uncomment this line if the Express.js server is running on a different host/port
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


function get_reviews_data($firm_name, $review_api_key)
{
    if (empty($review_api_key)) {
        return;
    }
    $api_response['firm_name'] = '';
    $api_response['success'] = 0;
    $api_response['totalCount'] = 0;
    $api_response['message'] = '';
    $api_response['reviews'] = 0;

    // $api_url = 'http://localhost:3000/api/free-google-reviews';
    $api_url = 'https://api.spiderdunia.com:3001/api/free-google-reviews';
    $headers = array(
        'Content-Type' => 'application/x-www-form-urlencoded',
        'apikey' => $review_api_key,
    );
    $firm_name = array(
        'firm' => $firm_name,
    );
    $response = wp_remote_post($api_url, array(
        'headers' => $headers,
        'timeout' => 50,
        'body' => $firm_name,
    ));

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    // ptr($response);exit;
    if (is_wp_error($response)) {
        $api_response['message'] = $response->errors['http_request_failed'][0];
    } else {
        if ($data['success'] == 1) {            
            $api_response['id'] = $data['id'];
            $api_response['firm_name'] = $data['firm_name'];
            $api_response['success'] = $data['success'];
            $api_response['totalCount'] = $data['totalCount'];
            $api_response['message'] = $data['message'];
            $api_response['reviews'] = $data['reviews'];
        }
        else{
            $api_response['id'] = $data['id'];
            $api_response['firm_name'] = $data['firm_name'];
            $api_response['success'] = $data['success'];
            $api_response['totalCount'] = $data['totalCount'];
            $api_response['message'] = $data['message'];
            $api_response['reviews'] = $data['reviews'];
        }
    }

    appendMessageToFile($api_response['message']);
    return $api_response;
}

// job start
add_action('wp_ajax_job_start_ajax_action', 'job_start_ajax_action_function');
add_action('wp_ajax_nopriv_job_start_ajax_action', 'job_start_ajax_action_function');

function job_start_ajax_action_function() {
    global $wpdb;
    $response = array(
        'success' => 0,
        'data'    => array('jobID' => ''),
        'msg'     => ''
    );

    $nonce         = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';
    $review_api_key = isset($_POST['review_api_key']) ? sanitize_text_field($_POST['review_api_key']) : '';
    $firm_name     = isset($_POST['firm_name']) ? sanitize_text_field($_POST['firm_name']) : '';

    if (!empty($nonce) && wp_verify_nonce($nonce, 'get_set_trigger')) {
        $response_api_data = job_start_at_api($review_api_key, $firm_name);

        if ($response_api_data['success']) {
            $jobID = $response_api_data['data']['jobID'];
            
          
            $table_name = $wpdb->prefix . 'jobapi';
            $c_ip = $wpdb->get_var($wpdb->prepare("SELECT client_ip FROM $table_name WHERE review_api_key = %s AND review_api_key_status = %d", $review_api_key, 1));  


            if ($wpdb->last_error) {                
                $response['msg'] = "Database Error: " . $wpdb->last_error;
            } else {                
                $existing_jobID = $wpdb->get_var(
                    $wpdb->prepare(
                        "SELECT jobID FROM {$wpdb->prefix}jobdata WHERE jobID = %s",
                        $jobID
                    )
                );

                $data = array('jobID' => $jobID, 'jobID_json' => 1, 'jobID_check' => 0 , 'review_api_key' => $review_api_key, 'firm_name' => $firm_name, 'client_ip' => $c_ip, 'created' => current_time('mysql'));
               

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

    appendMessageToFile($response['msg']);

    wp_send_json($response);
    wp_die();
}

function job_start_at_api($review_api_key,$firm_name)
{   
    $api_response = array(
        'success' => 0,
        'data'    => array('jobID' => 0),
        'msg'     => array('')
    );
    $api_url = 'http://localhost:3000/scrape';    
    $headers = array(
        'Content-Type' => 'application/json', // Update content type to JSON
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
        $api_response['data']['jobID'] = 0;
        $api_response['success'] = 0;
        $api_response['msg'] = $response->get_error_message();
    } else {
        $body = wp_remote_retrieve_body($response);       
        $data = json_decode($body, true);        
        if (isset($data['success']) && $data['success']) {
            $api_response['data']['jobID'] = $data['jobID'];
            $api_response['success'] = 1;
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

//job start to check job
function job_check_at_api($review_api_key,$current_job_id)
{   
    $api_response = array(
        'success' => 0,
        'data'    => array('jobID' => 0),
        'msg'     => array('')
    );
    $api_url = 'http://localhost:3000/events';      
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
        $file_save = save_json_response_to_file($current_job_id,$data);
        if (isset($file_save) && $file_save) {     
            $status_flag = 1;
            $status_label = 'error';
            if($data['error']){
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
function save_json_response_to_file($current_job_id,$data) {    
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

function job_check_ajax_action_function() {
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
            $c_ip = $wpdb->get_var($wpdb->prepare("SELECT client_ip FROM $table_name WHERE review_api_key = %s AND review_api_key_status = %d", $review_api_key, 1));

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
                    'created' => current_time('mysql')
                );
                
                if ($existing_jobID !== null) {
                    $where = array('jobID' => $jobID , 'jobID_json' => 1, 'client_ip' => $c_ip);
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
            $c_ip = $wpdb->get_var($wpdb->prepare("SELECT client_ip FROM $table_name WHERE review_api_key = %s AND review_api_key_status = %d", $review_api_key, 1));

           

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
                    'jobID_json' => 0,
                    'created' => current_time('mysql')
                );
                
                if ($existing_jobID !== null) {
                    $where = array('jobID' => $jobID , 'client_ip' => $c_ip);
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
    $response['firm_name'] = '';
    $response['success'] = 0;
    $response['totalCount'] = 0;
    $response['message'] = '';
    $response['reviews'] = 0;
    $nonce = sanitize_text_field($_POST['nonce']);
    $firm_name = sanitize_text_field($_POST['firm_name']);
    $review_api_key = sanitize_text_field($_POST['review_api_key']);

    if (!empty($nonce) && wp_verify_nonce($nonce, 'get_set_trigger')) {        
        $reviews_array = get_reviews_data($firm_name, $review_api_key);
        // ptr($reviews_array);exit;
        if ($reviews_array['success'] == 0) {
            $response['message'] = $reviews_array['message'];            
        } else {
            $response['id'] = $reviews_array['id'];
            $response['firm_name'] = $reviews_array['firm_name'];
            $response['success'] = $reviews_array['success'];
            $response['totalCount'] = $reviews_array['totalCount'];
            $response['message'] = $reviews_array['message'];
            $response['reviews'] = $reviews_array['reviews'];
            
            //Delete Old reviews
            delete_reviews_data();

            // Insert all reviews
            store_data_into_reviews($reviews_array);

            add_option('firm_name', $firm_name);
            if (get_option('firm_name') !== false) {
                update_option('firm_name', $firm_name);
            } 
            add_option('business_valid', 1);          
            if (get_option('firm_name') !== false) {
                update_option('business_valid', 1);
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
function delete_reviews_data() {
    // Get all posts of the "agr_google_review" post type
    $reviews = get_posts(array(
        'post_type' => 'agr_google_review',
        'posts_per_page' => -1, // Get all posts
        'fields' => 'ids', // Fetch only post IDs to improve performance
    ));
    // Loop through each review post and delete it
    foreach ($reviews as $review_id) {
        // Delete the post
        wp_delete_post($review_id, true); // Set second parameter to true to force delete (bypassing trash)

        // Delete associated post meta
        delete_post_meta($review_id, 'post_review_id');
        delete_post_meta($review_id, 'reviewer_name');
        delete_post_meta($review_id, 'reviewer_picture_url');
        delete_post_meta($review_id, 'url');
        delete_post_meta($review_id, 'rating');
        delete_post_meta($review_id, 'text');
        delete_post_meta($review_id, 'publish_date'); 

    }

    // Optionally, clean up any orphaned post meta
    global $wpdb;
    $wpdb->query($wpdb->prepare("DELETE FROM $wpdb->postmeta WHERE post_id NOT IN (SELECT ID FROM $wpdb->posts WHERE post_type = 'agr_google_review')"));

    return true;
}



function store_data_into_reviews($reviews_array)
{
   
    foreach ($reviews_array['reviews'] as $get_review) {
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
            update_post_meta($new_post_id, 'post_review_id', $id);
            update_post_meta($new_post_id, 'reviewer_name', $reviewer_name);
            update_post_meta($new_post_id, 'reviewer_picture_url', $reviewer_picture_url);
            update_post_meta($new_post_id, 'url', $reviewer_read_more);
            update_post_meta($new_post_id, 'rating', $rating);
            update_post_meta($new_post_id, 'text', $text);
            update_post_meta($new_post_id, 'publish_date', $published_at);
        }
    }

    return true;
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

function job_reset_ajax_action_function() {
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
            $jobID = $current_job_id ;
          
            $table_name = $wpdb->prefix . 'jobapi';
            $c_ip = $wpdb->get_var($wpdb->prepare("SELECT client_ip FROM $table_name WHERE review_api_key = %s AND review_api_key_status = %d", $review_api_key, 1));

            if ($wpdb->last_error) {                
                $response['msg'] = "Database Error: " . $wpdb->last_error;
            } else {          
                $existing_jobID = $wpdb->get_var(
                    $wpdb->prepare(
                        "SELECT jobID FROM {$wpdb->prefix}jobdata WHERE jobID = %s AND client_ip = %s",
                        $jobID, $c_ip
                    )
                );
            
                if ($existing_jobID !== null) {
                    // Update only if jobID and client_ip match
                    $where = array('jobID' => $jobID, 'client_ip' => $c_ip);
                    $data = array('jobID_json' => 0, 'jobID_check' => 0);
                    $result = $wpdb->update($wpdb->prefix . 'jobdata', $data, $where, array('%d', '%d'), array('%s', '%s'));
            
                    if ($result !== false) {
                        $response['data']['jobID'] = $jobID;
                        $response['success'] = 1;
                        $response['msg'] = 'Reset data successfully....';
                    } else {
                        $response['msg'] = "Database Error: Failed to update job data.";
                    }
                } else {
                    $response['msg'] = "Database Error: No existing jobID found for the provided client_ip.";
                }
            }
        
    } else {
        $response['msg'] = 'Something went wrong !';
    }

    appendMessageToFile($response['msg']);
    clearLogFile();


    wp_send_json($response);
    wp_die();
}



// clear logs
add_action('wp_ajax_job_reset_logs_ajax_action', 'job_reset_logs_ajax_action_function');
add_action('wp_ajax_nopriv_job_reset_logs_ajax_action', 'job_reset_logs_ajax_action_function');

function job_reset_logs_ajax_action_function() {
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
function clearLogFile() {
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

function job_upload_ajax_action_function() {
   
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
