<?php

namespace snippy;

function toISODate($datetime) {
    return $datetime->format('Y-m-d');
}

class Placeholders
{
    static public function get_placeholder_value($name) {

        global $wp;
        global $post;

        /**
         * WordPress
         */

        // Returns the current page id
        if ($name === 'page_id') {
            return $post->ID;
        }

        // Returns the page absolute url
        if ($name === 'page_absolute_url') {
            return \home_url(\add_query_arg(array(), $wp->request));
        }

        // Returns the page relative url (excluding domain)
        if ($name === 'page_relative_url') {
            return \add_query_arg(array(), $wp->request);
        }

        // return name of the current theme
        if ($name === 'theme') {
            return \get_template();
        }

        // returns uri of the themes directory
        if ($name === 'theme_root_uri') {
            return \get_theme_root_uri();
        }

        // Returns the uri of the current theme
        if ($name === 'template_directory_uri') {
            return \get_template_directory_uri();
        }

        
        /**
         * PHP
         */

        // Returns a unique id
        if ($name === 'unique_id') {
            return \uniqid();
        }

        // Returns the date of today in ISO 8601 format
        if ($name === 'date_today') {
            return toISODate(new \DateTime('today'));
        }

        // Returns the date of tomorrow in ISO 8601 format
        if ($name === 'date_tomorrow') {
            return toISODate(new \DateTime('tomorrow'));
        }
        
        return '';
    }

}