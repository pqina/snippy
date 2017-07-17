<?php
namespace snippy;

if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

require_once('includes/db.php');
require_once('includes/utils.php');

// delete resources
$bits = Data::get_entries_all('bits');

foreach ( $bits as $bit ) {

    if (Utils::get_bit_type($bit) !== 'resource') {
        continue;
    }

    $path = wp_upload_dir()['basedir'];
    $file = $path . $bit['value'];

    // delete file
    @unlink($file);
}

// clear database
Data::drop_db();