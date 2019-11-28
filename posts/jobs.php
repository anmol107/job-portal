<?php




add_action("admin_menu", "wpl_owt_list_table_menu");
// add_action("admin_menu", "job_board_custom_post_type");

function wpl_owt_list_table_menu()
{
    add_menu_page("Job Posts", "Job Posts", "manage_options", "job-posts", "wpl_owt_list_table_fn");
}

function wpl_owt_list_table_fn()
{
    $owt_table = new OWTTableList();
    $owt_table->prepare_items();
    $owt_table->display();
}

class OWTTableList extends WP_List_Table
{

    public function prepare_items()
    {
        $this->items = $this->wp_list_table_data();
        $columns = $this->get_columns();
        $this->_column_headers = array($columns);
    }



    public function get_columns()
    {
        $columns = array(
            'id' => 'ID',
            'post_title' => 'Job Title',
            'post_content' => 'Job Content'
        );

        return $columns;  
    }

    public function column_post_title($items)
    {
        $action = array(
            'edit' => sprintf('<a href="post.php?post=%s&action=%s">Edit</a>', $items['id'] ,'edit')
        );

        return sprintf('%s  %s', $items['post_title'], $this->row_actions($action));
    }
         
    
    public function column_default($item, $column_name)
    {
        switch($column_name)
        {
            case 'id':
            case 'post_title':
            case 'post_content':
                return $item[$column_name];
            default: 
                return 'No Value';
        }
    }

    public function wp_list_table_data()
    {
        $job_posts = get_posts(array(
            'post_type' => 'jobboard'
        ));

        $data = array();

        if(count($job_posts) > 0)
        {
            foreach($job_posts as $key=>$val)
            {
                $data[] = array(
                    'id' => $val->ID,
                    'post_title' => $val->post_title,
                    'post_content' => $val->post_content
                );
            }
        }

        return $data; 

    }

}
