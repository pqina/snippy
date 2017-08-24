<?php

namespace snippy;

if(!class_exists('WP_List_Table')) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

require_once('db.php');

class Item_List_Table extends \WP_List_Table {

    private $table = null;
    private $columns = null;
    private $db_actions = null;

    function __construct($table, $columns, $db_actions, $labels)
    {
        $this->table = $table;

        $this->columns = $columns;

        $this->db_actions = $db_actions;

        parent::__construct(array(
            'singular' => $labels[0],
            'plural' => $labels[1],
        ));
    }

    function column_default($item, $column_name)
    {
        $itemValue = $item[$column_name];

        if (isset($this->columns[$column_name]['format'])) {
            $itemValue = $this->columns[$column_name]['format']($this, $item);
        }

        if (isset($this->columns[$column_name]['actions'])) {
            $actions = $this->columns[$column_name]['actions'];
            foreach ($actions as $key => $value) {

                $params = array(
                    $value['page'],
                    $item['id']
                );

                if ($key === 'delete') {
                    array_push($params, '&action=delete');
                }
                else {
                    array_push($params, '');
                }

                array_push($params, $value['label']);

                $actions[$key] = vsprintf('<a href="?page=%s&id=%s%s">%s</a>', $params);
            }

            return sprintf('%s %s',
                $itemValue,
                $this->row_actions($actions)
            );
        }

        return $itemValue;
    }

    function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="id[]" value="%s" />',
            $item['id']
        );
    }

    function get_columns()
    {
        $cols = array(
            'cb' => '<input type="checkbox" />' // Render a checkbox instead of text
        );

        foreach ($this->columns as $key => $value) {
            $cols[$key] = $value['label'];
        }

        return $cols;
    }

    function get_sortable_columns()
    {
        $cols = array();

        foreach ($this->columns as $key => $value) {
            $cols[$key] = array($key, $value['sortable']);
        }

        return $cols;
    }

    function get_bulk_actions()
    {
        $actions = array(
            'delete' => 'Delete'
        );
        return $actions;
    }

    function process_bulk_action()
    {
        if ('delete' === $this->current_action()) {
            $this->db_actions['delete']($this->table, $_REQUEST['id']);
        }
    }

    function prepare_items()
    {
        $per_page = 20;
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

        // here we configure table headers, defined in our methods
        $this->_column_headers = array($columns, $hidden, $sortable);

        // process bulk action if any
        $this->process_bulk_action();

        // will be used in pagination settings
        $total_items = Data::get_total_entries($this->table);

        // prepare query params, as usual current page, order by and order direction
        $paged = isset($_REQUEST['paged']) ? max(0, intval($_REQUEST['paged']) - 1) : 0;
        $orderby = (isset($_REQUEST['orderby']) && in_array($_REQUEST['orderby'], array_keys($this->get_sortable_columns()))) ? $_REQUEST['orderby'] : 'name';
        $order = (isset($_REQUEST['order']) && in_array($_REQUEST['order'], array('asc', 'desc'))) ? $_REQUEST['order'] : 'asc';

        // define $items array
        $this->items = Data::get_entries($this->table, $orderby, $order, $per_page, $paged);

        $this->set_pagination_args( array(
            'total_items' => $total_items, // total items defined above
            'per_page' => $per_page, // per page constant defined at top of method
            'total_pages' => ceil($total_items / $per_page) // calculate pages count
        ));
    }
}