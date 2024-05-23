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

if (!class_exists('SimpleContactForm')) {

    class SimpleContactForm
    {

        public function __construct()
        {
            define('SIMPLE_CONTACT_FORM_PLUGIN_PATH', plugin_dir_path(__FILE__));
            require_once(SIMPLE_CONTACT_FORM_PLUGIN_PATH . '/vendor/autoload.php');
        }

        public function initialize()
        {
            include_once(SIMPLE_CONTACT_FORM_PLUGIN_PATH . '/includes/utilities.php');
            include_once(SIMPLE_CONTACT_FORM_PLUGIN_PATH . '/includes/options.php');
            include_once(SIMPLE_CONTACT_FORM_PLUGIN_PATH . '/includes/form.php');
        }
    }

    $simpleContactForm = new SimpleContactForm;
    $simpleContactForm->initialize();
}
