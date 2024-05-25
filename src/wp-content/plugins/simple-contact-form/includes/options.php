<?php
if (!defined('ABSPATH')) exit;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

add_action('after_setup_theme', 'load_carbon_fields');
add_action('carbon_fields_register_fields', 'create_options_page');

function load_carbon_fields()
{
    \Carbon_Fields\Carbon_Fields::boot();
}

function create_options_page()
{
    Container::make('theme_options', __('SCForm Options'))
        ->set_icon('dashicons-media-text') // Wordpress Dash Icons
        ->add_fields(array(
            Field::make('checkbox', 'simple_contact_form_active', __('Activated')),

            Field::make('text', 'simple_contact_form_recipient', __('Recipient Email'))
                ->set_attribute('placeholder', 'eg. your@email.com')
                ->set_help_text('Enter email of the recipient for contact form'),

            Field::make('textarea', 'simple_contact_form_confirmation_message', __('Confirmation Message'))
                ->set_attribute('placeholder', 'Enter confirmation message sent on submission')
                ->set_help_text('Enter confirmation message sent on submission'),
        ));
}
