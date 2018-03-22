<?php
/*
Plugin Name: Snippy
Plugin URI: https://pqina.nl/snippy
Description: Snippy, create your own super flexible shortcodes
Version: 1.2.1
Author: PQINA
Author URI: https://pqina.nl
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: snippy

Copyright 2009-2018 PQINA

Snippy is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

Snippy is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Snippy. If not, see {URI to Plugin License}.
*/

namespace snippy;


function uninstall() {

    echo 'uninstall';

    error_log("uninstall uninstall uninstall uninstall", 0);

    if (!defined('WP_UNINSTALL_PLUGIN')) {
        die;
    }

    error_log("drop the tables", 0);

    Data::drop_db();

}

// Get dependencies
require_once('includes/db.php');
require_once('includes/utils.php');
require_once('includes/placeholders.php');


// Only required for admin
if ( is_admin() ) {
    require_once('includes/list.php');
    require_once('includes/tiny.php');
    require_once('includes/bits.php');
    require_once('includes/shortcodes.php');
}


// Class
class Snippy {

    // Snippy version
    public static $version = '1.2.0';

    private static $_instance = null;

    public static function get_instance() {

        if ( self::$_instance == null ) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    private function __construct() {

        \register_activation_hook( __FILE__, array( $this, 'install' ) );

        \register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

        \add_action( 'plugins_loaded', array($this, 'update' ) );

        \add_action( 'init', array( $this, 'init') );

        \add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts') );

        \add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_scripts') );

    }

    public function init()
    {

        if (\current_user_can('administrator')) {
            \add_action( 'admin_menu', array( $this, 'admin_menu') );
        }

        \load_plugin_textdomain('snippy', false, dirname(\plugin_basename(__FILE__)));

        if (is_admin()) {
            Tiny::setup();
        }
        else {
            $this->shortcodes();
        }

    }

    public function install() {

        Data::setup_db();

    }

    public function deactivate() {

        // flush cache/temp
        // flush permalinks

    }

    public function update() {

        Data::update_db();

    }

    public function admin_menu() {

        $svg = '
        <svg width="100%" height="100%" viewBox="0 0 20 20" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xml:space="preserve">
            
            <!-- left bracket -->
            <rect x="1" y="1" width="2" height="18" rx="2" ry="2" fill="#9ea3a8" />
            <rect x="2" y="1" width="3" height="2" rx="1" ry="1" fill="#9ea3a8" />
            <rect x="2" y="17" width="3" height="2" rx="1" ry="1" fill="#9ea3a8" />
            
            <!-- big circle -->
            <circle cx="7" cy="10" r="3.5" fill="#9ea3a8"/>
            
            <!-- small circle -->
            <circle cx="7.25" cy="17" r="2" fill="#9ea3a8"/>
            
            <!-- square -->
            <g transform="translate(9.5 7.5) rotate(45 3 3)">
              <rect x="3" y="3" width="6" height="6" rx="1" ry="1" fill="#9ea3a8" />
            </g>

            <!-- right bracket -->
            <rect x="17" y="1" width="2" height="18" rx="2" ry="2" fill="#9ea3a8" />
            <rect x="15" y="1" width="3" height="2" rx="1" ry="1" fill="#9ea3a8" />
            <rect x="15" y="17" width="3" height="2" rx="1" ry="1" fill="#9ea3a8" />
        </svg>
        ';
        $icon = 'data:image/svg+xml;base64,' . base64_encode($svg);

        \add_menu_page(
            \__('Snippy', 'snippy'),
            \__('Snippy', 'snippy'),
            'activate_plugins',
            'snippy',
            array( 'snippy\Shortcodes_View', 'handle_overview'),
            $icon,
            60
        );

        \add_submenu_page(
            'snippy',
            \__('Shortcodes', 'snippy'),
            \__('Shortcodes', 'snippy'),
            'activate_plugins',
            'snippy',
            array( 'snippy\Shortcodes_View', 'handle_overview')
        );

        \add_submenu_page(
            'snippy',
            \__('Add Shortcode', 'snippy'),
            \__('Add Shortcode', 'snippy'),
            'activate_plugins',
            'snippy_edit_shortcode',
            array( 'snippy\Shortcodes_View', 'handle_edit')
        );

        \add_submenu_page(
            'snippy',
            \__('Bits', 'snippy'),
            \__('Bits', 'snippy'),
            'activate_plugins',
            'snippy_bits',
            array( 'snippy\Bits_View', 'handle_overview')
        );

        \add_submenu_page(
            'snippy',
            \__('Add Bit', 'snippy'),
            \__('Add Bit', 'snippy'),
            'activate_plugins',
            'snippy_edit_bit',
            array( 'snippy\Bits_View', 'handle_edit')
        );

    }

    public function register_admin_scripts() {

        \wp_enqueue_style( 'snippy-admin-styles', \plugin_dir_url( __FILE__ ) . 'admin/css/style.css', array(), Snippy::$version );
        \wp_enqueue_script( 'snippy-admin-scripts', \plugin_dir_url( __FILE__ ) . 'admin/js/script.js', array(), Snippy::$version, true );

    }

    public function register_scripts() {

        $upload_url = \wp_upload_dir()['baseurl'];
        $bits = Data::get_entries_all('bits');
        foreach($bits as $bit) {

            $url = Utils::is_remote($bit['value']) ? $bit['value'] : $upload_url . $bit['value'];
            $name = $bit['name'];

            if ($bit['type'] === 'script') {
                \wp_register_script( $name, $url, array(), false, true );
            }
            else if ($bit['type'] === 'stylesheet') {
                \wp_register_style( $name, $url );
            }
        }

    }

    private function shortcodes() {

        $shortcode_entries = Data::get_entries_all('shortcodes');

        foreach ($shortcode_entries as $shortcode_entry) {
            \add_shortcode($shortcode_entry['name'], array( $this , 'handle_shortcode'));
        }
    }

    public function handle_shortcode($atts, $content, $tag) {

        $hasContent = strlen($content) > 0;

        // set base output
        $output = '';

        // get bits for shortcode with this id
        $bits = Data::get_bits_for_shortcode_by_name($tag);

        // use bits
        foreach ($bits as $bit) {

            $bit_name = $bit['name'];
            $bit_type = $bit['type'];
            $bit_value = $bit['value'];

            // if is script, enqueue
            if ($bit_type === 'script') {
                \wp_enqueue_script( $bit_name );
            }

            // if is stylesheet, enqueue
            else if ($bit_type === 'stylesheet') {
                \wp_enqueue_style( $bit_name );
            }

            // if is CSS wrap in <style> tags and prepend to output
            else if ($bit_type === 'css') {

                // replace placeholders in css value
                $css = html_entity_decode($bit_value);
                $placeholders_merged = Utils::merge_placeholders_and_atts($bit, $atts);
                $css = Utils::replace_placeholders($placeholders_merged, $css);

                $output .= "<style>$css</style>";
            }

            // if is HTML, add to output and replace placeholders with $atts
            else if ($bit_type === 'html') {

                // replace placeholders in html value
                $html = \do_shortcode(html_entity_decode($bit_value));
                $placeholders_merged = Utils::merge_placeholders_and_atts($bit, $atts);

                // if has content add content to placeholder
                if ($hasContent) {
                    $placeholders_merged = array_filter($placeholders_merged, function($placeholder) {
                        return $placeholder['name'] !== 'content';
                    });
                }
                // has content attr
                elseif (isset($atts['content'])) {
                    foreach ($placeholders_merged as &$placeholder) {
                        if ($placeholder['name'] === 'content') {
                            $placeholder['value'] = $atts['content'];
                        }
                    }
                }

                // replace placeholders
                $html = Utils::replace_placeholders($placeholders_merged, $html);

                // if has {{content}}, replace with do_shortcodes($content);
                if ($hasContent) {
                    $html = str_replace('{{content}}', \do_shortcode($content), $html);
                }

                // done
                $output .= $html;
            }

            // if is JS wrap in <script> tags and append to output
            else if ($bit_type === 'js') {

                // replace placeholders in js value
                $js = html_entity_decode($bit_value);
                $placeholders_merged = Utils::merge_placeholders_and_atts($bit, $atts);
                $js = Utils::replace_placeholders($placeholders_merged, $js);

                $output .= "<script>$js</script>";
            }


        }

        // render output
        return $output;

    }

}

// go!
Snippy::get_instance();