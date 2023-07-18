<?php
require_once('db_connect.php');
session_start();

if(isset($_POST['update_admin_email']))
{
    
    $new_email = $_POST['receiver_email'];
    if($new_email == $_SESSION['admin_email'])
    {
        echo "<script>alert('Email Already Exists!')</script>";
    }
    else
    {
        $query = "UPDATE users SET email = '$new_email' WHERE is_admin = 1";
        mysqli_query($con, $query);
        $_SESSION['admin_email'] = $new_email;
        echo "<script>alert('Email Updated Successfully')</script>";
    }
}

if (isset($_SESSION['username']))
{
    
    $query = "SELECT ct.conference_id, bd.branch_name, ct.approved, ct.conference_date, ct.conference_start_time, ct.conference_end_time, ct.conference_duration, rd.room_name, dd.department_name, ct.entered_by, ct.conference_purpose, ct.entered_date, ct.comment
                FROM conference_table AS ct
                INNER JOIN room_details AS rd ON rd.room_id = ct.room_name
                INNER JOIN department_details AS dd ON dd.department_id = ct.department_name
                INNER JOIN branch_details AS bd ON bd.branch_id = ct.branch_name
                WHERE DATE(ct.conference_date) > CURDATE() OR (DATE(ct.conference_date) = CURDATE() AND TIME(ct.conference_end_time) > CURTIME())";
    
    $result = mysqli_query($con, $query);
    $numRows = mysqli_num_rows($result);
}
else
{

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["username"]) && isset($_POST["password"]))
    {
        $username = htmlspecialchars($_POST["username"]);
        $key = $_POST["password"];
        $query = "SELECT user_name, password, email from users where user_name = '{$username}' ";
        $output = mysqli_query($con, $query);
        $_POST = array();

        if (mysqli_num_rows($output) > 0)
        {
            $row = mysqli_fetch_assoc($output);
            $hashedkey = $row['password'];
            if(password_verify($key, $hashedkey))
            {
                $_SESSION['username'] = $username;
                $_SESSION['admin_email'] = $row['email'];
                echo "<script>window.location.reload();</script>";
            }
            else
            {
                echo '<script>alert("Password is Wrong."); </script>';
            }
        }
        else
        {
            echo '<script>alert("No user Found."); </script>';
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["approve"]) && isset($_POST["conference_id"]))
{
    $conference_id = htmlspecialchars($_POST["conference_id"]);
    $update_query = "UPDATE conference_table SET approved = 1 WHERE conference_id = '{$conference_id}'";
    mysqli_query($con, $update_query);
    header('Location: admin.php');
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["decline"]) && isset($_POST["conference_id"]) && isset($_POST["comment"]))
{
    $conference_id = htmlspecialchars($_POST["conference_id"]);
    $comment = htmlspecialchars($_POST["comment"]);
    $update_query = "UPDATE conference_table SET approved = 2, comment = '{$comment}' WHERE conference_id = '{$conference_id}'";
    mysqli_query($con, $update_query);
    header('Location: admin.php');

}

$roomQuery = "SELECT room_name FROM room_details";
$roomResult = mysqli_query($con, $roomQuery);
$rooms = array();
while ($roomRow = mysqli_fetch_assoc($roomResult))
{
    $rooms[] = $roomRow['room_name'];
}

// Retrieve department names
$departmentQuery = "SELECT department_name FROM department_details";
$departmentResult = mysqli_query($con, $departmentQuery);
$departments = array();
while ($departmentRow = mysqli_fetch_assoc($departmentResult))
{
    $departments[] = $departmentRow['department_name'];
}

if (isset($_POST["search-btn"]))
{
    // Retrieve the search values
    $searchConferenceId = $_POST["search-conference-id"];
    $searchApprovalStatus = $_POST["search-approval-status"];
    $searchConferenceDate = $_POST["search-conference-date"];
    $searchRoomName = $_POST["search-room-name"];
    $searchDepartment = $_POST["search-department"];

    // Append the search conditions to the query based on the provided input
    $searchQuery = '';
    if (!empty($searchConferenceId))
    {
        $searchQuery .= " AND ct.conference_id = '{$searchConferenceId}'";
    }
    if (!empty($searchApprovalStatus))
    {
        $searchQuery .= " AND ct.approved = '{$searchApprovalStatus}'";
    }
    if (!empty($searchConferenceDate))
    {
        $searchQuery .= " AND ct.conference_date = '{$searchConferenceDate}'";
    }
    if (!empty($searchRoomName))
    {
        $searchQuery .= " AND rd.room_name = '{$searchRoomName}'";
    }
    if (!empty($searchDepartment))
    {
        $searchQuery .= " AND dd.department_name = '{$searchDepartment}'";
    }

    $searchQuery = "SELECT ct.conference_id, bd.branch_name, ct.approved, ct.conference_date, ct.conference_start_time, ct.conference_end_time, ct.conference_duration, rd.room_name, dd.department_name, ct.entered_by, ct.conference_purpose, ct.entered_date, ct.comment
                    FROM conference_table AS ct
                    INNER JOIN room_details AS rd ON rd.room_id = ct.room_name
                    INNER JOIN department_details AS dd ON dd.department_id = ct.department_name
                    INNER JOIN branch_details AS bd ON bd.branch_id = ct.branch_name
                    WHERE (DATE(ct.conference_date) > CURDATE() OR (DATE(ct.conference_date) = CURDATE() AND TIME(ct.conference_end_time) > CURTIME()))
                    $searchQuery
                    ORDER BY ct.conference_id DESC LIMIT 0,1000";

    // Execute the search query
    $result = mysqli_query($con, $searchQuery);
    $numRows = mysqli_num_rows($result);
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="refresh" content="180">
    <link rel="stylesheet" href="../css/bootstrap4.min.css">
    <script src="../js/sweetalert.min.js"></script>
    <link rel="stylesheet" href="../css/sweetalert.min.css">
    <script src="../js/jquery.min.js"></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@200;300;400;600&family=Playfair+Display&family=Poppins:wght@200;300;400;500;600;700;800&display=swap');
        body
        {
            font-family: 'Poppins', sans-serif;
        }
        .custom-border
        {
        border-bottom: 2px solid black;
        }
    </style>
</head>
<body class="bg-dark">
    <?php if(isset($_SESSION['username'])): ?>
        <div class="container-fluid">
            <div class="card">
                <div class="card-body">
                    <div class="row pt-3">
                        <div class="col-4">
                            <div class="text-danger h1 custom-border font-weight-bold">ADMIN PORTAL</div><br>
                            <span class="h6 font-weight-bold">Receiver's Mail Id: &nbsp;&nbsp;&nbsp;</span><?php echo $_SESSION['admin_email']; ?>
                        </div>
                        
                        <div class="col-8 pt-3"><br><br>
                            <a href="room.php" type="button" class="btn btn-primary">Room Details</a>
                            <a href="department.php" type="button" class="btn btn-secondary">Dept. or Branch Details</a>
                            <a href="password.php" type="button" class="btn btn-primary">Change Password</a>
                            <a href="past_booking.php" type="button" class="btn btn-secondary">Past Bookings</a>
                            <button type="button" class="btn btn-danger" onclick="confirmLogout()">Logout</button>
                            <details>
                                <summary type="button" class="btn btn-warning mt-3">Delegate Mail Id</summary><br><br><ul class="h6"><li>Only change the mail Id when admin changes or admin is on leave.</li><br><li>Approval for the booking will be send to entered mail Id.</li></ul><br>
                                <form class="w-50" method="post" action="">
                                    <label for="receiver_email" class="h6 font-weight-bold">Receiver's Mail Id:</label>
                                    <div class="form-group">
                                        <input type="email" class="form-control" value="<?php echo $_SESSION['admin_email'] ?>" id="receiver_email" name="receiver_email" required>
                                    </div>
                                    <input type="submit" class="btn btn-success" name="update_admin_email" value="Submit">
                                </form><br>
                            </details>
                            <script>
                                function confirmLogout()
                                {
                                    Swal.fire({
                                        title: 'Logout',
                                        text: 'Are you sure you want to logout?',
                                        icon: 'warning',
                                        showCancelButton: true,
                                        confirmButtonText: 'Yes',
                                        cancelButtonText: 'Cancel',
                                        reverseButtons: true
                                    }).then((result) => {
                                        if (result.isConfirmed)
                                        {
                                            window.location.href = 'logout.php';
                                        }
                                    });
                                }
                            </script>
                        </div>
                    </div>
                </div>
            </div>
            <h1 class="text-success text-center pb-3 pt-3 font-weight-bold">Details of Upcoming Bookings</h1>
            <form method="POST" action="">
                <div class="row">
                    <div class="col-2">
                        <div class="form-group mr-2">
                            <label for="search-conference-id" class="form-label text-primary mr-2">Conference ID:</label>
                            <input type="text" name="search-conference-id" id="search-conference-id" class="form-control mr-2" placeholder="Enter Conference ID">
                        </div>
                        <div class="form-group mr-2">
                            <label for="search-approval-status" class="form-label text-primary mr-2">Approval Status:</label>
                            <select name="search-approval-status" id="search-approval-status" class="form-control mr-2">
                                <option value="">All</option>
                                <option value="1">Approved</option>
                                <option value="2">Declined</option>
                                <option value="0">Pending</option>
                            </select>
                        </div>
                        <div class="form-group mr-2">
                            <label for="search-conference-date" class="form-label text-primary mr-2">Conference Date:</label>
                            <input type="date" name="search-conference-date" id="search-conference-date" class="form-control mr-2">
                        </div>
                    </div>
                    <div class="col-2">
                        <div class="form-group mr-2">
                            <label for="search-room-name" class="form-label text-primary mr-2">Room Name:</label>
                            <select name="search-room-name" id="search-room-name" class="form-control mr-2">
                                <option value="">All</option>
                                <?php
                                foreach ($rooms as $room)
                                {
                                    echo "<option value='" . $room . "'>" . $room . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group mr-2">
                            <label for="search-department" class="form-label text-primary mr-2">Department:</label>
                            <select name="search-department" id="search-department" class="form-control mr-2">
                                <option value="">All</option>
                                <?php
                                foreach ($departments as $department)
                                {
                                    echo "<option value='" . $department . "'>" . $department . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <button type="submit" name="search-btn" id="search-btn" class="btn btn-primary mt-4">Search</button>
                        <button type="button" onclick="window.location.reload()" class="btn btn-danger mt-4">Reset</button>
                    </div>
                    <div class="col-8"></div>
                </div>
            </form>
            <table class="table">
                <thead>
                    <tr class="bg-warning text-center">
                        <th>CF. ID</th>
                        <th>Branch Name</th>
                        <th>Approval Status</th>
                        <th>Conference Date</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Duration</th>
                        <th>Room Name</th>
                        <th>Department</th>
                        <th>Booked By</th>
                        <th>Purpose of Booking</th>
                        <th>Booking Date</th>
                        <th>Approve / Decline</th>
                        <th>Reason for decline</th>
                    </tr>
                </thead>
                <tbody class="bg-info">
                    <?php
                    if ($numRows > 0)
                    {
                        while ($row = mysqli_fetch_assoc($result))
                        {
                            ?>
                                <tr class="text-center">
                                    <td><?php echo $row['conference_id']; ?></td>
                                    <td><?php echo $row['branch_name']; ?></td>
                                    <td><?php if ($row['approved'] == 1)
                                            {
                                                echo 'Approved';
                                            }
                                            elseif ($row['approved'] == 2)
                                            {
                                                echo 'Declined';
                                            }
                                            elseif ($row['approved'] == 0)
                                            {
                                                echo 'Pending';
                                            }
                                            else
                                            {
                                                echo 'Pending';
                                            } ?></td>
                                    <td><?php echo $row['conference_date']; ?></td>
                                    <td class="bg-success"><?php echo $row['conference_start_time']; ?></td>
                                    <td class="bg-success"><?php echo $row['conference_end_time']; ?></td>
                                    <td><?php echo $row['conference_duration']; ?></td>
                                    <td class="bg-success"><?php echo $row['room_name']; ?></td>
                                    <td><?php echo $row['department_name']; ?></td>
                                    <td><?php echo $row['entered_by']; ?></td>
                                    <td><?php echo $row['conference_purpose']; ?></td>
                                    <td><?php echo $row['entered_date']; ?></td>
                                    <form method="POST" action="<?php echo $_SERVER["PHP_SELF"]; ?>">
                                        <td>
                                            <input type="hidden" name="conference_id" value="<?php echo $row['conference_id']; ?>">
                                            <button type="submit" name="approve" class="btn btn-success btn-sm w-100" <?php echo $row['approved'] == 1 ? 'disabled' : '' ?>>Approve</button>
                                            <button type="submit" name="decline" class="btn btn-danger btn-sm w-100 mt-2" <?php echo $row['approved'] == 2 ? 'disabled' : '' ?>>Decline</button>
                                        </td>
                                        <td>
                                            <input type="text" name="comment" placeholder="Reason" class="form-control" style="height:4rem" <?php echo $row['approved'] == 1 || $row['approved'] == 0 ? 'required' : 'disabled' ?>>
                                        </td>
                                    </form>
                                </tr>
                            <?php
                        }
                    }
                    else
                    {
                        echo '<tr><td colspan="14" class="text-center">No results found.</td></tr>';
                    } 
                    ?>
                </tbody>
            </table>
        </div>
        <script>
            if ( window.history.replaceState )
            {
                window.history.replaceState( null, null, window.location.href );
            }
        </script>
    <?php endif; ?>

    <?php if(!isset($_SESSION['username'])): ?>
        <div class="container-fluid">
            <div class="row">
                <div class="col-4"></div>
                <div class="col-4 pt-5">
                    <div class="pt-5">
                        <div class="pt-5">
                            <div class="card rounded border" style="background-image: url(../img/login.jpg); background-size: cover;">
                                <div class="card-title">
                                    <h1 class="text-center pt-5">ADMIN LOGIN</h1>
                                </div>
                                <div class="card-body">
                                    <?php if (isset($_GET['message']) && !empty($_GET['message'])): ?>
                                        <div class="alert alert-success alert-dismissible" id="myAlert" role="alert"><?php echo $_GET['message']; ?>
                                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                    <form class="text-center py-3" action="<?php $_SERVER["PHP_SELF"] ?>" method="POST">
                                        <div class="form-group pb-2">
                                            <p class="fw-bolder h4 text-center">User Name:</p>
                                            <div class="ml-5">
                                                <input type="text" name="username" class="form-control col-sm-8 text-center ml-5" placeholder="Enter Your Username" required>
                                            </div>
                                        </div>
                                        <div class="form-group pb-2">
                                            <p class="fw-bolder h4 text-center">Password:</p>
                                            <div class="ml-5">
                                                <input type="password" name="password" class="form-control col-sm-8 text-center ml-5" placeholder="Enter Your Password" required>
                                            </div>
                                        </div>
                                        <button type="submit" name="submit" value="submit" class="btn btn-success btn-lg rounded-circle">Login</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-4"></div>
            </div>
        </div>
    <?php endif; ?>
    <script>
        $('#myAlert').click(function()
        {
            $('#myAlert').addClass('d-none');
        });
    </script>
</body>
</html>