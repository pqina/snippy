<?php

namespace snippy;

class Tiny {

    public static function setup() {

        if (!\current_user_can('edit_posts') && !\current_user_can('edit_pages')) {
            return;
        }

        if (\get_user_option('rich_editing') !== 'true') {
            return;
        }

        add_filter('mce_external_plugins', array('snippy\Tiny', 'handle_mce_plugin'));
        add_filter('mce_buttons', array('snippy\Tiny', 'handle_mce_toolbar'));

    }

    public static function handle_mce_plugin($plugin_array) {
        $plugin_array['snippy'] = plugin_dir_url( __FILE__ ) . 'mce/index.js';
        return $plugin_array;
    }

    public static function handle_mce_toolbar($buttons) {

        $shortcodes = Data::get_entries_all('shortcodes');

        ?>
        <div id="snippy--mce-shortcode-popup" style="display:none;">
            <ul>
                <?php
                foreach($shortcodes as $shortcode) {

                    $name = $shortcode['name'];
                    $bits = Data::get_bits_for_shortcode_by_name($name);
                    $placeholders = array();

                    foreach ($bits as $bit) {
                        $placeholders = array_merge($placeholders, Utils::get_placeholders_from_bit($bit));
                    }

                    ?>
                    <li><a data-placeholders="<?php echo htmlentities(json_encode($placeholders, ENT_QUOTES)) ?>" href="#"><?php echo $name;?></a></li>
                    <?php
                }
                ?>
            </ul>
        </div>

        <?php


        array_push( $buttons, '|', 'snippy' );
        return $buttons;
    }

}