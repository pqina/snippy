<?php
namespace snippy;

if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

// need these files do clean up
require_once('includes/db.php');
require_once('includes/utils.php');

// delete resources found in bits
$bits = Data::get_entries_all('bits');
foreach ( $bits as $bit ) {
    if (Utils::get_bit_type($bit) !== 'resource') {
        continue;
    }
    $path = \wp_upload_dir()['basedir'];
    $file = $path . $bit['value'];
    @unlink($file);
}

// drop all created tables
Data::drop_db();