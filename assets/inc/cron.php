<?php


// Step 1: Add custom cron schedule
function custom_cron_schedules($schedules) {
    $schedules['every_minute'] = array(
        'interval' => 60,
        'display'  => __('Every Minute')
    );
    return $schedules;
}
add_filter('cron_schedules', 'custom_cron_schedules');

// Step 2: Schedule the custom cron event
if (!wp_next_scheduled('myjob')) {
    wp_schedule_event(time(), 'every_minute', 'myjob');
}

// Step 3: Create the custom cron job function
add_action('myjob', 'myjob_function');
function myjob_function(){
    

   
}


// function to upload data

function get_term_id_and_firm_name() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'jobdata';
    $query = $wpdb->prepare("
        SELECT term_id, firm_name 
        FROM $table_name 
        WHERE jobID_json = %d 
          AND jobID_check_status = %d 
          AND jobID_check = %d 
          AND jobID_final = %d
    ", 1, 1, 1, 1);
    $results = $wpdb->get_results($query);

    // $results = get_term_id_and_firm_name();
    foreach ($results as $result) {
        echo 'Term ID: ' . $result->term_id . ', Firm Name: ' . $result->firm_name . '<br>';
    }

    // return $results;
}

// get_term_id_and_firm_name();









  
   
    
