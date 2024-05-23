<?php

add_shortcode('simple_contact_form', 'display_form');
add_action('rest_api_init', 'process_form_rest_api_endpoint');

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
    foreach ($data as $key => $value) {
        $message .= "<strong>" . ucfirst($key) . ":</strong>&nbsp;" . $value . "<br/>";
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
