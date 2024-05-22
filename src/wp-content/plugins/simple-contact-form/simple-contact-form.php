<?php

/**
 * Plugin Name: Simple Contact Form
 * Description: Simple Contact Form Plugin
 * Author: JanusQA
 * Author URI: blog.retrievo.net
 * Version: 1.0.0
 * Text Domain: simple-contact-form
 */

if (!defined('ABSPATH')) exit;

class SimpleContactForm
{

    public function __construct()
    {
        add_action('init', array($this, 'create_custom_post_type'));
    }

    public function create_custom_post_type()
    {
        $args = array(
            'public' => true,
            'has_archive' => true,
            'supports' => array('title'),
            'eclude_from_search' => true,
            'publicly_querable' => false,
            'capability' => 'manage_options',
            'labels' => array(
                'name' => 'Contact Form',
                'singular_name' => 'Contact Form Entry'
            ),
            'menu_icon' => 'dashicons-media-text' // Dashicons | Wordpress Developer Resources
        );

        register_post_type('simple_contact_form', $args);
    }
}

new SimpleContactForm;
