<?php
    session_start();
    require_once('db_connect.php');
    // Check if the form is submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST')
    {
        // Get the submitted form data
        $username = $_SESSION['username'];
        $oldPassword = $_POST['old_password'];
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];

        // Retrieve the user's password from the database
        $query = "SELECT password FROM users WHERE user_name = '$username'";
        $result = mysqli_query($con, $query);
        $row = mysqli_fetch_assoc($result);
        $storedPassword = $row['password'];

        // Verify the old password
        if (password_verify($oldPassword, $storedPassword))
        {
            // Validate the new password
            if ($newPassword === $confirmPassword)
            {
                // Update the password in the database
                $newPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $updateQuery = "UPDATE users SET password = '$newPassword' WHERE user_name = '$username'"; // Replace 'users' with your table name and 'username' with the appropriate column name
                mysqli_query($con, $updateQuery);
                $_SESSION['message'] = 'Password is Changed, Please login to continue.';
                header('Location: logout.php');
            }
            else
            {
                echo '<script>alert("New password and confirm password do not match."); </script>';
            }
        }
        else
        {
            echo '<script>alert("Old Password is Incorrect.");</script>';
        }
    }
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="../css/bootstrap4.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@200;300;400;600&family=Playfair+Display&family=Poppins:wght@200;300;400;500;600;700;800&display=swap');
        body
        {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>
<body class="bg-dark">
    <div class="pt-5">
        <div class="pt-3">
            <div class="row">
                <div class="col-4"></div>
                <div class="col-4">
                    <?php if(isset($_SESSION['username'])  && $_SESSION['username'] !== ''): ?>
                    <div class="card" style="background-image: url(../img/login.jpg); background-size: cover;">
                        <div class="card-title text-center">
                            <div class="text-danger h1 pt-4">Change Password</div>
                        </div>
                        <div class="card-body text-center">
                            <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                                <div class="form-group">
                                    <label for="old_password">Old Password:</label>
                                    <input type="password" id="old_password" name="old_password" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label for="new_password">New Password:</label>
                                    <input type="password" id="new_password" name="new_password" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label for="confirm_password">Confirm Password:</label>
                                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                                </div>
                                <button type="submit" class="btn btn-success mt-5">Change Password</button>
                            </form>
                        </div>
                    </div>
                    <?php else: ?>
                        <?php header('Location: admin.php') ?>
                    <?php endif; ?>
                </div>
                <div class="col-4"></div>
            </div>
        </div>
    </div>
</body>
</html>