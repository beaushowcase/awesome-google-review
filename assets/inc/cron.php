<?php

function custom_cron_schedules($schedules) {
    $schedules['every_minute'] = array(
        'interval' => 10,
        'display'  => __('Every Minute')
    );
    return $schedules;
}
add_filter('cron_schedules', 'custom_cron_schedules');

if (!wp_next_scheduled('myjob')) {
    wp_schedule_event(time(), 'every_minute', 'myjob');
}

add_action('myjob', 'myjob_function');
function myjob_function(){
    // display_next_cron_run();
}


function display_next_cron_run() {
    $timestamp = wp_next_scheduled( 'myjob' );

    if ( $timestamp ) {
        $next_run_time = date( 'c', $timestamp ); // ISO 8601 format
        $countdown = '<div id="countdown"></div>';
        $script = "
            <script>
                function startCountdown(endTime) {
                    var countDownDate = new Date(endTime).getTime();

                    var x = setInterval(function() {
                        var now = new Date().getTime();
                        var distance = countDownDate - now;

                        var days = Math.floor(distance / (1000 * 60 * 60 * 24));
                        var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                        var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                        var seconds = Math.floor((distance % (1000 * 60)) / 1000);

                        document.getElementById('countdown').innerHTML =
                            days + 'd ' + hours + 'h ' + minutes + 'm ' + seconds + 's ';

                        if (distance < 0) {
                            clearInterval(x);
                            document.getElementById('countdown').innerHTML = 'EXPIRED';
                        }
                    }, 1000);
                }

                document.addEventListener('DOMContentLoaded', function() {
                    startCountdown('" . $next_run_time . "');
                });
            </script>
        ";
                return alert();
        // return '<p class="nextrun1">Next cron job run time: ' . $next_run_time . '</p>' . $countdown . $script;
    } else {
        return alert();
        // return '<p class="nextrun">No cron job scheduled.</p>';
    }
}

// add_shortcode( 'next_cron_run', 'display_next_cron_run' );






  
   
    
