<?php
if(isset($_GET['request']) && isset($_GET['action']))
{
    if(!empty($_GET['request']) && !empty($_GET['action']))
    {
        $url_cid = urldecode($_GET['request']);
        $conference_id = base64_decode($url_cid);
        if(is_numeric($conference_id))
        {
            $action = $_GET['action'];
            require_once('db_connect.php');
            if($action == 'approve')
            {
                //Db update for Approval
                $update_query = "UPDATE conference_table SET approved = 1 WHERE conference_id = '{$conference_id}' and conference_end_time > '$cur_time' ";
                mysqli_query($con, $update_query);
                if(mysqli_affected_rows($con))
                {
                    header("Location: admin.php?message=Successfully%20Approved!"); 
                } else {
                    header("Location: admin.php?message=Confernce%20has%20been%20expired%20or%20already%20approved%20or%20Invalid!");
                }
            }
            else
            {
                if($action == 'disapprove')
                {
                    // Db update for disapproval
                    $update_query = "SELECT conference_id from conference_table WHERE conference_id = '{$conference_id}' AND approved != 2 and conference_end_time > '$cur_time' ";
                    $result = mysqli_query($con, $update_query);
                    
                    if(mysqli_num_rows($result) > 0)
                    {
                        
                        if(isset($_POST['comment']))
                        {
                            $comment = htmlspecialchars($_POST['comment']);
                            $update_query = "UPDATE conference_table SET approved = 2, comment = '{$comment}' WHERE conference_id = '{$conference_id}'";
                            mysqli_query($con, $update_query);
                            header("Location: admin.php?message=Successfully%20Declined!"); 
                            
                        }
                        else
                        {
                            echo "
                            <form method='post' action=''>
                            <p>Please provide the assocaitaed reason!</p>
                            <textarea required name='comment' rows=5 cols=40 placeholder='Your reason'></textarea>
                            <br>
                            <button style='padding:12px;color:red;margin:10px 0px' type='submit'>Decline</button>
                            </form>
                            ";
                        }
                    }
                    else
                    {
                        header("Location: admin.php?message=Confernce%20has%20been%20expired%20or%20already%20declined%20or%20Invalid!");
                    }
                }
                else
                {
                    header("Location: admin.php?message=Invalid%20request%20from%20email");
                }
            }
        }
    }
    else
    {
        header("Location: admin.php?message=Invalid%20request%20from%20email");
    }
}
else
{
    header("Location: admin.php?message=Invalid%20request%20from%20email");
}
?>