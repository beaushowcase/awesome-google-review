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
        'Delete Reviews', // Page title
        'Delete Reviews', // Menu title
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
            jobID_check_status bigint(20) NOT NULL,
            jobID_check bigint(20) NOT NULL,            
            jobID_final bigint(20) NOT NULL,
            term_id bigint(20) NOT NULL,
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

<section id="processbar" style="display:none;"><span class="loader-71"> </span></section>

<div class="partition">
<div class="right-box">        
        <div class="inner-content-data">
                <h2 class="boxtitle display_total">Delete Review</h2>
                <form id="review_delete_form" method="post" autocomplete="off">
                    <div class="delete_reviews">
                        <div class="center">                                        
                            <select name="sources" id="sources" class="custom-select sources" placeholder="Select Business">
                                <option class="select_business" value="0">Select business</option>
                                <?php 
                                $all_firms = get_all_firms();                                            
                                foreach ($all_firms as $firm) {                        
                                ?>                        
                                <option class="select_business" value="<?php echo $firm['id']; ?>"><?php echo $firm['name']; ?></option>
                            <?php } ?>                        
                            </select>
                            <div class="">
                                <button type="submit" class="custom-btn btn-16">                    
                                    <section>
                                        <span class="trash">
                                            <span></span>
                                            <i></i>
                                        </span>
                                    </section>
                                </button>
                            </div>
                        </div>
                    </div> 
                </form> 
                
                
        </div>
    </div>

    

</div>


<?php
}


function our_google_reviews_callback() {
    $get_existing_api_key = get_existing_api_key();
    $get_api_status = get_api_key_status($get_existing_api_key);    
?>

<?php        
$firm_data = get_existing_firm_data();
$firm_name_data = isset($firm_data['firm_name']) ? $firm_data['firm_name'] : '';
$job_id_data = isset($firm_data['jobID']) ? $firm_data['jobID'] : '';
$j_term_id = isset($firm_data['term_id']) ? $firm_data['term_id'] : '';
    

// Function to check if ID exists
function isIdExists($array, $idToCheck) {
    foreach ($array as $item) {
        if ($item['id'] == $idToCheck) {
            return true;
        }
    }
    return false;
}


$client_ip = $_SERVER['REMOTE_ADDR'];
$jp = check_prepared_job_status($client_ip);


$getjdata = get_job_data_by_client_ip($client_ip);
$jflag = 0;
if((!empty($getjdata['jobID_json']) && $getjdata['jobID_json'] == 1) && ($getjdata['jobID_check_status'] == 0 || $getjdata['jobID_check'] == 0 || $getjdata['jobID_final'] == 0)){
    $jflag = 1;
}

$step = false;
if((!empty($getjdata['jobID_json']) && $getjdata['jobID_json'] == 1) && ($getjdata['jobID_check_status'] == 0 || $getjdata['jobID_check'] == 0 || $getjdata['jobID_final'] == 0)){
    $step = true;
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
  max-width: 1200px;
  margin: 0 auto;
}

.step-indicator {
    display: flex;
    align-items: center;
    background: transparent;
    color: white;
    border-radius: 8px;
    padding: 40px 70px;
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
    box-shadow: inset 0px 0px 20px 0px rgba(255, 255, 255, .5), 7px 7px 20px 0px rgba(0, 0, 0, .1), 4px 4px 5px 0px rgba(0, 0, 0, .1);
    position: relative;
    margin-top: 5%;
    padding-top: 25px;
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
  background: #000912;
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

@media screen and (max-width: 500px) {
  .step p {
    font-size: 11px;
    bottom: -20px;
  }
}
</style>


<div class="container-process">
  <section class="step-indicator">
      <div class="step step1 active">
          <div class="step-icon">1</div>
        <p>Delivery</p>
      </div>
    <div class="indicator-line active"></div>
    <div class="step step2">
        <div class="step-icon">2</div>
      <p>Payment</p>
    </div>
    <div class="indicator-line"></div>
    <div class="step step3">
        <div class="step-icon">3</div>
      <p>Confirmation</p>
    </div>
  </section>
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
        /*
        <div class="selection_box container">
            <div class="inner-content-data">
                <div class="container_selection_box">    
                    <div class="form cf">
                        <section class="payment-type cf">                            
                        <?php 
                        $all_firms = get_all_firms();
                        $checked = false;                            
                        foreach ($all_firms as $firm) {                                
                            if($firm['id'] == $j_term_id){
                                $checked = true;
                            }                        
                        ?>
                            <input <?php echo ($checked && $firm['id'] == $j_term_id ? 'checked' : ''); ?> type="radio" name="radio3" id="<?php echo $firm['id']; ?>" value="<?php echo $firm['id']; ?>"><label class="paypal-label four col" for="<?php echo $firm['id']; ?>"><?php echo $firm['name']; ?></label>
                            <?php } ?> 
                        </section>
                    </div>
                </div>
            </div>
        </div>
        */ ?>    

        

        <div class="seo-plugin-data-info container google_review_upload_form cont hidden">
            <?php 
                if($firm_data){
                    ?>
                    <p class="reset">
                        <!-- <svg xmlns="http://www.w3.org/2000/svg" width="35" height="35" viewBox="0 0 256 256" xmlns:v="https://vecta.io/nano"><g transform="matrix(2.81 0 0 2.81 1.4066 1.4066)"><circle cx="45" cy="45" r="45" fill="#e63e32"/><path d="M65.592 29.574h-9.171v-3.481c0-3.635-2.957-6.593-6.593-6.593h-9.656c-3.635 0-6.593 2.958-6.593 6.593v3.481h-9.171a2 2 0 1 0 0 4h3.394V60.41c0 5.563 4.526 10.09 10.09 10.09h14.215c5.563 0 10.09-4.526 10.09-10.09V33.574h3.395a2 2 0 1 0 0-4zm-28.013-3.481c0-1.43 1.163-2.593 2.593-2.593h9.656c1.43 0 2.593 1.163 2.593 2.593v3.481H37.579v-3.481zM58.197 60.41a6.1 6.1 0 0 1-6.09 6.09H37.892a6.1 6.1 0 0 1-6.09-6.09V33.574h26.395V60.41zM40.3 39.566a2 2 0 0 0-2 2V56.78a2 2 0 1 0 4 0V41.566a2 2 0 0 0-2-2zm9.4 0a2 2 0 0 0-2 2V56.78a2 2 0 1 0 4 0V41.566a2 2 0 0 0-2-2z" fill="#fff"/></g></svg> -->
                        <!-- <svg fill="#ffffff" width="35px" height="35px" viewBox="-1075.2 -1075.2 4070.40 4070.40" xmlns="http://www.w3.org/2000/svg" stroke="#ffffff"><g id="SVGRepo_bgCarrier" stroke-width="0"><rect x="-1075.2" y="-1075.2" width="4070.40" height="4070.40" rx="2035.2" fill="rgb(230, 62, 50)" strokewidth="0"></rect></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M960 0v213.333c411.627 0 746.667 334.934 746.667 746.667S1371.627 1706.667 960 1706.667 213.333 1371.733 213.333 960c0-197.013 78.4-382.507 213.334-520.747v254.08H640V106.667H53.333V320h191.04C88.64 494.08 0 720.96 0 960c0 529.28 430.613 960 960 960s960-430.72 960-960S1489.387 0 960 0" fill-rule="evenodd"></path> </g></svg> -->
                        
<svg xmlns="http://www.w3.org/2000/svg" width="35" height="35" viewBox="0 0 20 20" fill="#fff" xmlns:v="https://vecta.io/nano"><path d="M5.05 14.95a1 1 0 0 1 1.414-1.414A4.98 4.98 0 0 0 10 15a5 5 0 0 0 5-5 1 1 0 1 1 2 0 7 7 0 0 1-7 7 6.98 6.98 0 0 1-4.95-2.05z"/><path d="M13.559 12.832a1 1 0 1 1-1.109-1.664l3-2a1 1 0 1 1 1.109 1.664l-3 2z"/><path d="M18.832 12.445a1 1 0 1 1-1.664 1.109l-2-3a1 1 0 0 1 1.664-1.109l2 3zm-3.975-7.594a1 1 0 0 1-1.414 1.414 4.98 4.98 0 0 0-3.536-1.464 5 5 0 0 0-5 5 1 1 0 1 1-2 0 7 7 0 0 1 7-7 6.98 6.98 0 0 1 4.95 2.05z"/><path d="M6.349 6.969a1 1 0 1 1 1.109 1.664l-3 2a1 1 0 1 1-1.109-1.664l3-2z"/><path d="M1.075 7.356a1 1 0 1 1 1.664-1.109l2 3a1 1 0 1 1-1.664 1.109l-2-3z"/></svg>


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
                            <input <?php echo($jflag ? 'disabled' : '') ?> list="existing" type="text" id="firm_name" data-termID="<?php echo ($j_term_id ? $j_term_id : 0)?>"  data-jobid="<?php echo esc_attr($job_id_data ? $job_id_data : ''); ?>" required spellcheck="false" value="<?php echo esc_attr($firm_name_data ? $firm_name_data : ''); ?>">
                            <datalist id="existing"> 
                                            <?php 
                                                $all_firms = get_all_firms();                                            
                                                foreach ($all_firms as $firm) {                        
                                                ?>                        
                                                <option class="select_business" data-listID="<?php echo $firm['id']; ?>"><?php echo $firm['name']; ?></option>
                                            <?php } ?>
                                           
                            </datalist>
                            <label class="focus_label">Firm Name</label>
                            <span class="correct-sign">✓</span>
                            <span class="wrong-sign">×</span>
                        </div>
                    </div>
                    
            
                    <div class="submit_btn_setget twoToneCenter">
                        <button type="submit" class="submit_btn job_start btn-process"><span class="label">JOB START</span></button>
                      
                        <!-- <button type="submit" class="submit_btn check_start_status btn-process" style="display:none;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="80" height="100%" viewBox="0 0 256 256" xml:space="preserve"><g style="stroke:none;stroke-width:0;stroke-dasharray:none;stroke-linecap:butt;stroke-linejoin:miter;stroke-miterlimit:10;fill:none;fill-rule:nonzero;opacity:1"><path d="M56.375 60.616c-.75-1.027-2.151-1.388-3.266-.776A16.785 16.785 0 0 1 45 61.905c-7.2 0-13.346-4.532-15.778-10.887h5.968a2.003 2.003 0 0 0 1.674-3.095L20.269 22.551a2 2 0 0 0-3.348 0L.326 47.924A2 2 0 0 0 2 51.019h7.609C12.482 67.96 27.253 80.905 45 80.905c6.736 0 13.203-1.841 18.864-5.348 1.272-.788 1.586-2.509.704-3.718l-8.193-11.223z" style="stroke:none;stroke-width:1;stroke-dasharray:none;stroke-linecap:butt;stroke-linejoin:miter;stroke-miterlimit:10;fill:green;fill-rule:nonzero;opacity:1" transform="matrix(2.81 0 0 2.81 1.407 1.407)"/><path d="M89.759 39.227A1.998 1.998 0 0 0 88 38.179h-7.753C77.051 21.632 62.465 9.095 45 9.095a35.782 35.782 0 0 0-17.291 4.432c-1.307.719-1.7 2.42-.883 3.668l7.613 11.628c.694 1.06 2.072 1.499 3.213.947A16.862 16.862 0 0 1 45 28.095c6.893 0 12.827 4.153 15.455 10.083H54.81a2 2 0 0 0-1.674 3.094l16.595 25.373a2.002 2.002 0 0 0 3.348 0l16.595-25.373a1.999 1.999 0 0 0 .085-2.045z" style="stroke:none;stroke-width:1;stroke-dasharray:none;stroke-linecap:butt;stroke-linejoin:miter;stroke-miterlimit:10;fill:#fff;fill-rule:nonzero;opacity:1" transform="matrix(2.81 0 0 2.81 1.407 1.407)"/></g></svg>
                        </button> -->
                                   
                        <button type="submit" class="submit_btn check_start_status btn-process" style="display:none;"><span class="label">GET</span></button>
                        <button type="submit" class="submit_btn check_start btn-process" <?php echo($jp != 1 ? 'disabled' : '') ?>><span class="label">SET</span></button>
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
              
                
<svg xmlns="http://www.w3.org/2000/svg" width="35" height="35" viewBox="0 0 20 20" fill="#fff" xmlns:v="https://vecta.io/nano"><path d="M5.05 14.95a1 1 0 0 1 1.414-1.414A4.98 4.98 0 0 0 10 15a5 5 0 0 0 5-5 1 1 0 1 1 2 0 7 7 0 0 1-7 7 6.98 6.98 0 0 1-4.95-2.05z"/><path d="M13.559 12.832a1 1 0 1 1-1.109-1.664l3-2a1 1 0 1 1 1.109 1.664l-3 2z"/><path d="M18.832 12.445a1 1 0 1 1-1.664 1.109l-2-3a1 1 0 0 1 1.664-1.109l2 3zm-3.975-7.594a1 1 0 0 1-1.414 1.414 4.98 4.98 0 0 0-3.536-1.464 5 5 0 0 0-5 5 1 1 0 1 1-2 0 7 7 0 0 1 7-7 6.98 6.98 0 0 1 4.95 2.05z"/><path d="M6.349 6.969a1 1 0 1 1 1.109 1.664l-3 2a1 1 0 1 1-1.109-1.664l3-2z"/><path d="M1.075 7.356a1 1 0 1 1 1.664-1.109l2 3a1 1 0 1 1-1.664 1.109l-2-3z"/></svg>

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
            SELECT data.jobID_json, data.jobID_check, data.jobID_check_status, data.jobID_final
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
            SELECT data.jobID_json, data.jobID_check, data.jobID_check_status, data.jobID_final
            FROM $table_data AS data
            INNER JOIN $table_api AS api ON data.client_ip = api.client_ip 
            WHERE data.client_ip = %s 
            ORDER BY data.id DESC
            LIMIT 1
        ) AS last_record
        WHERE last_record.jobID_json = 1 AND last_record.jobID_check = 1 AND last_record.jobID_check_status = 1 AND last_record.jobID_final = 1",
        $client_ip
    ));

    return $result->count == 1 ? true : false;
}



/**
 * Function to delete all data of a specific post type and taxonomy.
 */

// add_action( 'admin_init', 'delete_all_agr_google_reviews' );
function delete_all_agr_google_reviews() {
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
function check_prepared_job_status($client_ip) {
    global $wpdb;
    
    $table_data = $wpdb->prefix . 'jobdata';
    $table_api = $wpdb->prefix . 'jobapi';

    // Check if the last record with the client_ip exists in both tables with specific conditions
    $result = $wpdb->get_row($wpdb->prepare(
        "SELECT COUNT(*) AS count 
        FROM (
            SELECT data.jobID_json, data.jobID_check, data.jobID_check_status, data.jobID_final
            FROM $table_data AS data
            INNER JOIN $table_api AS api ON data.client_ip = api.client_ip 
            WHERE data.client_ip = %s 
            ORDER BY data.id DESC
            LIMIT 1
        ) AS last_record
        WHERE last_record.jobID_json = 1 AND last_record.jobID_check_status = 1 AND last_record.jobID_check = 0 AND last_record.jobID_final = 0",
        $client_ip
    ));

    return $result->count == 1 ? true : false;
}


// Get job data by client IP
function get_job_data_by_client_ip($client_ip) {
    global $wpdb;

    $table_data = $wpdb->prefix . 'jobdata';
    $table_api = $wpdb->prefix . 'jobapi';

    // Query to retrieve job data
    $query = $wpdb->prepare(
        "SELECT data.jobID_json, data.jobID_check, data.jobID_check_status, data.jobID_final
        FROM $table_data AS data
        INNER JOIN $table_api AS api ON data.client_ip = api.client_ip 
        WHERE data.client_ip = %s 
        ORDER BY data.id DESC
        LIMIT 1",
        $client_ip
    );

    // Retrieve job data from the database
    $job_data = $wpdb->get_row($query, ARRAY_A);

    return $job_data;
}
