<?php
    require_once('db_connect.php');

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["department_name"]))
    {
        $departmentName = htmlspecialchars($_POST["department_name"]);
        $insertDepartmentQuery = "INSERT INTO department_details (department_name) VALUES ('{$departmentName}')";
        mysqli_query($con, $insertDepartmentQuery);
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["delete_department_id"]))
    {
        $deleteDepartmentId = htmlspecialchars($_POST["delete_department_id"]);
        $deleteDepartmentQuery = "DELETE FROM department_details WHERE department_id = '{$deleteDepartmentId}'";
        mysqli_query($con, $deleteDepartmentQuery);
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["department_id"]) && isset($_POST["availability"]))
    {
        $departmentId = htmlspecialchars($_POST["department_id"]);
        $availability = htmlspecialchars($_POST["availability"]);
        $updateAvailabilityQuery = "UPDATE department_details SET availability = '{$availability}' WHERE department_id = '{$departmentId}'";
        mysqli_query($con, $updateAvailabilityQuery);
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["branch_name"]))
    {
        $branchName = htmlspecialchars($_POST["branch_name"]);
        $insertBranchQuery = "INSERT INTO branch_details (branch_name) VALUES ('{$branchName}')";
        mysqli_query($con, $insertBranchQuery);
    }
    
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["delete_branch_id"]))
    {
        $deleteBranchId = htmlspecialchars($_POST["delete_branch_id"]);
        $deleteBranchQuery = "DELETE FROM branch_details WHERE branch_id = '{$deleteBranchId}'";
        mysqli_query($con, $deleteBranchQuery);
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
    <script src="../js/sweetalert.min.js"></script>
    <link rel="stylesheet" href="../css/sweetalert.min.css">
</head>
<body class="bg-dark">
    <div class="container">
        <div class="row">
            <div class="col-7">
                <div class="card">
                    <div class="card-body bg-warning">
                        <h1 class="text-success text-center pb-3 pt-3">Manage Departments</h1>
                    </div>
                </div>
                <div class="card mt-3">
                    <div class="card-body">
                        <h5 class="card-title">Add New Department</h5>
                        <form id="addDepartmentForm" method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                            <div class="form-group">
                                <label for="departmentName">Department Name</label>
                                <input type="text" class="form-control" id="departmentName" name="department_name" required>
                            </div>
                            <button type="submit" class="btn btn-primary" onclick="return confirmAddDepartment()">Add Department</button>
                        </form>
                    </div>
                </div>
                <?php
                    $departmentQuery = "SELECT department_id, department_name, availability FROM department_details";
                    $departmentResult = mysqli_query($con, $departmentQuery);

                    if ($departmentResult)
                    {
                        while ($departmentRow = mysqli_fetch_assoc($departmentResult))
                        {
                            $departmentId = $departmentRow['department_id'];
                            $departmentName = $departmentRow['department_name'];
                            $availability = $departmentRow['availability'];
                            ?>
                            <div class="card mt-3">
                                <div class="card-body">
                                    <p><?php echo $departmentName; ?></p>
                                </div>
                                <div class="card-footer">
                                    <div class="d-flex justify-content-end">
                                        <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" onsubmit="confirmDeleteDepartment(event, <?php echo $departmentId; ?>)">
                                            <input type="hidden" name="delete_department_id" value="<?php echo $departmentId; ?>">
                                            <button type="submit" class="btn btn-danger">Delete</button>
                                        </form>
                                        <div class="ml-auto">
                                            <button type="button" class="btn btn-success" id="enableButton_<?php echo $departmentId; ?>" onclick="updateAvailability(<?php echo $departmentId; ?>, 1)" <?php echo $availability == 1 ? 'disabled' : ''; ?>>Unblock</button>
                                            <button type="button" class="btn btn-warning" id="disableButton_<?php echo $departmentId; ?>" onclick="updateAvailability(<?php echo $departmentId; ?>, 2)" <?php echo $availability == 2 ? 'disabled' : ''; ?>>Block</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                    }
                ?>
            </div>
            <div class="col-5">
                <div class="card">
                    <div class="card-body bg-warning">
                        <h1 class="text-success text-center pb-2 pt-3">Manage Branch</h1>
                        <div class="h3 text-danger">WARNING</div>
                        <ul class="text-danger">
                            <li>Please before filling/using this form update the source code.</li>
                            <li>It is recommended that before adding/deleating any branch take the approval from the higher authorities.</li>
                            <li>Submitting this form is a very risky process so please ensure that all the above required things are done before submitting.</li>
                        </ul>
                    </div>
                </div>
                <div class="card mt-4">
                    <div class="card-body">
                        <h5 class="card-title">Add New Branch</h5>
                        <form id="addBranchForm" method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                            <div class="form-group">
                                <label for="branchName">Branch Name</label>
                                <input type="text" class="form-control" id="branchName" name="branch_name" required>
                            </div>
                            <button type="submit" class="btn btn-primary" onclick="return confirmAddBranch()">Add Branch</button>
                        </form>
                    </div>
                </div>
                <?php
                    $branchQuery = "SELECT branch_id, branch_name FROM branch_details";
                    $branchResult = mysqli_query($con, $branchQuery);

                    if ($branchResult)
                    {
                        while ($branchRow = mysqli_fetch_assoc($branchResult))
                        {
                            $branchId = $branchRow['branch_id'];
                            $branchName = $branchRow['branch_name'];
                            ?>
                            <div class="card mt-3">
                                <div class="card-body">
                                    <p><?php echo $branchName; ?></p>
                                </div>
                                <div class="card-footer">
                                    <div class="d-flex justify-content-end">
                                        <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" onsubmit="confirmDeleteBranch(event, <?php echo $branchId; ?>)">
                                            <input type="hidden" name="delete_branch_id" value="<?php echo $branchId; ?>">
                                            <button type="submit" class="btn btn-danger">Delete</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                    }
                ?>
            </div>
        </div>
    </div>

    <script>
        function confirmAddDepartment()
        {
            const departmentName = document.getElementById('departmentName').value;
            
            if (departmentName.trim() === '')
            {
                Swal.fire({
                    title: 'Error',
                    text: 'Department name cannot be empty',
                    icon: 'error'
                });
                return false; // Prevent the form submission
            }
            
            Swal.fire({
                title: 'Add Department',
                text: 'Are you sure you want to add this department?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Add',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed)
                {
                    document.getElementById('addDepartmentForm').submit(); // Submit the form
                }
            });

            return false; // Prevent the default form submission
        }

        function confirmDeleteDepartment(event, departmentId)
        {
            event.preventDefault(); // Prevent the form from submitting immediately

            Swal.fire({
                title: 'Delete Department',
                text: 'Are you sure you want to delete this department?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Delete',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed)
                {
                    // Submit the form programmatically
                    document.querySelector(`form[action='<?php echo $_SERVER['PHP_SELF']; ?>'] input[name='delete_department_id']`).value = departmentId;
                    event.target.submit();
                }
            });
        }

        function updateAvailability(departmentId, availability)
        {
            const confirmation = availability === 1 ? 'Unblock' : 'Block';
            Swal.fire({
                title: `${confirmation} Department`,
                text: `Are you sure you want to ${confirmation.toLowerCase()} this department?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes',
                cancelButtonText: 'No'
            }).then((result) => {
                if (result.isConfirmed)
                {
                    const enableButton = document.querySelector(`#enableButton_${departmentId}`);
                    const disableButton = document.querySelector(`#disableButton_${departmentId}`);

                    if (availability === 1)
                    {
                        enableButton.setAttribute('disabled', '');
                        disableButton.removeAttribute('disabled');
                    }
                    else
                    {
                        disableButton.setAttribute('disabled', '');
                        enableButton.removeAttribute('disabled');
                    }

                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', '<?php echo $_SERVER["PHP_SELF"]; ?>', true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    xhr.send(`department_id=${departmentId}&availability=${availability}`);
                }
            });
        }

        function confirmAddBranch()
        {
            const branchName = document.getElementById('branchName').value;
            
            if (branchName.trim() === '')
            {
                Swal.fire({
                    title: 'Error',
                    text: 'Branch name cannot be empty',
                    icon: 'error'
                });
                return false; // Prevent the form submission
            }
            
            Swal.fire({
                title: 'Add Branch',
                text: 'Are you sure you want to add this branch?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Add',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed)
                {
                    document.getElementById('addBranchForm').submit(); // Submit the form
                }
            });

            return false; // Prevent the default form submission
        }

        function confirmDeleteBranch(event, branchId)
        {
            event.preventDefault(); // Prevent the form from submitting immediately

            Swal.fire({
                title: 'Delete Branch',
                text: 'Are you sure you want to delete this branch?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Delete',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed)
                {
                    // Submit the form programmatically
                    document.querySelector(`form[action='<?php echo $_SERVER['PHP_SELF']; ?>'] input[name='delete_branch_id']`).value = branchId;
                    event.target.submit();
                }
            });
        }


        if ( window.history.replaceState )
        {
            window.history.replaceState( null, null, window.location.href );
        }
    </script>
</body>
</html>