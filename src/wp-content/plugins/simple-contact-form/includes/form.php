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
    $data = array(
        'name' => $request->get_param('name'),
        'email' => $request->get_param('email'),
        'phone' => $request->get_param('phone'),
        'message' => $request->get_param('message'),
    );

    $response = new WP_REST_Response();
    $response->set_data($data);
    $response->set_headers(array('Content-type' => 'application/json'));
    $response->set_status(200);
    return $response;
}
