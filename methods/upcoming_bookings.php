<?php
require_once('db_connect.php');

// Retrieve room names
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

// Search functionality
if (isset($_POST['search-btn']))
{
    // Retrieve the search inputs
    $searchConferenceId = $_POST['search-conference-id'];
    $searchApprovalStatus = $_POST['search-approval-status'];
    $searchConferenceDate = $_POST['search-conference-date'];
    $searchRoomName = $_POST['search-room-name'];
    $searchDepartment = $_POST['search-department'];

    // Build the WHERE clause based on the provided search criteria
    $whereClause = '';
    if (!empty($searchConferenceId))
    {
        $whereClause .= " AND ct.conference_id = '$searchConferenceId'";
    }
    if (!empty($searchApprovalStatus))
    {
        $whereClause .= " AND ct.approved = '$searchApprovalStatus'";
    }
    if (!empty($searchConferenceDate))
    {
        $whereClause .= " AND ct.conference_date = '$searchConferenceDate'";
    }
    if (!empty($searchRoomName))
    {
        $whereClause .= " AND rd.room_name LIKE '%$searchRoomName%'";
    }
    if (!empty($searchDepartment))
    {
        $whereClause .= " AND dd.department_name LIKE '%$searchDepartment%'";
    }

    // Update the SQL query with the WHERE clause
    $query = "SELECT ct.conference_id, bd.branch_name, ct.approved, ct.conference_date, ct.conference_start_time, ct.conference_end_time, ct.conference_duration, rd.room_name, dd.department_name, ct.entered_by, ct.conference_purpose, ct.entered_date, ct.comment
              FROM conference_table AS ct
              INNER JOIN room_details AS rd ON rd.room_id = ct.room_name
              INNER JOIN department_details AS dd ON dd.department_id = ct.department_name
              INNER JOIN branch_details AS bd ON bd.branch_id = ct.branch_name
              WHERE (DATE(ct.conference_date) > '$cur_date' OR (DATE(ct.conference_date) = '$cur_date' AND TIME(ct.conference_end_time) > '$cur_time'))
              $whereClause
              ORDER BY ct.conference_id DESC LIMIT 0,500";
    
    // Execute the modified query
    $result = mysqli_query($con, $query);
    $numRows = mysqli_num_rows($result);
    
}
else
{
    $query = "SELECT ct.conference_id, bd.branch_name, ct.approved, ct.conference_date, ct.conference_start_time, ct.conference_end_time, ct.conference_duration, rd.room_name, dd.department_name, ct.entered_by, ct.conference_purpose, ct.entered_date, ct.comment
                FROM conference_table AS ct
                INNER JOIN room_details AS rd ON rd.room_id = ct.room_name
                INNER JOIN department_details AS dd ON dd.department_id = ct.department_name
                INNER JOIN branch_details AS bd ON bd.branch_id = ct.branch_name
                WHERE DATE(ct.conference_date) > '$cur_date' OR (DATE(ct.conference_date) = '$cur_date' AND TIME(ct.conference_end_time) > '$cur_time')
                ORDER BY ct.conference_id DESC LIMIT 0,1000";
    $result = mysqli_query($con, $query);
    $numRows = mysqli_num_rows($result);
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="../css/bootstrap4.min.css">
    <script src="../js/table2csv.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@200;300;400;600&family=Playfair+Display&family=Poppins:wght@200;300;400;500;600;700;800&display=swap');
        body
        {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>
<body class="bg-dark">
    <div class="container-fluid">
        <h1 class="text-danger text-center pb-3 pt-3">Details of Upcoming Bookings</h1>
        <button id="export-btn" class="btn btn-primary mb-3 float-right">Export to Excel</button>
        <div class="row mb-3">
            <form method="POST" action="">
                <div class="row">
                    <div class="col-5">
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
                    <div class="col-5">
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
                    <div class="col-2"></div>
                </div>
            </form>
        </div>

        <table id="booking-table" class="table">
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
                    <th>Comment</th>
                </tr>
            </thead>
            <tbody class="bg-success">
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
                            <td class="bg-info"><?php echo $row['conference_start_time']; ?></td>
                            <td class="bg-info"><?php echo $row['conference_end_time']; ?></td>
                            <td><?php echo $row['conference_duration']; ?></td>
                            <td><?php echo $row['room_name']; ?></td>
                            <td class="bg-info"><?php echo $row['department_name']; ?></td>
                            <td><?php echo $row['entered_by']; ?></td>
                            <td><?php echo $row['conference_purpose']; ?></td>
                            <td><?php echo $row['entered_date']; ?></td>
                            <td><?php echo $row['comment']; ?></td>
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

        <script>
            // Fetch the data from the server-side script
            fetch('get_bookings.php')
                .then(data => {
                    // Create the HTML table rows dynamically
                    const rows = data.map(row => `<tr>
                        <td>${row.conference_id}</td>
                        <td>${row.branch_name}</td>
                        <td>${getStatus(row.approved)}</td>
                        <td>${row.conference_date}</td>
                        <td>${row.conference_start_time}</td>
                        <td>${row.conference_end_time}</td>
                        <td>${row.conference_duration}</td>
                        <td>${row.room_name}</td>
                        <td>${row.department_name}</td>
                        <td>${row.entered_by}</td>
                        <td>${row.conference_purpose}</td>
                        <td>${row.entered_date}</td>
                        <td>${row.comment}</td>
                    </tr>`);

                    // Append the rows to the table body
                    const tableBody = document.getElementById('booking-table').getElementsByTagName('tbody')[0];
                    tableBody.innerHTML = rows.join('');
                })
                .catch(error => {
                    console.error('Error:', error);
                });

            // Function to get the status based on the 'approved' value
            function getStatus(approved)
            {
                if (approved === 1)
                {
                    return 'Approved';
                }
                else if (approved === 2)
                {
                    return 'Declined';
                }
                else
                {
                    return 'Pending';
                }
            }

            // Export the table data to Excel on button click
            const exportBtn = document.getElementById('export-btn');
            exportBtn.addEventListener('click', () => {
                const table = document.getElementById('booking-table');
                const workbook = XLSX.utils.table_to_book(table, { sheet: 'Bookings' });
                XLSX.writeFile(workbook, 'Upcomin Bookings.xlsx');
            });
            if ( window.history.replaceState )
            {
                window.history.replaceState( null, null, window.location.href );
            }
        </script>
    </div>
</body>
</html>