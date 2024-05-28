<?php




// increment_my_custom_option();

// $timestamp = strtotime($cron_timer);
function custom_cron_schedules($schedules) {
    $schedules['every_minute'] = array(
        'interval' => 60,
        'display'  => __('Every Minute')
    );
    return $schedules;
}
add_filter('cron_schedules', 'custom_cron_schedules');

if (!wp_next_scheduled('myjobsecond')) {
    wp_schedule_event(time(), 'every_minute', 'myjobsecond');
}

add_action('myjobsecond', 'myjobsecond_function');
function myjobsecond_function(){
    $g_op = get_option('my_custom_option');
    $g_op = $g_op+1;
    $st = update_option('my_custom_option', $g_op);
    return $st;
}








  
   
    
