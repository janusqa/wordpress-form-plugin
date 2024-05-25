<?php
if (!defined('ABSPATH')) exit;

function get_plugin_options($name)
{
    return carbon_get_theme_option($name);
}

function IsNullOrEmptyString($str)
{
    return ($str === null || trim($str) === '');
}
