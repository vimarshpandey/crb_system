<?php
ob_start();
session_start();
require_once('db_connect.php');
// Retrieve the selected option from the dropdown
$selectedOption = '';
if (isset($_POST['options2']))
{
    $selectedOption = $_POST['options2'];
}

include_once 'mailer.php';

// Use the selected option as needed (e.g., perform further database operations, etc.)
echo "Selected option: " . $selectedOption;

$selectedOption1 = '';
if (isset($_POST['options1']))
{
    $selectedOption1 = $_POST['options1'];
}

// Use the selected option as needed (e.g., perform further database operations, etc.)
echo "Selected option: " . $selectedOption1;

$selectedOption2 = '';
if (isset($_POST['options3']))
{
    $selectedOption2 = $_POST['options3'];
}

// Use the selected option as needed (e.g., perform further database operations, etc.)
echo "Selected option: " . $selectedOption2;

if (isset($_POST['submit']))
{
    $branch_name = $_POST['options1'];
    $conference_date = $_POST['options6'];
    $conference_start_time = $_POST['options7'];
    $conference_end_time = $_POST['options8'];
    $conference_duration = $_POST['options9'];
    $room_name = $_POST['options2'];
    $department_name = $_POST['options3'];
    $entered_by = htmlspecialchars($_POST['options4']);
    $conference_purpose = htmlspecialchars($_POST['options5']);

    //$availabilityCheck = "SELECT * FROM conference_table WHERE room_name = '$room_name' AND conference_date = '$conference_date' AND conference_start_time <= '$conference_end_time' AND conference_end_time >= '$conference_start_time' AND approved = 1";
    $availabilityCheck = "SELECT * FROM conference_table WHERE room_name = '$room_name' AND conference_date = '$conference_date' AND conference_start_time <= '$conference_end_time' AND conference_end_time >= '$conference_start_time' AND (approved = 1 OR approved = 0)";
    $availabilityResult = mysqli_query($con, $availabilityCheck);

    if (mysqli_num_rows($availabilityResult) > 0)
    {
        // Room is already booked for the specified time interval
        $_SESSION['error_message'] = "The selected room is not available for the specified time interval.";
    }
    else
    {
        // Room is available, proceed with inserting the data into the database
        $sql = "INSERT INTO conference_table(branch_name, conference_date, conference_start_time, conference_end_time, conference_duration, room_name, department_name, entered_by, conference_purpose) VALUES ('$branch_name', '$conference_date', '$conference_start_time', '$conference_end_time', '$conference_duration', '$room_name', '$department_name', '$entered_by', '$conference_purpose')";

        if (mysqli_query($con, $sql))
        {
            $conference_id = mysqli_insert_id($con); // Get the last inserted conference_id

            // Check if approval is required for the selected room
            $approvalCheckQuery = "SELECT approval_required FROM room_details WHERE room_id = '$room_name'";
            $approvalCheckResult = mysqli_query($con, $approvalCheckQuery);

            if ($approvalCheckResult && mysqli_num_rows($approvalCheckResult) > 0)
            {
                $row = mysqli_fetch_assoc($approvalCheckResult);
                $approval_required = $row['approval_required'];

                if ($approval_required == 0)
                {
                    // Update the approved column to 1
                    $updateQuery = "UPDATE conference_table SET approved = 1 WHERE conference_id = $conference_id";
                    if (mysqli_query($con, $updateQuery))
                    {
                        $_SESSION['success_message'] = "Booking Successful";
                    }
                    else
                    {
                        $_SESSION['error_message'] = "Some Error Occurred...";
                    }
                }
                else if ($approval_required == 1)
                {
                    $roomQuery = "SELECT room_name FROM room_details WHERE room_id = '$room_name'";
                    $roomResult = mysqli_query($con, $roomQuery);
                    if ($roomResult && mysqli_num_rows($roomResult) > 0)
                    {
                        $roomRow = mysqli_fetch_assoc($roomResult);
                        $roomName = $roomRow['room_name'];
                    }
                    else
                    {
                        $roomName = 'N/A';
                    }

                    $departmentQuery = "SELECT department_name FROM department_details WHERE department_id = '$department_name'";
                    $departmentResult = mysqli_query($con, $departmentQuery);
                    if ($departmentResult && mysqli_num_rows($departmentResult) > 0)
                    {
                        $departmentRow = mysqli_fetch_assoc($departmentResult);
                        $departmentName = $departmentRow['department_name'];
                    }
                    else
                    {
                        $departmentName = 'N/A';
                    }

                    $branchQuery = "SELECT branch_name FROM branch_details WHERE branch_id = '$branch_name'";
                    $branchResult = mysqli_query($con, $branchQuery);
                    if ($branchResult && mysqli_num_rows($branchResult) > 0)
                    {
                        $branchRow = mysqli_fetch_assoc($branchResult);
                        $branchName = $branchRow['branch_name'];
                    }
                    else
                    {
                        $branchName = 'N/A';
                    }

                    try
                    {
                        $mail->Subject = "Conference Room Booking Approval for $roomName room.";
                        $mail->Body = "<h2>Booking Approval Request for room $roomName</h2><hr/><br>";
                        $mail->Body .= "<p>A conference room booking request has been issued.</p><br>";
                        $mail->Body .= "<i>Details:</i><ul>";
                        $mail->Body .= "<li>Branch: $branchName</li>";
                        $mail->Body .= "<li>Conference ID: $conference_id</li>";
                        $mail->Body .= "<li>Date: $conference_date</li>";
                        $mail->Body .= "<li>Start Time: $conference_start_time</li>";
                        $mail->Body .= "<li>End Time: $conference_end_time</li>";
                        $mail->Body .= "<li>Duration: $conference_duration</li>";
                        $mail->Body .= "<li>Room: $roomName</li>";
                        $mail->Body .= "<li>Department: $departmentName</li>";
                        $mail->Body .= "<li>Entered By: $entered_by</li>";
                        $mail->Body .= "<li>Purpose: $conference_purpose</li></ul>";

                        $encode_cid = urlencode(base64_encode($conference_id));
                        $homeURL = (empty($_SERVER['HTTPS']) ? 'http' : 'https') . '://' . $_SERVER['HTTP_HOST'];
                        $request_link = $homeURL . '/methods/emailActions.php?request=' . $encode_cid . '&action';
                        $mail->Body .= "<p><b>Actions</b><br><a href='$request_link=approve'><button style='padding:12px;color:green'>Approve </button></a> &nbsp;&nbsp;";
                        $mail->Body .= "<a href='$request_link=disapprove'><button style='padding:12px;color:red'> Decline</button></a> <br></p>";
                        $mail->Body .= "Please review and approve the booking as soon as possible.</p><br><br><p><img src='https://doctornitingarg.com/wp-content/uploads/2021/10/logo-dark.png' width='200px'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src='https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSsdX30k66dNI6GlHWgTfp8AMHy5_qWBffaGrT6LiirDMBDhF1j4nplMNW6eLblBPhxNQ' width='180px'></p>";
                        $mail->send();
                        // $mail->addAttachment('/var/tmp/file.tar.gz'); 
                        $_SESSION['success_message'] = "Mail Sent Successfully";
                    }
                    catch (Exception $e)
                    {
                        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                    }
                }
            }
        }
        else
        {
            $_SESSION['error_message'] = "Some Error Occurred...";
        }
    }

    header("Location: ../index.php"); // Replace "index.php" with the actual filename or URL of your main page
    exit();
}
?>