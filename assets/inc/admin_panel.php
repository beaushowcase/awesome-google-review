<?php
// Add a setting page
function our_google_reviews_add_menu_page() {
    add_menu_page(
        'Our Google Reviews',
        'Review Settings',
        'manage_options',
        'awesome-google-review',
        'our_google_reviews_callback',
        'dashicons-google'
    );
}
add_action('admin_menu', 'our_google_reviews_add_menu_page');

// Setting Link at plugin
function add_settings_link($links, $file) {
    if (strpos($file, 'awesome-google-review/awesome-google-review.php') !== false) {
        $settings_link = '<a href="' . esc_url(get_admin_url(null, 'admin.php?page=awesome-google-review')) . '">' . __('Review Settings') . '</a>';
        array_unshift($links, $settings_link);
    }
    return $links;
}
add_filter('plugin_action_links', 'add_settings_link', 10, 2);

// Create post type on activation
register_activation_hook(__FILE__, 'awesome_google_review_plugin_activate');

function awesome_google_review_plugin_activate() {
    add_agr_google_review_post_type();    
    flush_rewrite_rules();
}

// Function to create table
function job_table() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'jobdata';   
    $table_name1 = $wpdb->prefix . 'jobapi'; 

    // Check if the table exists already
    if( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name ) {

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            review_api_key varchar(255) NOT NULL,
            jobID bigint(20) NOT NULL,
            jobID_json bigint(20) NOT NULL,
            jobID_check bigint(20) NOT NULL,
            firm_name varchar(255) NOT NULL,
            client_ip varchar(255) NOT NULL,
            created datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id)
            ) $charset_collate;";

        // Include upgrade.php for dbDelta
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        
        // Create the table
        maybe_create_table( $table_name, $sql );
    }


    // Check if the table exists already
    if( $wpdb->get_var( "SHOW TABLES LIKE '$table_name1'" ) != $table_name1 ) {

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name1 (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            review_api_key varchar(255) NOT NULL,
            review_api_key_status varchar(255) NOT NULL,
            client_ip varchar(255) NOT NULL,
            created datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id)           
        ) $charset_collate;";

        // Include upgrade.php for dbDelta
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        
        maybe_create_table( $table_name1, $sql );
    }
}

// Hide post type on deactivation
register_deactivation_hook(__FILE__, 'awesome_google_review_plugin_deactivate');
function awesome_google_review_plugin_deactivate() {
    unregister_post_type('agr_google_review');
    flush_rewrite_rules();
}

// Remove data on deletion
register_uninstall_hook(__FILE__, 'awesome_google_review_plugin_uninstall');
function awesome_google_review_plugin_uninstall() {
    delete_option('firm_name');
    delete_option('review_api_key_status');
    delete_option('business_valid');
    delete_option('review_api_key');
}

add_action('init', 'add_agr_google_review_post_type');
function add_agr_google_review_post_type() {
    $labels = array(
        'name'               => _x('Google Reviews', 'post type general name', 'awesome-google-review'),
        'singular_name'      => _x('Google Review', 'post type singular name', 'awesome-google-review'),
        'menu_name'          => _x('Google Reviews', 'admin menu', 'awesome-google-review'),
        'name_admin_bar'     => _x('Google Review', 'add new on admin bar', 'awesome-google-review'),
        'add_new'            => _x('Add New', 'Google Review', 'awesome-google-review'),
        'add_new_item'       => __('Add New Google Review', 'awesome-google-review'),
        'new_item'           => __('New Google Review', 'awesome-google-review'),
        'edit_item'          => __('Edit Google Review', 'awesome-google-review'),
        'view_item'          => __('View Google Review', 'awesome-google-review'),
        'all_items'          => __('All Google Reviews', 'awesome-google-review'),
        'search_items'       => __('Search Google Reviews', 'awesome-google-review'),
        'parent_item_colon'  => __('Parent Google Reviews:', 'awesome-google-review'),
        'not_found'          => __('No Google Reviews found.', 'awesome-google-review'),
        'not_found_in_trash' => __('No Google Reviews found in Trash.', 'awesome-google-review'),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array('slug' => 'google-reviews'),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'menu_icon'          => 'dashicons-google',
        'hierarchical'       => false,
        'menu_position'      => null,
        'supports'           => array(''),
    );

    register_post_type('agr_google_review', $args);

    // Register 'Business' custom taxonomy
    register_taxonomy(
        'business',
        'agr_google_review',
        array(
            'label' => __('Business'),
            'rewrite' => array('slug' => 'business'),
            'hierarchical' => true,
        )
    );

    add_action('add_meta_boxes', 'add_agr_google_review_meta_box');

    job_table();
}

function add_agr_google_review_meta_box() {
    add_meta_box(
        'agr_google_review_meta_box',
        __('Google Review Details', 'awesome-google-review'),
        'render_agr_google_review_meta_box',
        'agr_google_review',
        'normal',
        'default'
    );
}

function render_agr_google_review_meta_box($post) {
    // Retrieve the current values of meta fields
    $id = get_post_meta($post->ID, 'post_review_id', true);
    $reviewer_name = get_post_meta($post->ID, 'reviewer_name', true);
    $reviewer_picture_url = get_post_meta($post->ID, 'reviewer_picture_url', true);
    $url = get_post_meta($post->ID, 'url', true);
    $rating = get_post_meta($post->ID, 'rating', true);
    $text = get_post_meta($post->ID, 'text', true);
    $publish_date = get_post_meta($post->ID, 'publish_date', true);

    // Output the second table for Place ID on the right side
    echo '<table class="form-table" style="border-bottom:1px solid #c3c4c7">';
    echo '<tr><th>' . __('ID:', 'awesome-google-review') . '</th><td><input style="background:#ccc;" readonly type="text" id="post_review_id" name="post_review_id" value="' . esc_attr($id) . '" /></td></tr>';
    echo '</table>';

    // Output a table
    echo '<table class="form-table" style="width:auto">';
    echo '<tr><th>' . __('Reviewer Name:', 'awesome-google-review') . '</th><td><input readonly type="text" id="reviewer_name" name="reviewer_name" value="' . esc_attr($reviewer_name) . '" /></td></tr>';
    echo '<tr><th>' . __('Reviewer Picture URL:', 'awesome-google-review') . '</th><td><input readonly type="text" id="reviewer_picture_url" name="reviewer_picture_url" value="' . esc_url($reviewer_picture_url) . '" /></td></tr>';
    echo '<tr><th>' . __('Read More URL:', 'awesome-google-review') . '</th><td><input readonly type="text" id="url" name="url" value="' . esc_url($url) . '" /></td></tr>';
    echo '<tr><th>' . __('Rating:', 'awesome-google-review') . '</th><td><input readonly type="number" id="rating" name="rating" value="' . esc_attr($rating) . '" /></td></tr>';
    echo '<tr><th>' . __('Description:', 'awesome-google-review') . '</th><td><textarea readonly id="text" name="text" rows="4" cols="23">' . esc_textarea($text) . '</textarea></td></tr>';
    echo '<tr><th>' . __('Publish Date:', 'awesome-google-review') . '</th><td><input readonly type="text" id="publish_date" name="publish_date" value="' . esc_attr($publish_date) . '" /></td></tr>';
    echo '</table>';
}

function shortcode_display() {}


function our_google_reviews_callback() {
    $firm_name = get_option('firm_name');
    $review_api_key = get_option('review_api_key');
?>

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
                            <input type="text" id="review_api_key" required spellcheck="false" value="<?php echo get_existing_api_key(); ?>">
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

   
        <?php        
        $firm_data = get_existing_firm_data();
        $firm_name_data = isset($firm_data['firm_name']) ? $firm_data['firm_name'] : '';        
        $job_id_data = isset($firm_data['jobID']) ? $firm_data['jobID'] : '';    
     
        ?>


        <div class="seo-plugin-data-info container google_review_upload_form cont hidden">
            <div class="inner-content-data">
                <h2 class="boxtitle ">Google Reviews Upload</h2>
                
                <form id="google_review_upload_form" method="post" autocomplete="off">
                    <?php wp_nonce_field('get_set_trigger', 'get_set_trigger_nonce'); ?>
                    <div class="field_container">
                        <div class="input-field">
                            <input type="text" id="firm_name" data-jobid="<?php echo esc_attr($job_id_data ? $job_id_data : ''); ?>" required spellcheck="false" value="<?php echo esc_attr($firm_name_data ? $firm_name_data : ''); ?>">
                            <label>Firm Name</label>
                            <span class="correct-sign">✓</span>
                            <span class="wrong-sign">×</span>
                        </div>
                    </div>
            
                    <div class="submit_btn_setget twoToneCenter">
                        <button type="submit" class="submit_btn job_start btn-process"><span class="label">JOB START</span></button>
                        <button type="submit" class="submit_btn check_start btn-process"><span class="label">CHECK</span></button>
                    </div>

                    <div class="submit_btn_setget twoToneCenter">
                        <button type="submit" class="submit_btn upload_start btn-process"><span class="label">UPLOAD</span></button>                        
                    </div>
                </form>
                
            </div>
        </div>
    </div>

    <div class="right-box">        
        <div class="inner-content-data">
                <h2 class="boxtitle display_total">Status</h2>
                <div class="typewriter">
                    <h1 class="output"> <p>...</p> </h1>
                </div>                
        </div>
    </div>

    <!-- <div class="right-box">        
        <button class="upload-btn">Upload</button>
    </div> -->

    

</div>
<?php
}

// Add custom columns to post type
function custom_add_custom_columns($columns) {
    $new_columns = array();

    foreach ($columns as $key => $value) {
        $new_columns[$key] = $value;
        if ($key === 'title') {
            // Add the custom columns after the title
            $new_columns['rating'] = '<span style="display: block; text-align: center;">Rating</span>';
            $new_columns['read_more'] = '<span style="display: block; text-align: center;">URL</span>';
            $new_columns['publish_date'] = '<span style="display: block; text-align: center;">Review Date</span>';
        }
    }
    return $new_columns;
}
add_filter('manage_agr_google_review_posts_columns', 'custom_add_custom_columns');

// Display custom meta values in the custom columns
function custom_display_custom_columns($column, $post_id) {
    switch ($column) {
        case 'rating':
            $rating_count = get_post_meta($post_id, 'rating', true);
            $stars_html = '<div style="text-align: center;">'; // Centering div
            for ($i = 0; $i < 5; $i++) {
                if ($i < $rating_count) {
                    // Fill star
                    $stars_html .= '<svg width="24px" height="24px" enable-background="new 0 0 64 64" version="1.0" viewBox="0 0 64 64" xml:space="preserve" xmlns="http://www.w3.org/2000/svg">	<path d="m63.893 24.277c-0.238-0.711-0.854-1.229-1.595-1.343l-19.674-3.006-8.815-18.778c-0.33-0.702-1.036-1.15-1.811-1.15s-1.48 0.448-1.811 1.15l-8.815 18.778-19.674 3.007c-0.741 0.113-1.356 0.632-1.595 1.343-0.238 0.71-0.059 1.494 0.465 2.031l14.294 14.657-3.378 20.704c-0.124 0.756 0.195 1.517 0.822 1.957 0.344 0.243 0.747 0.366 1.151 0.366 0.332 0 0.666-0.084 0.968-0.25l17.572-9.719 17.572 9.719c0.302 0.166 0.636 0.25 0.968 0.25 0.404 0 0.808-0.123 1.151-0.366 0.627-0.44 0.946-1.201 0.822-1.957l-3.378-20.704 14.294-14.657c0.525-0.538 0.705-1.322 0.467-2.032z" fill="#2271b1"/></svg>';
                } else {
                    // Not-fill star
                    $stars_html .= '<svg width="24px" height="24px" enable-background="new 0 0 64 64" version="1.0" viewBox="0 0 64 64" xml:space="preserve" xmlns="http://www.w3.org/2000/svg"><path d="m32.001 2.484c0.279 0 0.463 0.509 0.463 0.509l8.806 18.759 20.729 3.167-14.999 15.38 3.541 21.701-18.54-10.254-18.54 10.254 3.541-21.701-14.999-15.38 20.729-3.167 8.798-18.743s0.192-0.525 0.471-0.525m0-2.477c-0.775 0-1.48 0.448-1.811 1.15l-8.815 18.778-19.674 3.006c-0.741 0.113-1.356 0.632-1.595 1.343-0.238 0.71-0.059 1.494 0.465 2.031l14.294 14.657-3.378 20.704c-0.124 0.756 0.195 1.517 0.822 1.957 0.344 0.244 0.748 0.367 1.152 0.367 0.332 0 0.666-0.084 0.968-0.25l17.572-9.719 17.572 9.719c0.302 0.166 0.636 0.25 0.968 0.25 0.404 0 0.808-0.123 1.151-0.366 0.627-0.44 0.946-1.201 0.822-1.957l-3.378-20.704 14.294-14.657c0.523-0.537 0.703-1.321 0.465-2.031-0.238-0.711-0.854-1.229-1.595-1.343l-19.674-3.006-8.814-18.779c-0.331-0.702-1.036-1.15-1.811-1.15z" fill="#231F20"/></svg>';
                }
            }
            $stars_html .= '</div>'; // Closing centering div
            echo $stars_html;
            break;
        case 'read_more':
            $read_more_url = get_post_meta($post_id, 'url', true);
            echo '<div style="text-align: center;"><a href="' . esc_url($read_more_url) . '" target="_blank">' . esc_html__('Read More', 'your_text_domain') . '</a></div>';
            break;

        case 'publish_date':
            $publish_date = get_post_meta($post_id, 'publish_date', true);
            echo '<div>' . esc_html($publish_date) . '</div>';
            break;
    }
}
add_action('manage_agr_google_review_posts_custom_column', 'custom_display_custom_columns', 16, 2);

// Make the Review Date column sortable
function custom_sortable_columns($columns) {
    $columns['publish_date'] = 'publish_date'; // 'publish_date' is the ID of the column
    return $columns;
}
add_filter('manage_edit-agr_google_review_sortable_columns', 'custom_sortable_columns');

// Custom sorting logic for the Review Date column
function custom_orderby($query) {
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
function check_job_status($client_ip) {
    global $wpdb;
    
    $table_data = $wpdb->prefix . 'jobdata';
    $table_api = $wpdb->prefix . 'jobapi';

    // Check if the last record with the client_ip exists in both tables with specific conditions
    $result = $wpdb->get_row($wpdb->prepare(
        "SELECT COUNT(*) AS count 
        FROM (
            SELECT data.jobID_json, data.jobID_check
            FROM $table_data AS data
            INNER JOIN $table_api AS api ON data.client_ip = api.client_ip 
            WHERE data.client_ip = %s 
            ORDER BY data.id DESC
            LIMIT 1
        ) AS last_record
        WHERE last_record.jobID_json = 1",
        $client_ip
    ));

    return $result->count == 1 ? true : false;
}

// UPLOAD BTN STATUS
function check_upload_job_status($client_ip) {
    global $wpdb;
    
    $table_data = $wpdb->prefix . 'jobdata';
    $table_api = $wpdb->prefix . 'jobapi';

    // Check if the last record with the client_ip exists in both tables with specific conditions
    $result = $wpdb->get_row($wpdb->prepare(
        "SELECT COUNT(*) AS count 
        FROM (
            SELECT data.jobID_json, data.jobID_check
            FROM $table_data AS data
            INNER JOIN $table_api AS api ON data.client_ip = api.client_ip 
            WHERE data.client_ip = %s 
            ORDER BY data.id DESC
            LIMIT 1
        ) AS last_record
        WHERE last_record.jobID_json = 1 AND last_record.jobID_check = 1",
        $client_ip
    ));

    return $result->count == 1 ? true : false;
}