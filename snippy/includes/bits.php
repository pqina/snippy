<?php

namespace snippy;

if ( ! function_exists( 'wp_handle_upload' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/file.php' );
}

require_once('view.php');

class Bits_View {

    public static function handle_overview() {

        // create table for shortcodes
        $table = new Item_List_Table(
            'bits',
            array(
                'name' => array(
                    'label' => \__('Name', 'snippy'),
                    'sortable' => true,
                    'actions' => array(
                        'edit' => array(
                            'page' => 'snippy_edit_bit',
                            'label' => \__('Edit', 'snippy')
                        ),
                        'delete' => array(
                            'page' => $_REQUEST['page'],
                            'label' => \__('Delete', 'snippy')
                        )
                    )
                ),
                'type' => array(
                    'label' => \__('Type', 'snippy'),
                    'sortable' => true,
                    'format' => function($table, $item) {

                        $bitType = Utils::get_bit_type($item);
                        $bitFormat = Utils::get_bit_format($item);

                        $label = '';
                        if ($bitType === 'resource') {
                            $label = '<span class="snippy--bit-type-resource ' . (Utils::is_remote($item['value']) ? 'snippy--bit-resource-remote' : '') . '">' . Utils::to_filename($item['value']) . '</span>';
                        }

                        return '<span class="snippy--bit-format-' . $bitFormat . '">' . strtoupper($bitFormat) . '</span> ' . $label;
                    }
                )
            ),
            array(
                'delete' => function($table, $id) {
                    Data::delete_bit($id);
                }
            ),
            ['Bit', 'Bits']
        );
        $table->prepare_items();
        View::draw_table($table, \__('Bits', 'snippy'), 'snippy_edit_bit');

    }

    public static function validate_item($item) {
        $messages = array();


        if (empty($item['name'])) {
            $messages[] = \__('Name is required', 'snippy');
        }

        if (empty($item['type'])) {
            $messages[] = \__('Type is required', 'snippy');
        }
        else {
            $existing_bits = Data::get_entries_all('bits');
            foreach ($existing_bits as $bit) {
                if ($item['id'] !== $bit['id'] && $bit['name'] === $item['name']) {
                    $messages[] = \__('This bit name is already in use', 'snippy');
                    break;
                }
            }
        }

        if (empty($messages)) return true;
        return implode('<br />', $messages);
    }

    public static function handle_edit() {

        global $wpdb;
        $table_name = Data::get_table_name('bits');

        $message = '';
        $notice = '';

        $default = array(
            'id' => 0,
            'name' => '',
            'type' => '',
            'value' => ''
        );

        // here we are verifying does this request is post back and have correct nonce
        if ( isset($_REQUEST['nonce']) && \wp_verify_nonce($_REQUEST['nonce'], basename(__FILE__))) {

            // combine our default item with request params
            $item = \shortcode_atts($default, $_REQUEST);

            // by default the item is valid
            $item_valid = true;

            // previous value
            //$item_previous_value = $_REQUEST['previous_value'];

            // if a file is posted
            if ($_REQUEST['type'] === 'resource') {

                // uploading new file or overwriting file
                if ($_REQUEST['resource-location'] === 'local') {

                    // if sent a file
                    if (isset($_FILES['resource']) &&
                        $_FILES['resource']['size'] > 0) {

                        $uploaded_file = $_FILES['resource'];
                        $uploaded_filename = $uploaded_file['name'];
                        $uploaded_file_extension = strtolower(pathinfo($uploaded_filename, PATHINFO_EXTENSION));

                        $allowed_extensions = ['css', 'js'];

                        if (!in_array($uploaded_file_extension, $allowed_extensions)) {
                            $item_valid = \__('The extension is not valid, only CSS and JS files are allowed.', 'snippy');
                        }
                        else {
                            
                            $move_result = \wp_handle_upload( $uploaded_file, array( 'test_form' => false ) );

                            if ( $move_result && ! isset( $move_result['error'] ) ) {

                                $path = str_replace( \wp_upload_dir()['basedir'], '', $move_result['file'] );

                                // set type to the extension of the uploaded file
                                $item['type'] = $uploaded_file_extension === 'js' ? 'script' : 'stylesheet';
                                $item['value'] = $path;

                            } else {
                                $item_valid = \__('An error was thrown while trying to upload the resource', 'snippy') . ': "' . $move_result['error'] . '"';
                            }
                        }
                    }
                    // updating existing file meta data
                    else {

                        // type can remain the same
                        // value can remain the same
                        // only name could be changed

                        $item['type'] = strtolower(pathinfo($item['value'], PATHINFO_EXTENSION)) === 'js' ? 'script' : 'stylesheet';

                    }

                }
                else if ($_REQUEST['resource-location'] === 'remote') {
                    $item['value'] = $_REQUEST['resource-remote'];
                    $item['type'] = strtolower(pathinfo($item['value'], PATHINFO_EXTENSION)) === 'js' ? 'script' : 'stylesheet';
                }

            }
            else {

                // clean up value
                if (isset($_REQUEST['value'])) {
                    $item['value'] = htmlentities(stripslashes($_REQUEST['value']));
                }

            }


            if ($item_valid === true) {
                $item_valid = Bits_view::validate_item($item);
            }


            if ($item_valid === true) {

                // new item
                if ($item['id'] == 0) {

                    // add to db
                    $result = $wpdb->insert($table_name, $item);
                    $item['id'] = $wpdb->insert_id;

                    if ($result) {
                        $message = \__('Item was successfully saved', 'snippy');
                    }
                    else {
                        $notice = \__('There was an error while saving item', 'snippy');
                    }

                }

                // existing item
                else {

                    // get current value
                    $bit = Data::get_entry('bits', $item['id']);

                    // remove previous file if set
                    if ($bit['value'] !== $item['value']) {
                        Data::delete_bit_resource($item['id']);
                    }

                    // update data
                    $result = $wpdb->update($table_name, $item, array('id' => $item['id']));

                    if ($result === false) {
                        $notice = \__('There was an error while updating item', 'snippy');
                    }
                    else {
                        $message = \__('Item was successfully updated', 'snippy');
                    }
                }
            }
            else {
                // if $item_valid not true it contains error message(s)
                $notice = $item_valid;
            }
        }

        // load item
        else {

            // if this is not post back we load item to edit or give new one to create
            $item = $default;
            if (isset($_REQUEST['id'])) {
                $item = Data::get_entry('bits', $_REQUEST['id']);
                if (!$item) {
                    $item = $default;
                    $notice = \__('Item not found', 'snippy');
                }
            }

        }

        // here we adding our custom meta box
        \add_meta_box('bits_form_meta_box', 'Bits data', array('snippy\Bits_view', 'handle_form_meta_box'), 'bit', 'normal', 'default');

        ?>
        <div class="wrap">
            <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
            <h2><?php \_e('Bit', 'snippy')?> <a class="add-new-h2"
                                                    href="<?php echo \get_admin_url(\get_current_blog_id(), 'admin.php?page=snippy_bits');?>"><?php _e('back to list', 'snippy')?></a>
            </h2>

            <?php if (!empty($notice)): ?>
                <div id="notice" class="error"><p><?php echo $notice ?></p></div>
            <?php endif;?>
            <?php if (!empty($message)): ?>
                <div id="message" class="updated"><p><?php echo $message ?></p></div>
            <?php endif;?>

            <form id="form" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="nonce" value="<?php echo \wp_create_nonce(basename(__FILE__))?>"/>
                <?php /* NOTICE: here we storing id to determine will be item added or updated */ ?>
                <input type="hidden" name="id" value="<?php echo $item['id'] ?>"/>

                <div class="metabox-holder" id="poststuff">
                    <div id="post-body">
                        <div id="post-body-content">
                            <?php /* And here we call our custom meta box */ ?>
                            <?php \do_meta_boxes('bit', 'normal', $item); ?>
                            <input type="submit" value="<?php \_e('Save', 'snippy')?>" id="submit" class="button-primary" name="submit">
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <?php

    }

    public static function handle_form_meta_box($item) {

        $isText = true;
        $isNew = $item['id'] === 0;

        if ($item['type'] === 'stylesheet' || $item['type'] === 'script') {
            $isText = false;
        }

        ?>

        <script src="<?php echo \plugin_dir_url( __FILE__ ) ?>../admin/js/ace/ace.js"></script>
        <script src="<?php echo \plugin_dir_url( __FILE__ ) ?>../admin/js/ace/theme-chrome.js" type="text/javascript" charset="utf-8"></script>

        <input type="hidden" name="previous_value" value="<?php echo \esc_attr(html_entity_decode($item['value'])) ?>">

        <table cellspacing="2" cellpadding="5" style="width: 100%;" class="form-table">
            <tbody>
            <tr class="form-field">
                <th valign="top" scope="row">
                    <label for="name"><?php \_e('Name', 'snippy')?></label>
                </th>
                <td>
                    <input id="name" name="name" type="text" style="width: 95%" value="<?php echo \esc_attr($item['name'])?>"
                           size="50" class="code" placeholder="<?php \_e('Name', 'snippy')?>" required>
                </td>
            </tr>
            <tr class="form-field">
                <th valign="top" scope="row">
                    <label for="type"><?php \_e('Type', 'snippy')?></label>
                </th>
                <td>
                    <fieldset class="snippy--type-toggle snippy--bit-type-toggle">
                        <legend><?php \_e('Type', 'snippy')?></legend>
                        <ul>
                            <li><label><input type="radio" name="type" value="html" <?php echo $item['type'] === 'html' || $isNew ? 'checked' : '' ?>> <span class="snippy--bit-format-html">HTML</span></label></li>
                            <li><label><input type="radio" name="type" value="css" <?php echo $item['type'] === 'css' ? 'checked' : '' ?>> <span class="snippy--bit-format-css">CSS</span></label></li>
                            <li><label><input type="radio" name="type" value="js" <?php echo $item['type'] === 'js' ? 'checked' : '' ?>> <span class="snippy--bit-format-js">JS</span></label></li>
                            <li><label><input type="radio" name="type" value="resource" <?php echo $item['type'] === 'script' || $item['type'] === 'stylesheet' ? 'checked' : '' ?>> <span class="snippy--bit-type-resource"><?php \_e('Resource', 'snippy')?></span></label></li>
                        </ul>
                        <script>
                            (function(){

                                document.querySelector('.snippy--bit-type-toggle').addEventListener('change', function(e) {
                                    if (e.target.value === 'resource') {
                                        Snippy.Bits.showFileField(<?php echo $isText ? 'true' : 'false' ?>);
                                    }
                                    else {
                                        Snippy.Bits.showTextField(<?php echo $isText ? 'true' : 'false' ?>);
                                        Snippy.Bits.setEditorFormat(e.target.value);
                                    }
                                });

                            }());
                        </script>
                    </fieldset>
                </td>
            </tr>
            <tr class="form-field snippy--bit-text-field" style="display:<?php echo $isText ? '': 'none' ?>">
                <th valign="top" scope="row">
                    <label for="snippy--bit-editor" style="display:none"><?php \_e('Value', 'snippy')?></label>
                </th>
                <td>
                    <div id="snippy--bit-editor"></div>
                    <textarea name="value" id="snippy--bit-editor-textarea" cols="30" rows="10" style="display:none;"><?php echo \esc_textarea(html_entity_decode($item['value']))?></textarea>
                </td>
            </tr>
            <tr class="form-field snippy--bit-text-field" style="display:<?php echo $isText ? '': 'none' ?>">
                <th valign="top" scope="row">
                    <label><?php \_e('Placeholders', 'snippy')?></label>
                    <div><small><a style="font-weight:normal;" href="#snippy--bit-placeholder-info"><?php _e('More information', 'snippy')?></a></small></div>
                </th>
                <td>
                    <ul class="snippy--bit-placeholders" data-empty="<?php \_e('No placeholders defined', 'snippy')?>"><li></li></ul>

                    <div id="snippy--bit-placeholder-info">

                        <p><?php \_e('Use placeholders to create dynamic areas in text snippets.', 'snippy')?></p>

                        <ol>
                            <li>
                                <p><?php \_e('Placeholders are described with double curly brackets <code>{{</code> and <code>}}</code>', 'snippy')?></p>
                                <pre><code>{{age}}</code></pre>
                                <p><?php \_e('You can now use the placeholder in a Snippy shortcode.', 'snippy')?></p>
                                <pre><code>[person age=18]</code></pre>
                                <p><?php \_e('Note that you can only use lowercase alphabetical characters in placeholders.', 'snippy')?></p>
                            </li>
                            <li>
                                <p><?php \_e('Placeholders can have default values, you can supply them by adding a semicolon after the placeholder name.', 'snippy')?></p>
                                <pre><code>{{age:0}}</code></pre>
                                <pre><code>{{name:John Doe}}</code></pre>
                            </li>
                            <li>
                                <p><?php \_e('The <code>{{content}}</code> placeholder is reserved for content wrapped by the Snippy shortcode and will automatically be replaced.', 'snippy')?></p>
                                <pre><code>[person]John Doe[/person]</code></pre>
                            </li>
                            <li>
                                <p><?php \_e('The following list of placeholders can be used to access dynamic page data.', 'snippy')?></p>
                                <pre><code>{{bit_id}}</code></pre>
                                <pre><code>{{shortcode_id}}</code></pre>
                                <pre><code>{{page_id}}</code></pre>
                                <pre><code>{{page_absolute_url}}</code></pre>
                                <pre><code>{{page_relative_url}}</code></pre>
                                <pre><code>{{unique_id}}</code></pre>
                                <pre><code>{{date_today}}</code></pre>
                                <pre><code>{{date_tomorrow}}</code></pre>
                                <pre><code>{{admin_url}}</code></pre>
                                <pre><code>{{nonce_field:action,name}}</code></pre>
                            </li>
                        </ol>

                    </div>
                </td>
            </tr>
            <tr class="form-field snippy--bit-resource-field" style="display:<?php echo $isText ? 'none': '' ?>">
                <th valign="top" scope="row">
                    <label for="resource"><?php \_e('Resource', 'snippy')?></label>
                </th>
                <td>
                    <?php
                    $is_remote_resource = Utils::is_remote($item['value']);
                    ?>
                    <fieldset class="snippy--type-toggle snippy--resource-typ-toggle">
                        <legend><?php \_e('Resource location', 'snippy')?></legend>
                        <ul>
                            <li><label><input type="radio" name="resource-location" value="local" <?php echo $is_remote_resource ? '' : 'checked' ?>> Local</label></li>
                            <li><label><input type="radio" name="resource-location" value="remote" <?php echo $is_remote_resource ? 'checked' :'' ?>> Remote</label></li>
                        </ul>
                        <script>
                          (function(){

                            document.querySelector('.snippy--resource-typ-toggle').addEventListener('change', function(e) {
                              if (e.target.value === 'local') {
                                Snippy.Bits.showLocalField();
                              }
                              else {
                                Snippy.Bits.showRemoteField();
                              }
                            });

                          }());
                        </script>
                    </fieldset>

                    <div class="snippy--resource-type-local" <?php echo $is_remote_resource ? 'style="display:none"' : '' ?>>
                        <?php if (!$isText && !$is_remote_resource) {?>
                        "<span class="snippy--bit-resource-original"><?php echo $item['value']; ?></span>"
                        <p>
                            Upload a new file: <input id="resource" name="resource" type="file">
                        </p>
                        <?php } else { ?>
                        <input id="resource" name="resource" type="file" style="width: 95%">
                        <?php } ?>
                    </div>

                    <div class="snippy--resource-type-remote" <?php echo $is_remote_resource ? '' : 'style="display:none"' ?>>
                        <input id="resource-remote" name="resource-remote" type="text" value="<?php echo $is_remote_resource ? $item['value'] : '' ?>" style="width: 95%">
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
        <?php
    }

}