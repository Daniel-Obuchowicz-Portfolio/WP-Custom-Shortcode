<?php
defined('ABSPATH') || exit;

class ShortcodeSettings {
    private static $shortcode_name_option = 'custom_shortcode_name';
    private static $shortcode_content_option = 'custom_shortcode_content';

    public static function get_shortcode_name() {
        return get_option(self::$shortcode_name_option, 'custom_shortcode');
    }

    public static function get_shortcode_content() {
        return get_option(self::$shortcode_content_option, '');
    }
}
