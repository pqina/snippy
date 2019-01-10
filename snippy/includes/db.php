<?php

namespace snippy;

// Inspired by
// https://github.com/pmbaldha/WP-Custom-List-Table-With-Database-Example/blob/master/custom-list-table-db-example.php

class Data {

    // database version, used for updating the database when necessary
    public static $version = '1.0.0';

    static public function get_table_name($name) {
        global $wpdb;
        return $wpdb->prefix . 'snippy_' . $name;
    }

    static public function run_structure_query($sql, $name) {
        \dbDelta(sprintf($sql, self::get_table_name($name)));
    }

    static public function run_simple_query($sql, $table, $params = []) {
        global $wpdb;

        $table = self::get_table_name($table);

        array_unshift($params, $table);

        return $wpdb->query( vsprintf($sql, $params) );
    }

    static public function setup_db() {

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        // create shortcodes table
        // id
        // name
        self::run_structure_query('CREATE TABLE %s (
          id int(11) NOT NULL AUTO_INCREMENT,
          name VARCHAR(100) NOT NULL,
          PRIMARY KEY  (id)
        );', 'shortcodes');

        // create bits table
        // id
        // name
        // value (url or text)
        // type (deferred from value)
        self::run_structure_query('CREATE TABLE %s (
          id int(11) NOT NULL AUTO_INCREMENT,
          name VARCHAR(100) NOT NULL,
          type VARCHAR(100) NOT NULL,
          value TEXT NOT NULL,
          PRIMARY KEY  (id)
        );', 'bits');

        // create shortcode_bits table
        // id
        // shortcode_id
        // bits_id
        self::run_structure_query('CREATE TABLE %s (
          id int(11) NOT NULL AUTO_INCREMENT,
          shortcode_id int(11) NOT NULL,
          bit_id int(11) NOT NULL,
          PRIMARY KEY  (id)
        );', 'shortcode_bits');


        // save current database version for later use (on update)
        \add_option("snippy_db_version", self::$version);

        // run db update logic
        $installed_ver = \get_option("snippy_db_version");
        if ($installed_ver != self::$version) {

            // update query here...

            // run query
            // dbDelta($sql);

            \update_option("snippy_db_version", self::$version);
        }
    }

    static public function update_db() {
        if (\get_site_option('flexcode_db_version') != self::$version) {
            self::setup_db();
        }
    }

    static public function drop_db() {
        global $wpdb;

        \delete_option('snippy_db_version');

        $table_name = Data::get_table_name('shortcodes');
        $wpdb->query("DROP TABLE IF EXISTS {$table_name}");

        $table_name = Data::get_table_name('bits');
        $wpdb->query("DROP TABLE IF EXISTS {$table_name}");

        $table_name = Data::get_table_name('shortcode_bits');
        $wpdb->query("DROP TABLE IF EXISTS {$table_name}");

    }

    static public function delete_entries($table, $ids) {
        $ids = isset($ids) ? $ids : array();
        if (is_array($ids)) $ids = implode(',', $ids);
        if (!empty($ids)) {
            self::run_simple_query('DELETE FROM %s WHERE id IN(%s)', $table, [$ids]);
        }
    }

    static public function get_total_entries($table) {
        global $wpdb;
        $table_name = self::get_table_name($table);
        return count($wpdb->get_results( "SELECT id FROM $table_name", ARRAY_A ));
    }

    static public function get_entries($table, $order_by, $order, $per_page, $paged) {
        global $wpdb;
        $table_name = self::get_table_name($table);
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table_name ORDER BY $order_by $order LIMIT %d OFFSET %d", $per_page, $paged * $per_page
            ),
            ARRAY_A
        );
    }

    static public function get_entry($table, $id) {
        global $wpdb;
        $table_name = self::get_table_name($table);
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id), ARRAY_A);
    }

    static public function get_entries_all($table) {
        global $wpdb;
        $table_name = self::get_table_name($table);
        return $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);
    }

    static public function delete_bit_resource($id) {
        $item = self::get_entry('bits', $id);
        if ($item['type'] === 'stylesheet' || $item['type'] === 'script') {
            self::delete_file($item['value']);
        }
    }

    static public function delete_file($file) {
        $upload_path = wp_upload_dir()['basedir'];
        @unlink( $upload_path . $file );
    }

    static public function delete_bit($id) {

        // get bit and see if should remove files
        self::delete_bit_resource($id);

        // delete bit entry
        self::delete_entries('bits', $id);

        // remove bit from shortcodes
        self::remove_bit_from_shortcodes($id);

    }

    static public function get_bits_for_shortcode($shortcode_id) {
        global $wpdb;
        $table_shortcode_bits = self::get_table_name('shortcode_bits');
        $table_bits = self::get_table_name('bits');
        $query = $wpdb->prepare("SELECT b.id, b.name, b.type, b.value FROM $table_bits AS b INNER JOIN $table_shortcode_bits AS sb ON b.id = sb.bit_id WHERE sb.shortcode_id = %d", $shortcode_id);
        return $wpdb->get_results($query, ARRAY_A);
    }

    static public function get_bits_for_shortcode_by_name($shortcode_name) {
        $shortcode = self::get_shortcode_by_name($shortcode_name);
        return self::get_bits_for_shortcode($shortcode['id']);
    }

    static public function get_shortcode_by_name($shortcode_name) {
        global $wpdb;
        $table_shortcodes = self::get_table_name('shortcodes');
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_shortcodes WHERE name = %s", $shortcode_name), ARRAY_A);
    }

    static public function add_bits_to_shortcode($bits, $shortcode_id) {

        global $wpdb;

        // clean up previously bound bits
        self::remove_bits_from_shortcode($shortcode_id);

        // prepare values
        $values = array();
        foreach ( $bits as $bit ) {
            $values[] = $wpdb->prepare( "(%d,%d)", $shortcode_id, $bit );
        }

        // insert into database
        $table_name = self::get_table_name('shortcode_bits');
        $query = "INSERT INTO $table_name (shortcode_id, bit_id) VALUES " . implode( ",\n", $values );
        $wpdb->query( $query );
    }

    static public function remove_bits_from_shortcode($shortcode_id) {

        global $wpdb;
        $table_name = self::get_table_name('shortcode_bits');

        // removes all bits bound to given shortcode
        $wpdb->query( $wpdb->prepare("DELETE  FROM $table_name WHERE shortcode_id = %d", $shortcode_id) );
    }

    static public function remove_bit_from_shortcodes($bit_id) {

        global $wpdb;
        $table_name = self::get_table_name('shortcode_bits');

        // removes all bits bound to given shortcode
        $wpdb->query( $wpdb->prepare("DELETE  FROM $table_name WHERE bit_id = %d", $bit_id) );
    }
}
