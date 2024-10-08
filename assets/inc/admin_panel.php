<?php
// Add a setting page
function our_google_reviews_add_menu_page()
{
    add_menu_page(
        'Our Google Reviews',
        'Review Settings',
        'manage_options',
        'awesome-google-review',
        'our_google_reviews_callback',
        'dashicons-google'
    );

    // Add submenu item
    add_submenu_page(
        'awesome-google-review', // Parent slug
        'Delete Reviews', // Page title
        'Delete Reviews', // Menu title
        'manage_options', // Capability
        'delete-review', // Menu slug
        'delete_review_callback' // Callback function
    );

    // Add submenu item
    // add_submenu_page(
    //     'awesome-google-review', // Parent slug
    //     'Cron Setting', // Page title
    //     'Cron Setting', // Menu title
    //     'manage_options', // Capability
    //     'review-cron-job', // Menu slug
    //     'cron_job_callback' // Callback function
    // );
}
add_action('admin_menu', 'our_google_reviews_add_menu_page');


function delete_all_agr_google_reviews2()
{
    global $wpdb;

    // Define the post type to be deleted
    $post_type = 'agr_google_review';

    // Get all posts of the specified post type
    $posts = get_posts(
        array(
            'post_type' => $post_type,
            'post_status' => 'any',
            'numberposts' => -1,
            'fields' => 'ids',
        )
    );

    // Loop through each post and delete it
    foreach ($posts as $post_id) {
        wp_delete_post($post_id, true); // true parameter ensures the post is permanently deleted
    }
}

// add_action('admin_init', 'delete_all_agr_google_reviews2');

function add_settings_link($links, $file)
{
    if (strpos($file, 'awesome-google-review-main/awesome-google-review.php') !== false || strpos($file, 'awesome-google-review/awesome-google-review.php') !== false) {
        $settings_link = '<a href="' . esc_url(get_admin_url(null, 'admin.php?page=awesome-google-review')) . '">' . __('Review Settings') . '</a>';
        array_unshift($links, $settings_link);
    }
    return $links;
}
add_filter('plugin_action_links_awesome-google-review-main/awesome-google-review.php', 'add_settings_link', 10, 2);
add_filter('plugin_action_links_awesome-google-review/awesome-google-review.php', 'add_settings_link', 10, 2);

// Create post type on activation
register_activation_hook(__FILE__, 'awesome_google_review_plugin_activate');

function awesome_google_review_plugin_activate()
{
    add_agr_google_review_post_type();
    flush_rewrite_rules();
}



// Function to create table
function job_table()
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'jobdata';
    $table_name1 = $wpdb->prefix . 'jobapi';

    // Check if the table exists already
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            review_api_key varchar(255) NOT NULL,
            jobID bigint(20) NOT NULL,
            jobID_json bigint(20) NOT NULL,
            jobID_check_status bigint(20) NOT NULL,
            jobID_check bigint(20) NOT NULL,            
            jobID_final bigint(20) NOT NULL,
            term_id bigint(20) NOT NULL,            
            firm_name varchar(255) NOT NULL,
            created datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id)
            ) $charset_collate;";

        // Include upgrade.php for dbDelta
        require_once (ABSPATH . 'wp-admin/includes/upgrade.php');

        // Create the table
        maybe_create_table($table_name, $sql);
    }


    // Check if the table exists already
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name1'") != $table_name1) {

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name1 (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            review_api_key varchar(255) NOT NULL,
            review_api_key_status varchar(255) NOT NULL,
            cron_status bigint(20) NOT NULL,                        
            recurrence varchar(255) NOT NULL,
            timeslot time DEFAULT NULL,
            timeslot_second time DEFAULT NULL,
            created datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id)           
        ) $charset_collate;";

        // Include upgrade.php for dbDelta
        require_once (ABSPATH . 'wp-admin/includes/upgrade.php');

        maybe_create_table($table_name1, $sql);
    }
}

function expose_all_meta_fields_in_rest($response, $post, $request)
{
    if ($post->post_type === 'agr_google_review') {
        $meta = get_post_meta($post->ID);
        // Sanitize meta data
        $sanitized_meta = array_map(function ($meta_value) {
            return is_array($meta_value) ? array_map('sanitize_text_field', $meta_value) : sanitize_text_field($meta_value);
        }, $meta);
        $response->data['meta'] = $sanitized_meta;
    }
    return $response;
}
add_filter('rest_prepare_agr_google_review', 'expose_all_meta_fields_in_rest', 10, 3);


add_action('init', 'add_agr_google_review_post_type');
function add_agr_google_review_post_type()
{
    $labels = array(
        'name' => _x('Google Reviews', 'post type general name', 'awesome-google-review'),
        'singular_name' => _x('Google Review', 'post type singular name', 'awesome-google-review'),
        'menu_name' => _x('Google Reviews', 'admin menu', 'awesome-google-review'),
        'name_admin_bar' => _x('Google Review', 'add new on admin bar', 'awesome-google-review'),
        'add_new' => _x('Add New', 'Google Review', 'awesome-google-review'),
        'add_new_item' => __('Add New Google Review', 'awesome-google-review'),
        'new_item' => __('New Google Review', 'awesome-google-review'),
        'edit_item' => __('Edit Google Review', 'awesome-google-review'),
        'view_item' => __('View Google Review', 'awesome-google-review'),
        'all_items' => __('All Google Reviews', 'awesome-google-review'),
        'search_items' => __('Search Google Reviews', 'awesome-google-review'),
        'parent_item_colon' => __('Parent Google Reviews:', 'awesome-google-review'),
        'not_found' => __('No Google Reviews found.', 'awesome-google-review'),
        'not_found_in_trash' => __('No Google Reviews found in Trash.', 'awesome-google-review'),
    );

    $args = array(
        'labels' => $labels,
        'public' => false,
        'exclude_from_search' => true,
        'show_in_admin_bar' => false,
        'show_in_nav_menus' => false,
        'publicly_queryable' => false,
        'query_var' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'rewrite' => array('slug' => 'google-reviews'),
        'capability_type' => 'post',
        'has_archive' => true,
        'menu_icon' => 'dashicons-google',
        'hierarchical' => false,
        'menu_position' => null,
        'show_in_rest' => true,
        'supports' => array(''),
    );

    register_post_type('agr_google_review', $args);

    $taxonomy_args = array(
        'labels' => array(
            'name' => __('Business'),
            'singular_name' => __('Business'),
        ),
        'public' => false,
        'publicly_queryable' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'show_admin_column' => true,
        'hierarchical' => true,
        'rewrite' => array('slug' => 'business'),
    );
    
    register_taxonomy('business', 'agr_google_review', $taxonomy_args);

    add_action('add_meta_boxes', 'add_agr_google_review_meta_box');

    job_table();
}

function add_agr_google_review_meta_box()
{
    add_meta_box(
        'agr_google_review_meta_box',
        __('Google Review Details', 'awesome-google-review'),
        'render_agr_google_review_meta_box',
        'agr_google_review',
        'normal',
        'default'
    );
}

// check cron enable disable query
// function check_cron_enable_or_disable() {
//     global $wpdb;
//     $table_name = $wpdb->prefix . 'jobdata'; 
//     $query = $wpdb->prepare("
//         SELECT COUNT(*) 
//         FROM $table_name 
//         WHERE jobID_json = %d
//         AND jobID_check_status = %d
//         AND jobID_check = %d         
//         AND jobID_final = %d 
//         AND term_id != %d
//         AND cron_status = %d
//     ", 1, 1, 1, 1, 0, 1);
//     $matching_count = $wpdb->get_var($query);
//     $total_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
//     return $matching_count == $total_count;
// }



// Allow radio button instead of checkboxes for hierarchical taxonomies
function term_radio_checklist($args)
{
    if (!empty($args['taxonomy']) && $args['taxonomy'] === 'business' /* <== Change to your required taxonomy */) {
        if (empty($args['walker']) || is_a($args['walker'], 'Walker')) { // Don't override 3rd party walkers.
            if (!class_exists('term_radio_checklist')) {
                class term_radio_checklist extends Walker_Category_Checklist
                {
                    function walk($elements, $max_depth, ...$args)
                    {
                        $output = parent::walk($elements, $max_depth, ...$args);
                        $output = str_replace(
                            array('type="checkbox"', "type='checkbox'"),
                            array('type="radio"', "type='radio'"),
                            $output
                        );

                        return $output;
                    }
                }
            }

            $args['walker'] = new term_radio_checklist;
        }
    }

    return $args;
}
add_filter('wp_terms_checklist_args', 'term_radio_checklist');

function render_agr_google_review_meta_box($post)
{
    // Retrieve the current values of meta fields
    $job_id = get_post_meta($post->ID, 'job_id', true);
    $id = get_post_meta($post->ID, 'post_review_id', true);
    $reviewer_name = get_post_meta($post->ID, 'reviewer_name', true);
    $reviewer_picture_url = get_post_meta($post->ID, 'reviewer_picture_url', true);
    $url = get_post_meta($post->ID, 'url', true);
    $rating = get_post_meta($post->ID, 'rating', true);
    $text = get_post_meta($post->ID, 'text', true);
    $text2 = get_post_meta($post->ID, 'text2', true);
    $publish_date = get_post_meta($post->ID, 'publish_date', true);

    // Output the second table for Place ID on the right side
    echo '<table class="form-table" style="border-bottom:1px solid #c3c4c7">';
    echo '<tr><th>' . esc_html__('Job ID:', 'awesome-google-review') . '</th><td><input style="background:#ccc;" readonly type="text" id="job_id" name="job_id" value="' . esc_attr($job_id) . '" /></td></tr>';
    echo '<tr><th>' . esc_html__('Unique ID:', 'awesome-google-review') . '</th><td><input style="background:#ccc;" readonly type="text" id="post_review_id" name="post_review_id" value="' . esc_attr($id) . '" /></td></tr>';
    echo '</table>';

    // Output a table
    echo '<table class="form-table" style="width:auto">';
    echo '<tr><th>' . esc_html__('Reviewer Name:', 'awesome-google-review') . '</th><td><input readonly type="text" id="reviewer_name" name="reviewer_name" value="' . esc_attr($reviewer_name) . '" /></td></tr>';
    echo '<tr><th>' . esc_html__('Reviewer Picture URL:', 'awesome-google-review') . '</th><td><input readonly type="text" id="reviewer_picture_url" name="reviewer_picture_url" value="' . esc_url($reviewer_picture_url) . '" /></td></tr>';
    echo '<tr><th>' . esc_html__('Read More URL:', 'awesome-google-review') . '</th><td><input readonly type="text" id="url" name="url" value="' . esc_url($url) . '" /></td></tr>';
    echo '<tr><th>' . esc_html__('Rating:', 'awesome-google-review') . '</th><td><input readonly type="number" id="rating" name="rating" value="' . esc_attr($rating) . '" /></td></tr>';
    echo '<tr><th>' . esc_html__('Description:', 'awesome-google-review') . '</th><td><textarea readonly id="text" name="text" rows="4" cols="23">' . esc_textarea($text) . '</textarea></td><td><textarea id="text2" name="text2" rows="4" cols="23">' . esc_textarea($text2) . '</textarea></td></tr>';    
    echo '<tr><th>' . esc_html__('Publish Date:', 'awesome-google-review') . '</th><td><input readonly type="text" id="publish_date" name="publish_date" value="' . esc_attr($publish_date) . '" /></td></tr>';
    echo '</table>';
}



function cron_job_callback()
{ ?>
    <div class="toggle-sec">
        <label class="setting">
            <span class="setting__label">Cron Setting : </span>
            <span class="switch">
                <input id="cron_switch" class="switch__input" type="checkbox" role="switch" name="switch3" <?php echo (check_cron_enable_or_disable() == 1) ? 'checked' : ''; ?>>
                <span class="switch__fill" aria-hidden="true">
                    <span class="switch__text">ON</span>
                    <span class="switch__text">OFF</span>
                </span>
            </span>
        </label>
    </div>


    <?php
    if (check_cron_enable_or_disable() == 1) {
        $first_function_next_run = wp_next_scheduled('first_daily_data');
        $second_function_next_run = wp_next_scheduled('second_daily_data');
        $cron_record_recurrence = wp_get_schedule('first_daily_data');
        ?>
        <section id="processbar" style="display:none;"><span class="loader-71"> </span></section>
        <?php if ($first_function_next_run) {
            ?>
            <div class="toggle-sec" id="show_cron">
                <label class="setting">
                    <span class="setting__label"></span>
                    <table class="widefat">
                        <thead>
                            <tr>
                                <th>CRON</th>
                                <th>NEXT RUN</th>
                                <th>RECURRENCE</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Scrapping Cron</td>
                                <td class="first_cron">
                                    <?php echo $first_function_next_run ? esc_html(date('Y-m-d h:i:s A', $first_function_next_run)) : esc_html__('Not scheduled', 'text-domain'); ?>
                                </td>
                                <td><?php echo esc_html(ucfirst($cron_record_recurrence)); ?></td>
                            </tr>
                            <tr>
                                <td>Uploading Cron</td>
                                <td class="second_cron">
                                    <?php echo $second_function_next_run ? esc_html(date('Y-m-d h:i:s A', $second_function_next_run)) : esc_html__('Not scheduled', 'text-domain'); ?>
                                </td>
                                <td><?php echo esc_html(ucfirst($cron_record_recurrence)); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </label>
            </div>
        <?php } ?>


    <?php } ?>




    <?php
}

function delete_review_callback()
{ ?>
    <div class="container-process">
        <section id="processbar" style="display:none;"><span class="loader-71"> </span></section>
        <div class="partition delete-part">
            <div class="right-box">
                <div class="inner-content-data">
                    <h2 class="boxtitle display_total">Delete Review</h2>
                    <form id="review_delete_form" method="post" autocomplete="off">
                        <div class="delete_reviews">
                            <select name="sources" id="sources" class="custom-select sources" placeholder="Select Business">
                                <option class="select_business" value="0">Select business</option>
                                <?php
                                $all_firms = get_all_firms();
                                foreach ($all_firms as $firm) {
                                    ?>
                                    <option class="select_business" value="<?php echo esc_attr($firm['id']); ?>">
                                        <?php echo esc_html($firm['name']); ?>
                                    </option>
                                <?php } ?>
                            </select>
                            <div class="del-btn">
                                <button type="submit" class="custom-btn btn-16">
                                    <span class="trash">
                                        <span></span>
                                        <i></i>
                                    </span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php
}

// Function to check if ID exists
function isIdExists($array, $idToCheck)
{
    foreach ($array as $item) {
        if ($item['id'] == $idToCheck) {
            return true;
        }
    }
    return false;
}


function our_google_reviews_callback()
{
    $get_existing_api_key = get_existing_api_key();
    $get_api_status = get_api_key_status($get_existing_api_key);

    // ptr($get_api_status);exit;
    ?>
    <?php

    $firm_data = get_existing_firm_data();
    $firm_name_data = isset($firm_data['firm_name']) ? $firm_data['firm_name'] : '';
    $job_id_data = isset($firm_data['jobID']) ? $firm_data['jobID'] : '';
    $j_term_id = isset($firm_data['term_id']) ? $firm_data['term_id'] : '';

    $client_ip = $_SERVER['REMOTE_ADDR'];
    $jp = check_prepared_job_status($get_existing_api_key);
    $getjdata = get_job_data_by_api_key($get_existing_api_key);

    $jflag = 0;
    if ((!empty($getjdata['jobID_json']) && $getjdata['jobID_json'] == 1) && ($getjdata['jobID_check_status'] == 0 || $getjdata['jobID_check'] == 0 || $getjdata['jobID_final'] == 0) && @$getjdata['term_id'] == 0) {
        $jflag = 1;
    }

    ?>
    <!-- fieldset -->
    <!-- <input list="great" placeholder="Enter Business">
<datalist id="great">            
    <option>San Marino</option>
    <option>Holy See</option>
</datalist> -->
    <!-- animation 1 = START -->
    <style>
        .container-process {
            max-width: 1520px;
            width: 100%;
            padding-right: 15px;
            padding-left: 15px;
            margin-right: auto;
            margin-left: auto;
        }

        .step-indicator {
            display: flex;
            align-items: center;
            background: transparent;
            color: white;
            border-radius: 8px;
            padding: 40px 20px;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
            box-shadow: inset 0px 0px 20px 0px rgba(255, 255, 255, .5), 7px 7px 20px 0px rgba(0, 0, 0, .1), 4px 4px 5px 0px rgba(0, 0, 0, .1);
            position: relative;
            margin-top: 50px;
            padding-top: 25px;
            /* max-width: 1500px; */
            /* margin: 50px auto 0; */
        }

        .step {
            display: flex;
            align-items: center;
            flex-direction: column;
            position: relative;
            z-index: 1;
        }

        .step-indicator .step-icon {
            height: 50px;
            width: 50px;
            border-radius: 50%;
            background: transparent;
            font-size: 10px;
            text-align: center;
            color: #ffffff;
            position: relative;
            line-height: 50px;
            font-size: 20px;
            box-shadow: inset 2px 2px 2px 0px rgba(255, 255, 255, .5), 7px 7px 20px 0px rgba(0, 0, 0, .1), 4px 4px 5px 0px rgba(0, 0, 0, .1);
        }

        .step.active .step-icon {
            background: green;
        }

        .step p {
            text-align: center;
            position: absolute;
            bottom: -40px;
            color: #c2c2c2;
            font-size: 14px;
            font-weight: bold;
        }

        .step.active p {
            color: green;
        }

        .step.step2 p,
        .step.step3 p {
            left: 50%;
            transform: translateX(-50%);
        }

        .indicator-line {
            width: 100%;
            height: 2px;
            background: #c2c2c2;
            flex: 1;
        }

        .indicator-line.active {
            background: green;
        }

        .partition {
            display: flex;
            justify-content: space-between;
            gap: 30px;
            width: 100%;
            margin-top: 30px;
        }

        .left-box {
            flex-grow: 1;
        }

        .left-box .seo-plugin-data-info.container+.seo-plugin-data-info.container {
            margin-top: 30px;
        }

        @media screen and (max-width:1799px) {
            .container-process {
                max-width: 1360px;
            }
        }

        @media screen and (max-width:1599px) {
            .container-process {
                max-width: 1260px;
            }

            .seo-plugin-data-info.container {
                width: 400px;
            }
        }

        @media screen and (max-width:1499px) {
            .container-process {
                max-width: 1060px;
            }
        }

        @media screen and (max-width:1299px) {
            .container-process {
                max-width: 960px;
            }

            .seo-plugin-data-info.container {
                width: 350px;
            }
        }

        @media screen and (max-width:1199px) {
            .container-process {
                max-width: 90%;
            }

            .auto-fold #wpcontent {
                padding-left: 0;
            }
        }

        @media screen and (max-width:991px) {
            .partition {
                flex-direction: column;
            }

            .seo-plugin-data-info.container,
            .right-box {
                width: 100%;
                margin: 0 auto;
                max-width: 500px;
            }
        }

        @media screen and (max-width:767px) {

            .seo-plugin-data-info.container,
            .right-box {
                width: auto;
            }

            .step-indicator .step-icon {
                height: 40px;
                width: 40px;
                line-height: 40px;
                font-size: 16px;
            }

            .step p {
                bottom: -35px;
                font-size: 12px;
            }
        }

        @media screen and (max-width: 500px) {
            .step p {
                font-size: 11px;
                bottom: -20px;
            }
        }
    </style>

    <?php
    $start_active = false;
    $get_active = false;
    $set_active = false;
    $upload_active = false;
    if (!empty($getjdata['jobID_json']) && $getjdata['jobID_json'] == 1) {
        $start_active = true;

        if ($getjdata['jobID_check_status'] == 1) {
            $get_active = true;

            if ($getjdata['jobID_check'] == 1) {
                $set_active = true;

                if ($getjdata['jobID_final'] == 1) {
                    $upload_active = true;
                }
            }
        }
    }
    ?>
    <?php //echo($upload_active ? 'active' : '') ; 
        ?>

    <div class="container-process">
        <div class="step-indicator">
            <div class="step step1 <?php echo ($start_active ? 'active' : ''); ?>">
                <div class="step-icon">1</div>
                <p>START</p>
            </div>
            <div class="indicator-line <?php echo ($start_active ? 'active' : ''); ?>"></div>
            <div class="step step2 <?php echo ($get_active ? 'active' : ''); ?>">
                <div class="step-icon">2</div>
                <p>CHECK</p>
            </div>
            <div class="indicator-line <?php echo ($get_active ? 'active' : ''); ?>"></div>
            <div class="step step3 <?php echo ($set_active ? 'active' : ''); ?>">
                <div class="step-icon">3</div>
                <p>GET</p>
            </div>
            <div class="indicator-line <?php echo ($set_active ? 'active' : ''); ?>"></div>
            <div class="step step4 <?php echo ($upload_active ? 'active' : ''); ?>">
                <div class="step-icon">4</div>
                <p>UPLOAD</p>
            </div>
        </div>
        <!-- animation 1 = STOP -->
        <section id="processbar" style="display:none;"><span class="loader-71"> </span></section>
        <div id="loader" class="lds-dual-ring hidden overlay"></div>


        <div class="partition">
            <div class="left-box">
                <div class="seo-plugin-data-info container api_key_setting_form">
                    <div class="inner-content-data">
                        <h2 class="boxtitle ">API Key Setting</h2>
                        <form id="api_key_setting_form" method="post" autocomplete="off">
                            <?php wp_nonce_field('review_api_key', 'review_api_key_nonce'); ?>
                            <div class="field_container">
                                <div class="input-field">
                                    <input type="text" required id="review_api_key"
                                        data-apiValid="<?php echo esc_attr($get_api_status ? $get_api_status : 0); ?>"
                                        spellcheck="false"
                                        value="<?php echo esc_attr($get_existing_api_key ? $get_existing_api_key : ''); ?>">
                                    <label>API Key</label>
                                    <span class="correct-sign">✓</span>
                                    <span class="wrong-sign">×</span>
                                </div>
                            </div>
                            <div class="twoToneCenter">
                                <button type="submit" class="submit_btn save btn-process">Save</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="seo-plugin-data-info container google_review_upload_form cont hidden">
                    <?php
                    if ($firm_data) {
                        ?>
                        <p class="reset new">
                            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 20 20" fill="#fff"
                                xmlns:v="https://vecta.io/nano">
                                <path
                                    d="M5.05 14.95a1 1 0 0 1 1.414-1.414A4.98 4.98 0 0 0 10 15a5 5 0 0 0 5-5 1 1 0 1 1 2 0 7 7 0 0 1-7 7 6.98 6.98 0 0 1-4.95-2.05z" />
                                <path d="M13.559 12.832a1 1 0 1 1-1.109-1.664l3-2a1 1 0 1 1 1.109 1.664l-3 2z" />
                                <path
                                    d="M18.832 12.445a1 1 0 1 1-1.664 1.109l-2-3a1 1 0 0 1 1.664-1.109l2 3zm-3.975-7.594a1 1 0 0 1-1.414 1.414 4.98 4.98 0 0 0-3.536-1.464 5 5 0 0 0-5 5 1 1 0 1 1-2 0 7 7 0 0 1 7-7 6.98 6.98 0 0 1 4.95 2.05z" />
                                <path d="M6.349 6.969a1 1 0 1 1 1.109 1.664l-3 2a1 1 0 1 1-1.109-1.664l3-2z" />
                                <path d="M1.075 7.356a1 1 0 1 1 1.664-1.109l2 3a1 1 0 1 1-1.664 1.109l-2-3z" />
                            </svg>
                        </p>
                        <?php
                    }
                    ?>
                    <div class="inner-content-data">
                        <h2 class="boxtitle ">Google Reviews Upload</h2>
                        <span class="correct-sign firm_area_sign" style="display:none">✓</span>
                        <span class="wrong-sign firm_area_sign" style="display:none">×</span>
                        <form id="google_review_upload_form" method="post" autocomplete="off">
                            <?php wp_nonce_field('get_set_trigger', 'get_set_trigger_nonce'); ?>
                            <div class="field_container">
                                <div class="input-field">
                                    <input <?php echo ($jflag ? 'disabled' : ''); ?> type="text" id="firm_name"
                                        data-termID="<?php echo esc_attr($j_term_id ? $j_term_id : 0); ?>"
                                        data-jobid="<?php echo esc_attr($job_id_data ? $job_id_data : ''); ?>" required
                                        spellcheck="false"
                                        value="<?php echo esc_attr($firm_name_data ? $firm_name_data : ''); ?>">
                                    <label>Firm Name</label>
                                    <button <?php echo ($jflag ? 'disabled' : ''); ?> type="submit"
                                        class="search_btn <?php echo ($jflag ? 'pointer_none' : ''); ?>"><span
                                            class="material-icons">Search</span></button>
                                </div>
                                <div class="search-result">
                                </div>
                            </div>
                            <?php
                            $get_d = 0;
                            if ((!empty($getjdata['jobID_json']) && $getjdata['jobID_json'] == 1) && ($getjdata['jobID_check_status'] == 1) && ($getjdata['jobID_check'] == 0 && $getjdata['jobID_final'] == 0)) {
                                $get_d = 1;
                            }
                            ?>
                            <div class="submit_btn_setget twoToneCenter">
                                <button type="submit" class="submit_btn job_start btn-process" disabled><span
                                        class="label">JOB START</span></button>
                                <button type="submit" class="submit_btn check_start_status btn-process"
                                    style="display:none;"><span class="label">CHECK STATUS</span></button>
                                <button type="submit" class="submit_btn check_start btn-process" <?php echo ($jp != 1 ? 'disabled' : '') ?>><span class="label">GET</span></button>
                                <button type="submit" class="submit_btn upload_start btn-process"><span
                                        class="label">UPLOAD</span></button>
                            </div>
                        </form>
                    </div>
                </div>



            </div>


            <div class="right-box">

                <div class="inner-content-data">
                    <h2 class="boxtitle display_total">
                        <p class="reset status"><svg xmlns="http://www.w3.org/2000/svg" width="35" height="35"
                                viewBox="0 0 20 20" fill="#fff" xmlns:v="https://vecta.io/nano">
                                <path
                                    d="M5.05 14.95a1 1 0 0 1 1.414-1.414A4.98 4.98 0 0 0 10 15a5 5 0 0 0 5-5 1 1 0 1 1 2 0 7 7 0 0 1-7 7 6.98 6.98 0 0 1-4.95-2.05z" />
                                <path d="M13.559 12.832a1 1 0 1 1-1.109-1.664l3-2a1 1 0 1 1 1.109 1.664l-3 2z" />
                                <path
                                    d="M18.832 12.445a1 1 0 1 1-1.664 1.109l-2-3a1 1 0 0 1 1.664-1.109l2 3zm-3.975-7.594a1 1 0 0 1-1.414 1.414 4.98 4.98 0 0 0-3.536-1.464 5 5 0 0 0-5 5 1 1 0 1 1-2 0 7 7 0 0 1 7-7 6.98 6.98 0 0 1 4.95 2.05z" />
                                <path d="M6.349 6.969a1 1 0 1 1 1.109 1.664l-3 2a1 1 0 1 1-1.109-1.664l3-2z" />
                                <path d="M1.075 7.356a1 1 0 1 1 1.664-1.109l2 3a1 1 0 1 1-1.664 1.109l-2-3z" />
                            </svg></p>
                        Detail Status
                    </h2>
                    <div class="typewriter">
                        <div class="output typing">
                            <p><?php echo esc_html(displayMessagesFromFile()); ?></p>
                        </div>
                    </div>

                </div>


            </div>


            <button class="control" style="display:none;"></button>
            <canvas id="canvas"></canvas>
        </div>
    </div>
    <?php
}

// echo do_shortcode('[next_cron_run]');
// Add custom columns to post type
function custom_add_custom_columns($columns)
{
    $new_columns = array();
    foreach ($columns as $key => $value) {
        $new_columns[$key] = $value;
        if ($key === 'title') {
            $new_columns['rating'] = '<span style="display: block; text-align: center;">Rating</span>';
            $new_columns['read_more'] = '<span style="display: block; text-align: center;">URL</span>';
            $new_columns['publish_date'] = '<span style="display: block; text-align: center;">Review Date</span>';
            $new_columns['img'] = '<span style="display: block; text-align: left;">Picture</span>';
            $new_columns['business'] = '<span style="display: block; text-align: center;">Business</span>';
        }
    }
    return $new_columns;
}

add_filter('manage_agr_google_review_posts_columns', 'custom_add_custom_columns');
function custom_display_custom_columns($column, $post_id)
{
    switch ($column) {
        case 'rating':
            $rating_count = intval(get_post_meta($post_id, 'rating', true));
            $rating_count = min(max($rating_count, 0), 5);

            $stars_html = '<div class="star-rating">';
            for ($i = 1; $i <= 5; $i++) {
                if ($i <= $rating_count) {
                    $stars_html .= '<span class="star filled">&#9733;</span>';
                } else {
                    $stars_html .= '<span class="star">&#9734;</span>';
                }
            }
            $stars_html .= '</div>';
            echo wp_kses_post($stars_html);
            break;
        case 'read_more':
            $read_more_url = get_post_meta($post_id, 'url', true);
            echo '<div style="text-align: center;"><a href="' . esc_url($read_more_url) . '" target="_blank">' . esc_html__('Read More', 'your_text_domain') . '</a></div>';
            break;
        case 'publish_date':
            $publish_date = get_post_meta($post_id, 'publish_date', true);
            echo '<div>' . esc_html($publish_date) . '</div>';
            break;
        case 'img':
            $reviewer_picture_url = get_post_meta($post_id, 'reviewer_picture_url', true);
            echo '<img src="' . esc_url($reviewer_picture_url) . '"</img>';
            break;
        case 'business':
            $terms = get_the_terms($post_id, 'business');
            if ($terms && !is_wp_error($terms)) {
                $term_names = array();
                foreach ($terms as $term) {
                    $term_names[] = $term->name;
                }
                echo '<div>' . esc_html(implode(', ', $term_names)) . '</div>';
            } else {
                echo '<div>' . esc_html__('No Term', 'google-reviews') . '</div>';
            }
            break;
    }

}
add_action('manage_agr_google_review_posts_custom_column', 'custom_display_custom_columns', 16, 2);

// Make the Review Date column sortable
function custom_sortable_columns($columns)
{
    $columns['publish_date'] = 'publish_date'; // 'publish_date' is the ID of the column
    return $columns;
}
add_filter('manage_edit-agr_google_review_sortable_columns', 'custom_sortable_columns');

// Custom sorting logic for the Review Date column
function custom_orderby($query)
{
    if (!is_admin() || !$query->is_main_query()) {
        return;
    }

    if ($query->get('orderby') == 'publish_date') {
        // Modify the query to sort by your custom field (e.g., meta_key)
        $query->set('meta_key', 'publish_date'); // Replace 'your_meta_key_here' with your actual meta key for the review date
        $query->set('orderby', 'meta_value'); // Change 'meta_value' to 'meta_value_num' if your date is stored as a timestamp

    }
}
add_action('pre_get_posts', 'custom_orderby');



// CHECK BTN STATUS
function check_job_status($client_ip)
{
    global $wpdb;

    $table_data = $wpdb->prefix . 'jobdata';
    $table_api = $wpdb->prefix . 'jobapi';

    // Check if the last record with the client_ip exists in both tables with specific conditions
    $result = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT COUNT(*) AS count 
        FROM (
            SELECT data.jobID_json, data.jobID_check, data.jobID_check_status, data.jobID_final
            FROM $table_data AS data
            INNER JOIN $table_api AS api ON data.client_ip = api.client_ip 
            WHERE data.client_ip = %s 
            ORDER BY data.id DESC
            LIMIT 1
        ) AS last_record
        WHERE last_record.jobID_json = 1",
            $client_ip
        )
    );

    return $result->count == 1 ? true : false;
}

// UPLOAD BTN STATUS
function check_upload_job_status($client_ip)
{
    global $wpdb;

    $table_data = $wpdb->prefix . 'jobdata';
    $table_api = $wpdb->prefix . 'jobapi';

    // Check if the last record with the client_ip exists in both tables with specific conditions
    $result = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT COUNT(*) AS count 
        FROM (
            SELECT data.jobID_json, data.jobID_check, data.jobID_check_status, data.jobID_final
            FROM $table_data AS data
            INNER JOIN $table_api AS api ON data.client_ip = api.client_ip 
            WHERE data.client_ip = %s 
            ORDER BY data.id DESC
            LIMIT 1
        ) AS last_record
        WHERE last_record.jobID_json = 1 AND last_record.jobID_check = 1 AND last_record.jobID_check_status = 1 AND last_record.jobID_final = 1",
            $client_ip
        )
    );

    return $result->count == 1 ? true : false;
}

/**
 * Function to delete all data of a specific post type and taxonomy.
 */

// add_action( 'admin_init', 'delete_all_agr_google_reviews' );
function delete_all_agr_google_reviews()
{
    global $wpdb;
    $post_type = 'agr_google_review';
    $taxonomy = 'business';
    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$wpdb->posts} WHERE post_type = %s",
            $post_type
        )
    );
    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$wpdb->term_taxonomy} WHERE taxonomy = %s",
            $taxonomy
        )
    );
    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$wpdb->term_relationships} WHERE term_taxonomy_id NOT IN (SELECT term_taxonomy_id FROM {$wpdb->term_taxonomy}) AND object_id NOT IN (SELECT ID FROM {$wpdb->posts} WHERE post_type = %s)",
            $post_type
        )
    );
}

// check job status
function check_prepared_job_status($review_api_key)
{

    global $wpdb;

    $table_data = $wpdb->prefix . 'jobdata';
    $table_api = $wpdb->prefix . 'jobapi';

    // Check if the last record with the review_api_key exists in both tables with specific conditions
    $result = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT COUNT(*) AS count 
        FROM (
            SELECT data.jobID_json, data.jobID_check, data.jobID_check_status, data.jobID_final
            FROM $table_data AS data
            INNER JOIN $table_api AS api ON data.review_api_key = api.review_api_key 
            WHERE data.review_api_key = %s 
            ORDER BY data.id DESC
            LIMIT 1
        ) AS last_record
        WHERE last_record.jobID_json = 1 AND last_record.jobID_check_status = 1 AND last_record.jobID_check = 0 AND last_record.jobID_final = 0",
            $review_api_key
        )
    );

    return $result->count == 1 ? true : false;
}




// Get job data by client IP
function get_job_data_by_api_key($review_api_key)
{
    global $wpdb;

    // Table names should not be part of the prepared statement
    $table_data = $wpdb->prefix . 'jobdata';
    $table_api = $wpdb->prefix . 'jobapi';

    // Prepare the SQL statement with placeholders
    $sql = "
        SELECT data.jobID_json, data.jobID_check_status, data.jobID_check, data.jobID_final
        FROM $table_data AS data
        INNER JOIN $table_api AS api ON data.review_api_key = api.review_api_key 
        WHERE data.review_api_key = %s
        ORDER BY data.id DESC
        LIMIT 1
    ";

    // Use the prepare method to safely insert the review_api_key
    $query = $wpdb->prepare($sql, $review_api_key);

    // Execute the query and fetch the result
    $job_data = $wpdb->get_row($query, ARRAY_A);

    return $job_data;
}
