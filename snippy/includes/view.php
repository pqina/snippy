<?php

namespace snippy;

class View {

    public static function draw_table($table, $title, $modify_page) {

        // create delete message
        $message = '';
        if ('delete' === $table->current_action()) {
            $message = '<div class="updated below-h2" id="message"><p>' . \sprintf(\__('Items deleted: %d', 'snippy'), \count($_REQUEST['id'])) . '</p></div>';
        }

        // render the table
        ?>
        <div class="wrap">

            <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
            <h2><?php echo $title ?> <a class="add-new-h2"
                                        href="<?php echo \get_admin_url(\get_current_blog_id(), 'admin.php?page=' . $modify_page);?>"><?php _e('Add new', 'snippy')?></a>
            </h2>

            <?php echo $message; ?>

            <form id="resources-table" method="GET">
                <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
                <?php $table->display() ?>
            </form>

        </div>
        <?php
    }

}