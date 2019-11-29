<?php 

    $id = $_GET['candidate_id'];
    global $wpdb;

    if(!empty($_POST) && isset($_POST['update_candidate']))
    {
        $post_name = '';
        $post_exp = '';
        $post_phone = '';
        $post_email = ''; 
        $post_profile = '';

        foreach($_POST as $key=>$val)
        {

            switch($key)
            {
                case 'name':
                    $post_name  =   sanitize_text_field($_POST[$key]); 
                case 'months_of_exp':
                    $post_exp = (int) preg_replace("/[^0-9]/", "", sanitize_text_field($_POST[$key]));
                case 'phone_no':
                    $post_phone =  preg_replace("/[^0-9]/", "", sanitize_text_field($_POST[$key]));
                case 'email':
                    $post_email = sanitize_email($val);
                case 'profile':
                    $post_profile = sanitize_text_field($_POST[$key]);
                default: 
            }
        }

        $error_msg = '';

        if ($post_name == "" || $post_exp == "" || $post_phone == "" || $post_email == "" || $post_profile == "")
        {
            $error_msg .= "All fields are mandatory.\n";
        }

        if(!is_numeric($post_exp))
        {
            $error_msg .= "Experience input must be a numeric value.\n";
        }

        if(strlen($post_phone) < 10)
        {
            $error_msg .= "Invalid phone number input.\n";
        }

        if (!filter_var($post_email, FILTER_VALIDATE_EMAIL)) {

            $error_msg .= "Invalid email input.\n";
        }

        if($error_msg != '')
        {
            print_r($error_msg); die; 

        } else {

            $sql = "select * from applied_candidates";
            $result  = $wpdb->get_results($sql, 'ARRAY_A');
            $same_phone = '';
            $same_email = '';

            foreach($result as $key=>$val)
            {
                $same_phone = ($val['phone_no'] == $post_phone) ?true:false; 
                $same_email = ($val['email']    == $post_email)    ?true:false;
            }

            if($same_phone)
            {
                print_r("The number ".$post_phone." already exists in the database. Cannot update candidate."); die;

            } elseif($same_email){

                print_r("The email ".$post_email." already exists in the database. Cannot update candidate."); die;

            } else {

                $sql = "update applied_candidates set name='$post_name', months_of_exp='$post_exp', phone_no='$post_phone', email='$post_email', profile='$post_profile' where id='$id'";
                $wpdb->query($sql);
                $msg = "Candidate ".$post_name." has been successfully updated.";
                die($msg);
            }

        }

    }

    $sql = "select * from applied_candidates where id = '$id'";
    $result = $wpdb->get_results($sql, 'ARRAY_A');

    ?>
    <h1> Edit Information for Applied Candidate:  <strong><?php echo $result[0]['name'] ?></strong> </h1>

    <form method="POST">
    <strong>Full Name:</strong> </br></br>
                <input type="text" name="name" value="<?php echo $result[0]['name'] ?>" required></br></br>
            <strong>Number of Months of Experience:</strong> </br></br>
                <input type="number" name="months_of_exp" value="<?php echo $result[0]['months_of_exp'] ?>" required></br></br>
            <strong>Phone Number:</strong> </br></br>
                <input type="number" name="phone_no" value="<?php echo $result[0]['phone_no'] ?>" required></br></br>
            <strong>Email:</strong> </br></br>
            <input type="email" name="email" value="<?php echo $result[0]['email'] ?>" required></br></br>
            <strong>Profile:</strong> </br></br>
            <input type="text" name="profile" value="<?php echo $result[0]['profile'] ?>" required /></br></br>
            <input type="submit" value="Update Candidate Information" name="update_candidate">
    </form>
