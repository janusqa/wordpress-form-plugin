<?php

add_shortcode('simple_contact_form', 'display_form');
add_action('rest_api_init', 'process_form_rest_api_endpoint'); // hook our function into wp as rest endpoint
add_action('init', 'create_post_type'); // create custom post type for the plugin
add_action('add_meta_boxes', 'create_meta_box');


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
        'post_type' => 'SCForm Submission',
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

    register_post_type('SCForm Submission', $args);
}

function create_meta_box()
{
    add_meta_box('simple_contact_form', 'SCForm Submission', 'display_submission', 'SCForm Submission');
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
