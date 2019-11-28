<?php  


if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/screen.php' );
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
    require_once( ABSPATH . 'wp-content/plugins/job-portal-plugin/posts/jobs.php');
}

if(!empty($_POST) && isset($_POST['delete_candidate']))
{
    // die(get_post()); 
    // print_r($_POST['hidden_info']); die; 
    $id = $_POST['hidden_info'];
    $sql = "delete from applied_candidates where id = '$id' ";
    global $wpdb;
    $result = $wpdb->query($sql);

}

add_action("admin_menu", "wpl_owt_list_table_menu_");
// add_action("admin_menu", "job_board_custom_post_type");

function wpl_owt_list_table_menu_()
{
    // add_menu_page("OWT Applied Candidates", "OWT Applied Candidates", "manage_options", "owt-applied-candidates", "wpl_owt_list_table_fnn");
    add_submenu_page("job-posts", 'Applied Candidates', 'Applied Candidates', 'manage_options', 'applied-candiates', 'wpl_owt_list_table_fnn');
}

function wpl_owt_list_table_fnn()
{
    $owt_table = new OWTTableListCandidate();
    $owt_table->prepare_items();
    $owt_table->display();
}

class OWTTableListCandidate extends WP_List_Table
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
            'name' => 'Name',
            'months_of_exp' => 'Months of Experience',
            'phone_no' => 'Phone Number',
            'email' => 'Email',
            'profile' => 'Profile'
        );

        return $columns;  
    }

    public function column_name($items)
    {
        $action = array(
            'delete' => sprintf('<form method="POST"><input type="hidden" value="%s" name="hidden_info"><input type="submit" value="Delete Candidate" name="delete_candidate"></form>', $items['id'], 'delete')
        );

        return sprintf('%s  %s', $items['name'], $this->row_actions($action));
    }

    public function column_default($item, $column_name)
    {
        switch($column_name)
        {

            case 'id'            :
            case 'name'          :
            case 'months_of_exp' :
            case 'phone_no'      :
            case 'email'         :
            case 'profile'       :
                return $item[$column_name];
            default: 
                return 'No Value';
        }
    }

    public function wp_list_table_data()
    {

        global $wpdb;
        $sql= "select * from applied_candidates";
        $result = $wpdb->get_results($sql, 'ARRAY_A');
        $data = array();

        foreach($result as $key=>$val)
        {
            $data[] = array(
                'id'            => $val['id'],
                'name'          => $val['name'],
                'months_of_exp' => $val['months_of_exp'],
                'phone_no'      => $val['phone_no'],
                'email'         => $val['email'],
                'profile'       => $val['profile']
            );
        }
        
        return $data; 

    }

}
?>