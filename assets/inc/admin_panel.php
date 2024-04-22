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

    // Add submenu item
    add_submenu_page(
        'awesome-google-review', // Parent slug
        'Delete Review', // Page title
        'Delete Review', // Menu title
        'manage_options', // Capability
        'delete-review', // Menu slug
        'delete_review_callback' // Callback function
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
            jobID_final bigint(20) NOT NULL,
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
    // delete_option('firm_name');
    // delete_option('review_api_key_status');
    // delete_option('business_valid');
    // delete_option('review_api_key');
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

// Allow radio button instead of checkboxes for hierarchical taxonomies
function term_radio_checklist( $args ) {
    if ( ! empty( $args['taxonomy'] ) && $args['taxonomy'] === 'business' /* <== Change to your required taxonomy */ ) {
        if ( empty( $args['walker'] ) || is_a( $args['walker'], 'Walker' ) ) { // Don't override 3rd party walkers.
            if ( ! class_exists( 'term_radio_checklist' ) ) {                
                class term_radio_checklist extends Walker_Category_Checklist {
                    function walk( $elements, $max_depth, ...$args ) {
                        $output = parent::walk( $elements, $max_depth, ...$args );
                        $output = str_replace(
                            array( 'type="checkbox"', "type='checkbox'" ),
                            array( 'type="radio"', "type='radio'" ),
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
add_filter( 'wp_terms_checklist_args', 'term_radio_checklist' );


function render_agr_google_review_meta_box($post) {
    // Retrieve the current values of meta fields
    $job_id = get_post_meta($post->ID, 'job_id', true);
    $id = get_post_meta($post->ID, 'post_review_id', true);    
    $reviewer_name = get_post_meta($post->ID, 'reviewer_name', true);
    $reviewer_picture_url = get_post_meta($post->ID, 'reviewer_picture_url', true);
    $url = get_post_meta($post->ID, 'url', true);
    $rating = get_post_meta($post->ID, 'rating', true);
    $text = get_post_meta($post->ID, 'text', true);
    $publish_date = get_post_meta($post->ID, 'publish_date', true);

    // Output the second table for Place ID on the right side
    echo '<table class="form-table" style="border-bottom:1px solid #c3c4c7">';
    echo '<tr><th>' . __('Job ID:', 'awesome-google-review') . '</th><td><input style="background:#ccc;" readonly type="text" id="job_id" name="job_id" value="' . esc_attr($job_id) . '" /></td></tr>';
    echo '<tr><th>' . __('Unique ID:', 'awesome-google-review') . '</th><td><input style="background:#ccc;" readonly type="text" id="post_review_id" name="post_review_id" value="' . esc_attr($id) . '" /></td></tr>';    
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

function delete_review_callback(){ ?>

<div class="partition">
<div class="right-box">        
        <div class="inner-content-data">
                <h2 class="boxtitle display_total">Delete Review</h2>
                <div class="delete_reviews">

                    <!-- <h1 class="output typing"></h1> -->

                    <div class="center">
                    <select name="sources" id="sources" class="custom-select sources" placeholder="Select Business">
                        <option value="profile">Select business</option>
                        <option value="profile">Profile</option>
                        <option value="word">Word</option>
                        <option value="hashtag">Hashtag</option>
                    </select>

                    <div class="delete_svg">
                        <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 256 256" xmlns:v="https://vecta.io/nano"><g transform="matrix(2.81 0 0 2.81 1.4066 1.4066)"><circle cx="45" cy="45" r="45" fill="#e63e32"/><path d="M65.592 29.574h-9.171v-3.481c0-3.635-2.957-6.593-6.593-6.593h-9.656c-3.635 0-6.593 2.958-6.593 6.593v3.481h-9.171a2 2 0 1 0 0 4h3.394V60.41c0 5.563 4.526 10.09 10.09 10.09h14.215c5.563 0 10.09-4.526 10.09-10.09V33.574h3.395a2 2 0 1 0 0-4zm-28.013-3.481c0-1.43 1.163-2.593 2.593-2.593h9.656c1.43 0 2.593 1.163 2.593 2.593v3.481H37.579v-3.481zM58.197 60.41a6.1 6.1 0 0 1-6.09 6.09H37.892a6.1 6.1 0 0 1-6.09-6.09V33.574h26.395V60.41zM40.3 39.566a2 2 0 0 0-2 2V56.78a2 2 0 1 0 4 0V41.566a2 2 0 0 0-2-2zm9.4 0a2 2 0 0 0-2 2V56.78a2 2 0 1 0 4 0V41.566a2 2 0 0 0-2-2z" fill="#fff"/></g></svg>
                    </div>

                    </div>

                    
                </div>                
        </div>
    </div>

    

</div>


<?php
}


function our_google_reviews_callback() {
    $get_existing_api_key = get_existing_api_key();
    $get_api_status = get_api_key_status($get_existing_api_key);    
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
                            <input type="text" required id="review_api_key" data-apiValid="<?php echo ($get_api_status ? $get_api_status : 0)?>" spellcheck="false" value="<?php echo ($get_existing_api_key ? $get_existing_api_key : '')?>">
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
            <?php 
                if($firm_data){
                    ?>
                    <p class="reset">
                        <!-- <svg xmlns="http://www.w3.org/2000/svg" width="35" height="35" viewBox="0 0 256 256" xmlns:v="https://vecta.io/nano"><g transform="matrix(2.81 0 0 2.81 1.4066 1.4066)"><circle cx="45" cy="45" r="45" fill="#e63e32"/><path d="M65.592 29.574h-9.171v-3.481c0-3.635-2.957-6.593-6.593-6.593h-9.656c-3.635 0-6.593 2.958-6.593 6.593v3.481h-9.171a2 2 0 1 0 0 4h3.394V60.41c0 5.563 4.526 10.09 10.09 10.09h14.215c5.563 0 10.09-4.526 10.09-10.09V33.574h3.395a2 2 0 1 0 0-4zm-28.013-3.481c0-1.43 1.163-2.593 2.593-2.593h9.656c1.43 0 2.593 1.163 2.593 2.593v3.481H37.579v-3.481zM58.197 60.41a6.1 6.1 0 0 1-6.09 6.09H37.892a6.1 6.1 0 0 1-6.09-6.09V33.574h26.395V60.41zM40.3 39.566a2 2 0 0 0-2 2V56.78a2 2 0 1 0 4 0V41.566a2 2 0 0 0-2-2zm9.4 0a2 2 0 0 0-2 2V56.78a2 2 0 1 0 4 0V41.566a2 2 0 0 0-2-2z" fill="#fff"/></g></svg> -->
                        <svg fill="#ffffff" width="35px" height="35px" viewBox="-1075.2 -1075.2 4070.40 4070.40" xmlns="http://www.w3.org/2000/svg" stroke="#ffffff"><g id="SVGRepo_bgCarrier" stroke-width="0"><rect x="-1075.2" y="-1075.2" width="4070.40" height="4070.40" rx="2035.2" fill="rgb(230, 62, 50)" strokewidth="0"></rect></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M960 0v213.333c411.627 0 746.667 334.934 746.667 746.667S1371.627 1706.667 960 1706.667 213.333 1371.733 213.333 960c0-197.013 78.4-382.507 213.334-520.747v254.08H640V106.667H53.333V320h191.04C88.64 494.08 0 720.96 0 960c0 529.28 430.613 960 960 960s960-430.72 960-960S1489.387 0 960 0" fill-rule="evenodd"></path> </g></svg>
                    </p>
                    <?php
                }
            ?>
            

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
                        <button type="submit" class="submit_btn upload_start btn-process"><span class="label">UPLOAD</span></button>
                    </div>

                    <!-- <div class="submit_btn_setget twoToneCenter">
                        <button type="submit" class="submit_btn upload_start btn-process"><span class="label">UPLOAD</span></button>                        
                    </div> -->
                </form>
                
            </div>
        </div>
    </div>

    <div class="right-box">        
        <div class="inner-content-data">
                <h2 class="boxtitle display_total">
                <p class="reset status">
                <!-- <svg xmlns="http://www.w3.org/2000/svg" width="35" height="35" viewBox="0 0 256 256" xmlns:v="https://vecta.io/nano"><g transform="matrix(2.81 0 0 2.81 1.4066 1.4066)"><circle cx="45" cy="45" r="45" fill="#e63e32"/><path d="M65.592 29.574h-9.171v-3.481c0-3.635-2.957-6.593-6.593-6.593h-9.656c-3.635 0-6.593 2.958-6.593 6.593v3.481h-9.171a2 2 0 1 0 0 4h3.394V60.41c0 5.563 4.526 10.09 10.09 10.09h14.215c5.563 0 10.09-4.526 10.09-10.09V33.574h3.395a2 2 0 1 0 0-4zm-28.013-3.481c0-1.43 1.163-2.593 2.593-2.593h9.656c1.43 0 2.593 1.163 2.593 2.593v3.481H37.579v-3.481zM58.197 60.41a6.1 6.1 0 0 1-6.09 6.09H37.892a6.1 6.1 0 0 1-6.09-6.09V33.574h26.395V60.41zM40.3 39.566a2 2 0 0 0-2 2V56.78a2 2 0 1 0 4 0V41.566a2 2 0 0 0-2-2zm9.4 0a2 2 0 0 0-2 2V56.78a2 2 0 1 0 4 0V41.566a2 2 0 0 0-2-2z" fill="#fff"/></g></svg> -->
                <svg fill="#ffffff" width="35px" height="35px" viewBox="-1075.2 -1075.2 4070.40 4070.40" xmlns="http://www.w3.org/2000/svg" stroke="#ffffff"><g id="SVGRepo_bgCarrier" stroke-width="0"><rect x="-1075.2" y="-1075.2" width="4070.40" height="4070.40" rx="2035.2" fill="rgb(230, 62, 50)" strokewidth="0"></rect></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M960 0v213.333c411.627 0 746.667 334.934 746.667 746.667S1371.627 1706.667 960 1706.667 213.333 1371.733 213.333 960c0-197.013 78.4-382.507 213.334-520.747v254.08H640V106.667H53.333V320h191.04C88.64 494.08 0 720.96 0 960c0 529.28 430.613 960 960 960s960-430.72 960-960S1489.387 0 960 0" fill-rule="evenodd"></path> </g></svg>
            </p>    
                Status</h2>
                <div class="typewriter">
                    <h1 class="output typing"><p><?php echo displayMessagesFromFile(); ?></p></h1>
                </div>                
        </div>
    </div>

    <button class="control" style="display:none;"></button>
    <canvas id="canvas"></canvas>

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
            $new_columns['job_id'] = '<span style="display: block; text-align: left;">Job ID</span>';
            $new_columns['business'] = '<span style="display: block; text-align: center;">Business</span>';
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

        case 'job_id':
            $publish_date = get_post_meta($post_id, 'job_id', true);
            echo '<div>' . esc_html($publish_date) . '</div>';
            break;

        case 'business':
            // Get the taxonomy terms associated with the post
            $terms = get_the_terms($post_id, 'business');
            // Check if terms exist
            if ($terms && !is_wp_error($terms)) {
                $term_names = array();
                foreach ($terms as $term) {
                    // Get term names
                    $term_names[] = $term->name;
                }
                // Output the term names separated by commas
                echo '<div>' . esc_html(implode(', ', $term_names)) . '</div>';
            } else {
                echo '<div>' . esc_html__('No Term', 'google-reviews') . '</div>';
            }
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
            SELECT data.jobID_json, data.jobID_check, data.jobID_final
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
            SELECT data.jobID_json, data.jobID_check, data.jobID_final
            FROM $table_data AS data
            INNER JOIN $table_api AS api ON data.client_ip = api.client_ip 
            WHERE data.client_ip = %s 
            ORDER BY data.id DESC
            LIMIT 1
        ) AS last_record
        WHERE last_record.jobID_json = 1 AND last_record.jobID_check = 1 AND last_record.jobID_final = 1",
        $client_ip
    ));

    return $result->count == 1 ? true : false;
}