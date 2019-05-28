<?php

namespace snippy;

class Utils {

    static public function get_bit_format($bit) {
        if ($bit['type'] === 'stylesheet'){
            return 'css';
        }
        if ($bit['type'] === 'script') {
            return 'js';
        }
        return $bit['type'];
    }

    static public function get_bit_type($bit) {
        if (($bit['type'] === 'stylesheet' || $bit['type'] === 'script')) {
            return 'resource';
        }
        return 'text';
    }

    static public function is_remote($value) {
        return preg_match('/^https{0,1}:\/\//', $value);
    }

    static public function to_filename($value) {
        preg_match('/([^\/]*)$/', $value, $matches);
        return $matches[0];
    }

    static public function replace_placeholders($placeholders, $html) {
        foreach ($placeholders as $placeholder) {
            $name = $placeholder['name'];
            $value = $placeholder['value'];
            $html = preg_replace('/{{'. $name .'(?::.+?){0,1}}}/i', $value, $html);
        }
        return $html;
    }

    static public function merge_placeholders_and_atts($bit, $atts) {

        if ($atts == null) {
            $atts = array();
        }

        // get placeholders
        $placeholders = Utils::get_placeholders_from_bit($bit);
        $placeholders_merged = array();

        // replace attributes
        foreach ($placeholders as $placeholder) {

            $placeholder_name = $placeholder['name'];
            $placeholder_value = $placeholder['value'];

            foreach ($atts as $key => $value) {
                if ($key === $placeholder_name) {
                    if (\substr($value, 0, 7) ===  'snippy_') {
                        $placeholder_value = call_user_func('snippy\\Snippy::' . $value, explode(', ', $placeholder_value));
                    }
                    else {
                        $placeholder_value = $value;
                    }
                }
            }

            array_push($placeholders_merged, array(
                'name' => $placeholder_name,
                'value' => $placeholder_value
            ));
        }

        return $placeholders_merged;
    }

    static public function get_placeholders_from_bit($bit) {

        preg_match_all('/({{[a-z_]+(?::.+?){0,1}}})/i', html_entity_decode($bit['value']), $placeholders);

        $formatted_placeholders = array();

        if (isset($placeholders) && count($placeholders) > 0) {

            $tags = array();

            foreach ($placeholders[0] as $placeholder) {

                preg_match('/{{([a-z_]+)/i', $placeholder, $nameMatch);

                $name = preg_replace('/^{{/', '', $nameMatch[0]);
                if (in_array($name, $tags)) {
                    continue;
                }
                array_push($tags, $name);

                $value = '';
                if (stripos($placeholder, ':') > -1) {
                    preg_match('/:(.+)}}$/i', $placeholder, $valueMatch);
                    $value = substr(preg_replace('/}}$/','', $valueMatch[0]), 1);
                }

                array_push($formatted_placeholders, array('name' => $name, 'value' => $value));
            }

            return $formatted_placeholders;
        }
        else {
            // no placeholders found
            return array();
        }

    }

}