<?php
/*
*Plugin Name: Job Portal
*/


require_once( ABSPATH . 'wp-content/plugins/job-portal-plugin/posts/candidates.php');
require_once( ABSPATH . 'wp-content/plugins/job-portal-plugin/posts/jobs.php');



/***********************************
    CREATE CUSTOM POST TYPE
 ***********************************/
function job_board_custom_post_type()
{

    register_post_type('Job Board',
        array(
            'labels' => array(
                'name' => 'Job Board'
            ),
            'public' => true,
            'supports' => array('title', 'editor')
        )
    );

    // add_submenu_page("job-posts", 'Applied Candidates', 'Applied Candidates', 'manage_options', 'applied-candiates', 'wpl_owt_list_table_fnn');
    // add_submenu_page("job-posts", 'Applied Candidates', 'Applied Candidates', 'manage_options', 'applied-candiates', 'wpl_owt_list_table_fnn');
}
add_action('init', 'job_board_custom_post_type');







/*******************************************
    CREATE SHORTCODE FOR JOB POST RENDERING
 *******************************************/
function get_job_board_posts()
{
    $args = array(
        'post_type' => 'jobboard'
    );
    $posts = get_posts($args);
    $titles = '';

    foreach($posts as $key=>$val)
    {
        $titles .= '<a href="'.get_permalink($val->ID).'"><strong>'.$val->post_title.'</strong></a></br></br>';
    }

    return $titles; 
}
add_shortcode('get_job_board_posts', 'get_job_board_posts');







/***********************************************************
    CREATE SHORTCODE FOR CANDIDATE FORM RENDERING/ HANDLING
 **********************************************************/

function job_form_builder($atts, $content = null)
{

    extract(shortcode_atts(array(
        'title'=> 'Default Job Title'
    ), $atts));

    if ( ! empty( $_POST ) ) {

        foreach($_POST as $key=>$val)
        {

            switch($key)
            {
                case 'name':
                $_POST[$key]  =   preg_replace("/[^a-zA-Z]/", "", sanitize_text_field($_POST[$key])); 
                case 'months_of_exp':
                    $_POST[$key] = preg_replace("/[^0-9]/", "", sanitize_text_field($_POST[$key]));
                case 'phone_no':
                    $_POST[$key] = preg_replace("/[^0-9]/", "", sanitize_text_field($_POST[$key]));
                case 'email':
                    $_POST[$key] = sanitize_email($val);
                default: 
            }
        }

        $all_fields_present = true;
        $experience_validate = true; 
        $phone_validate = true; 
        $email_validate = true; 
        $resume_validate = true; 
        $error_msg = '';

        if ($_POST['name'] == "" || $_POST['months_of_exp'] == "" || $_POST['phone_no'] == "" || $_POST['email'] == "")
        {
            $all_fields_present = false;
            $error_msg .= "All fields are mandatory, resume attachment is optional.\n";
        }

        if(!is_numeric($_POST['months_of_exp']))
        {
            $experience_validate = false; 
            $erro_msg .= "Experience input must be a numeric value.\n";
        }

        if(count($_POST['phone_no']) < 10)
        {
            $phone_validate = false; 
            $error_msg .= "Invalid phone number input.\n";
        }

        if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $email_validate = false; 
            $error_msg .= "Invalid email input.\n";
        }

        if(!preg_match("/\.(doc|docx|pdf)$/", $_POST['resume']))
        {
            $resume_validate = false; 
            $error_msg .= "Resume must be of the following file type: PDF, DOC, DOCX.\n";
        }

        if($error_msg != '')
        {
            print_r($error_msg); die; 
        } else {

            global $wpdb; 
            $sql = "select * from applied_candidates";
            $result  = $wpdb->get_results($sql, 'ARRAY_A');

            foreach($result as $key=>$val)
            {
                $same_phone = ($val['phone_no'] == $_POST['phone_no']) ?true:false; 
                $same_email = ($val['email']    == $_POST['email'])    ?true:false;
            }

            if($same_phone)
            {
                print_r("The number ".$_POST['phone_no']." already exists in the database. Cannot process your application."); die;
            } elseif($same_email){
                print_r("The email ".$_POST['email']." already exists in the database. Cannot process your application."); die;
            } else {


                $sql = (isset($_POST['resume']))? "insert into applied_candidates(name, months_of_exp, phone_no, email, resume, profile) values ('".$_POST['name']."',".$_POST['months_of_exp'].",'".$_POST['phone_no']."','".$_POST['email']."','".$_POST['resume']."'".",'".$_POST['profile']."')" : "insert into applied_candidates(name, months_of_exp,phone_no, email, profile) values ('".$_POST['name']."',".$_POST['months_of_exp'].",'".$_POST['phone_no']."','".$_POST['email'].",'".$_POST['profile']."')";
                $wpdb->query($sql);
                $msg = "Thank you ".$_POST['name'].", your application has been successfully submitted.\nSee you at the interview!";
                die($msg);
            }

        }

    }

    $form_data = <<<EOT
    <h2> Candidate Form </h2>
    <p> Interested and eligible candidates need apply. </p> </br>
    <div id="candidate-form">
        <form method="post" >
            <strong>Full Name:</strong> </br></br>
                <input type="text" name="name" required></br></br>
            <strong>Number of Months of Experience:</strong> </br></br>
                <input type="number" name="months_of_exp" required></br></br>
            <strong>Phone Number:</strong> </br></br>
                <input type="number" name="phone_no" required></br></br>
            <strong>Email:</strong> </br></br>
            <input type="email" name="email" required></br></br>
            <strong>Resume (Accepted File Formats are PDF, DOC and DOCX):</strong> </br></br>
            <input type="file" name="resume" enctype="multipart/form-data" accept=".doc,.docx,.pdf" /></br></br>
            <input type="hidden" name="profile" value="$title" >
            <input type="submit" value="Submit">
        </form>
    </div>
EOT;

    return $form_data; 
}
add_shortcode('job_form', 'job_form_builder');