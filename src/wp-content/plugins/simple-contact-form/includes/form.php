<?php

add_shortcode('simple_contact_form', 'display_form');
add_action('rest_api_init', 'process_form_rest_api_endpoint'); // hook our function into wp as rest endpoint
add_action('init', 'create_post_type'); // create custom post type for the plugin
add_action('add_meta_boxes', 'create_meta_box');

// this allows us to put the fields from our data as coloumns on custome post type page
add_filter('manage_scformsubmission_posts_columns', 'create_custom_submission_columns');
// we need to add a priority to make this load after all other actions. We give it a priority of 10
// additional the callback accepts 2 args so we specify that as well
add_action('manage_scformsubmission_posts_custom_column', 'hydrate_custom_submission_columns', 10, 2);
// make post search work for all columns
add_action('admin_init', 'configure_search');

function display_form()
{
    include(SIMPLE_CONTACT_FORM_PLUGIN_PATH . '/includes/templates/form.php');
}

function process_form_rest_api_endpoint()
{
    register_rest_route('v1/simple-contact-form', 'process_form', array(
        'methods' => 'POST',
        'callback' => 'process_form'
    ));
}

function process_form(WP_REST_Request $request)
{

    if (!wp_verify_nonce($request->get_param('_wpnonce'), 'wp_rest')) {
        $response = new WP_REST_Response();
        $response->set_status(400);
        return $response;
    }

    $data = $request->get_body_params();
    unset($data['_wpnonce']);
    unset($data['_wp_http_referer']);

    $sender = array('email' => get_bloginfo('admin_email'), 'name' => get_bloginfo('name'));

    $headers = [];
    $headers[] = "From: {$sender['name']} <{$sender['email']}>";
    $headers[] = "Reply-to: {$data['name']} <{$data['email']}>";
    $headers[] = "Content-Type: text/html";

    $subject = "New enquiry from {$data['name']}";

    $message = "<h1>Message has been sent from {$data['name']}</h1>";

    $postarr = [
        'post_title' => $data['name'],
        'post_type' => 'scformsubmission',
        'post_status' => 'publish' // enter post and set as published. It now will not show as draft in the post types
    ];
    $post_id = wp_insert_post($postarr);

    foreach ($data as $key => $value) {
        $message .= "<strong>" . ucfirst($key) . ":</strong>&nbsp;" . $value . "<br/>";
        add_post_meta($post_id, $key, $value);
    }

    $response = new WP_REST_Response();
    $response->set_headers(array("Content-type" => "application/json"));
    try {
        wp_mail($sender['email'], $subject, $message, $headers);
        $response->set_data(array("message" => "Message Sent!"));
        $response->set_status(200);
    } catch (Exception $e) {
        $response->set_data(array("message" => "Something went wrong: {$e->getMessage()}"));
        $response->set_status(500);
    }
    return $response;
}

function create_post_type()
{
    $args = [
        'public' => true,
        'has_archive' => true,
        'labels' => [
            'name' => 'SCForm Submissions',
            'singular_name' => 'SCForm Submission'
        ],
        'capability_type' => 'post',
        'capabilities' => [
            'create_posts' => false // remove the add button on the custom post page
        ],
        'map_meta_cap' => true, // set to false, if users are not allowed to edit/delete existing posts
        'supports' => false

    ];

    register_post_type('scformsubmission', $args);
}

function create_meta_box()
{
    add_meta_box('simple_contact_form', 'SCForm Submission', 'display_submission', 'scformsubmission');
}

function display_submission()
{
    $post_meta = get_post_meta(get_the_ID());
    unset($post_meta['_edit_lock']);

    echo "<ul>";
    foreach ($post_meta as $key => $value) {
        echo "<li><strong>" . ucfirst($key) . ":</strong><br/>" . $value[0] . "</li>";
    }
    echo "</ul>";

    // example of hardcoding the meta (without looping)
    // echo "Name:" . get_post_meta(get_the_ID(), 'name', true);
}

// $columns is injected into this callback by the add_filter hook
function create_custom_submission_columns($columns)
{
    $columns = [
        'cb' => $columns['cb'],
        'name' => __('Name', 'simple-contact-form'),
        'email' => __('Email', 'simple-contact-form'),
        'phone' => __('Phone', 'simple-contact-form'),
        'message' => __('Message', 'simple-contact-form'),
    ];

    return $columns;
}

function hydrate_custom_submission_columns($column, $post_id)
{
    echo get_post_meta($post_id, $column, true);

    // switch ($column) {
    //     case 'name':
    //         echo get_post_meta($post_id, 'name', true);
    //          break;
    //      case 'email':
    //          echo get_post_meta($post_id,'email', true);
    //          break;
    //      case 'phone':
    //          echo get_post_meta($post_id,'phone', true);
    //          break;
    //      case 'message':
    //          echo get_post_meta($post_id,'message', true);
    //          break;
    // }
}

function configure_search()
{
    global $typenow;
    if ($typenow === 'scformsubmission') {
        add_filter('posts_search', 'scformsubmission_search_override', 10, 2);
    }
}

function scformsubmission_search_override($search, $query)
{
    // Override the submissions page search to include custom meta data

    global $wpdb;

    if ($query->is_main_query() && !empty($query->query['s'])) {
        $sql = "
                or exists (
                    select * from {$wpdb->postmeta} where post_id={$wpdb->posts}.ID
                    and meta_key in ('name','email','phone')
                    and meta_value like %s
                )
            ";
        $like   = '%' . $wpdb->esc_like($query->query['s']) . '%';
        $search = preg_replace(
            "#\({$wpdb->posts}.post_title LIKE [^)]+\)\K#",
            $wpdb->prepare($sql, $like),
            $search
        );
    }

    return $search;
}
