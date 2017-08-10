<?php

namespace snippy;

require_once('view.php');

class Shortcodes_View {

    public static function handle_overview() {

        // create table for shortcodes
        $table = new Item_List_Table(
            'shortcodes',
            array(
                'name' => array(
                    'label' => \__('Name', 'snippy'),
                    'sortable' => true,
                    'actions' => array(
                        'edit' => array(
                            'page' => 'snippy_edit_shortcode',
                            'label' => \__('Edit', 'snippy')
                        ),
                        'delete' => array(
                            'page' => $_REQUEST['page'],
                            'label' => \__('Delete', 'snippy')
                        )
                    )
                )
            ),
            array(
                'delete' => function($table, $id) {
                    // delete shortcode
                    Data::delete_entries($table, $id);

                    // delete shortcode bit relations
                    Data::remove_bits_from_shortcode($id);
                }
            ),
            ['Shortcode', 'Shortcodes']
        );
        $table->prepare_items();

        View::draw_table($table, \__('Shortcodes', 'snippy'), 'snippy_edit_shortcode');

    }

    public static function handle_edit() {

        global $wpdb;
        $table_name = Data::get_table_name('shortcodes');

        $message = '';
        $notice = '';

        $default = array(
            'id' => 0,
            'name' => ''
        );

        // here we are verifying does this request is post back and have correct nonce
        if ( isset($_REQUEST['nonce']) && wp_verify_nonce($_REQUEST['nonce'], basename(__FILE__))) {

            // combine our default item with request params
            $item = shortcode_atts($default, $_REQUEST);

            // clean up name
            if (!empty($item['name'])) {
                $item['name'] = preg_replace('/[^a-z\-_]/', '', $item['name']);
            }

            // validate data, and if all ok save item to database
            // if id is zero insert otherwise update
            $item_valid = Shortcodes_View::validate_item($item);

            // bits are required
            if (!isset($_REQUEST['bits'])) {
                $item_valid = \__('You need to select a minimum of one bit', 'snippy');
            }

            if ($item_valid === true) {
                if ($item['id'] == 0) {

                    // create the entry
                    $result = $wpdb->insert($table_name, $item);
                    $item['id'] = $wpdb->insert_id;

                    // add the bits (will automatically remove any previous bits)
                    Data::add_bits_to_shortcode($_REQUEST['bits'], $item['id']);

                    if ($result) {
                        $message = \__('Item was successfully saved', 'snippy');
                    } else {
                        $notice = \__('There was an error while saving item', 'snippy');
                    }
                } else {

                    $result = $wpdb->update($table_name, $item, array('id' => $item['id']));

                    // add the bits (will automatically remove any previous bits)
                    Data::add_bits_to_shortcode($_REQUEST['bits'], $item['id']);

                    if ($result !== false) {
                        $message = \__('Item was successfully updated', 'snippy');
                    } else {
                        $notice = \__('There was an error while updating item', 'snippy');
                    }
                }
            } else {
                // if $item_valid not true it contains error message(s)
                $notice = $item_valid;
            }
        }
        else {
            // if this is not post back we load item to edit or give new one to create
            $item = $default;
            if (isset($_REQUEST['id'])) {
                $item = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $_REQUEST['id']), ARRAY_A);
                if (!$item) {
                    $item = $default;
                    $notice = \__('Item not found', 'snippy');
                }
            }
        }

        // here we adding our custom meta box
        \add_meta_box('shortcode_form_meta_box', 'Shortcode data', array( 'snippy\Shortcodes_View', 'handle_form_meta_box'), 'shortcode', 'normal', 'default');

        ?>
        <div class="wrap">
            <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
            <h2><?php \_e('Shortcode', 'snippy')?> <a class="add-new-h2"
                                                      href="<?php echo \get_admin_url(\get_current_blog_id(), 'admin.php?page=snippy');?>"><?php _e('Back to list', 'snippy')?></a>
            </h2>

            <?php if (!empty($notice)): ?>
                <div id="notice" class="error"><p><?php echo $notice ?></p></div>
            <?php endif;?>
            <?php if (!empty($message)): ?>
                <div id="message" class="updated"><p><?php echo $message ?></p></div>
            <?php endif;?>

            <form id="form" method="POST">
                <input type="hidden" name="nonce" value="<?php echo \wp_create_nonce(basename(__FILE__))?>"/>
                <?php /* NOTICE: here we storing id to determine will be item added or updated */ ?>
                <input type="hidden" name="id" value="<?php echo $item['id'] ?>"/>

                <div class="metabox-holder" id="poststuff">
                    <div id="post-body">
                        <div id="post-body-content">
                            <?php /* And here we call our custom meta box */ ?>
                            <?php \do_meta_boxes('shortcode', 'normal', $item); ?>
                            <input type="submit" value="<?php \_e('Save', 'snippy')?>" id="submit" class="button-primary" name="submit">
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <?php

    }

    public static function handle_form_meta_box($item) {

        // get bits for this shortcode
        $bits_available = Data::get_entries_all('bits');
        $bits_selected = Data::get_bits_for_shortcode($item['id']);

        ?>
        <table cellspacing="2" cellpadding="5" style="width: 100%;" class="form-table">
            <tbody>
            <tr class="form-field">
                <th valign="top" scope="row">
                    <label for="name"><?php _e('Name', 'snippy')?></label>
                </th>
                <td>
                    [ <input id="name"
                             name="name"
                             required pattern="[a-z\-_]+"
                             type="text"
                             value="<?php echo \esc_attr($item['name'])?>"
                             style="width:auto; min-width:10em"
                             size="10"
                             class="code"
                             placeholder="<?php \_e('shortcodename', 'snippy')?>" required> ]
                    <p><?php \_e('Allowed characters', 'snippy')?>: <code>a-z</code>, <code>_</code>, <code>-</code></p>
                </td>
            </tr>
            <tr class="form-field">
                <th valign="top" scope="row">
                    <?php \_e('Bits', 'snippy')?>
                </th>
                <td>
                    <fieldset>
                        <legend><?php \_e('Available bits', 'snippy')?></legend>
                        <table class="snippy--bit-table">
                            <?php
                            foreach ($bits_available as $bit) {
                                ?>
                                <tr>
                                    <td><input type="checkbox" <?php echo in_array($bit, $bits_selected) ? 'checked' : '' ?> name="bits[]" id="bit-<?php echo $bit['id']?>" value="<?php echo $bit['id'] ?>"></td>
                                    <th>
                                        <label for="bit-<?php echo $bit['id']?>"><?php echo $bit['name']; ?></label>
                                    </th>
                                    <td>
                                        <?php
                                        $bitFormat = Utils::get_bit_format($bit);
                                        echo '<span class="snippy--bit-format-' . $bitFormat . '">' . strtoupper($bitFormat) . '</span>';

                                        if (Utils::get_bit_type($bit) === 'resource') {
                                            echo '<span class="snippy--bit-type-resource ' . (Utils::is_remote($bit['value']) ? 'snippy--bit-resource-remote' : '') . '">' . Utils::to_filename($bit['value']) . '</span>';
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <?php
                            }
                            ?>
                        </table>
                    </fieldset>
                </td>
            </tr>
            </tbody>
        </table>
        <?php
    }

    public static function validate_item($item) {

        global $shortcode_tags;

        $shortcodes = array();
        foreach($shortcode_tags as $key => $value) {
            array_push($shortcodes, $key);
        }

        $messages = array();

        if (empty($item['name'])) {
            $messages[] = \__('Name is required', 'snippy');
        }

        else if (in_array($item['name'], $shortcodes)) {
            $messages[] = \__('This shortcode name is already in use', 'snippy');
        }

        else {
            $existing_codes = Data::get_entries_all('shortcodes');
            foreach ($existing_codes as $shortcode) {
                if ($item['id'] !== $shortcode['id'] && $shortcode['name'] === $item['name']) {
                    $messages[] = \__('This shortcode name is already in use', 'snippy');
                    break;
                }
            }
        }

        if (empty($messages)) return true;

        return implode('<br />', $messages);
    }


}