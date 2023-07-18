<?php
    require_once('db_connect.php');

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["room_name"]))
    {
        $roomName = htmlspecialchars($_POST["room_name"]);
        $insertRoomQuery = "INSERT INTO room_details (room_name, approval_required) VALUES ('{$roomName}', 0)";
        mysqli_query($con, $insertRoomQuery);
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["room_id"]) && isset($_POST["approval_required"]))
    {
        $roomId = htmlspecialchars($_POST["room_id"]);
        $approvalRequired = htmlspecialchars($_POST["approval_required"]);
        $updateRoomQuery = "UPDATE room_details SET approval_required = '{$approvalRequired}' WHERE room_id = '{$roomId}'";
        mysqli_query($con, $updateRoomQuery);
        // Retrieve the updated room name from the form
        $updatedRoomName = isset($_POST["room_name"]) ? htmlspecialchars($_POST["room_name"]) : '';
        // Update the room name in the database
        if (!empty($updatedRoomName))
        {
            $updateNameQuery = "UPDATE room_details SET room_name = '{$updatedRoomName}' WHERE room_id = '{$roomId}'";
            mysqli_query($con, $updateNameQuery);
        }
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["delete_room_id"]))
    {
        $deleteRoomId = htmlspecialchars($_POST["delete_room_id"]);
        $deleteRoomQuery = "DELETE FROM room_details WHERE room_id = '{$deleteRoomId}'";
        mysqli_query($con, $deleteRoomQuery);
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["room_id"]) && isset($_POST["availability"]))
    {
        $roomId = htmlspecialchars($_POST["room_id"]);
        $availability = htmlspecialchars($_POST["availability"]);
        $updateAvailabilityQuery = "UPDATE room_details SET availability = '{$availability}' WHERE room_id = '{$roomId}'";
        mysqli_query($con, $updateAvailabilityQuery);
    }

    $roomQuery = "SELECT room_id, room_name, approval_required FROM room_details";
    $roomResult = mysqli_query($con, $roomQuery);

    // Retrieve total conference duration for each room
    $today = date('Y-m-d');
    $conferenceQuery = "SELECT room_name, SUM(TIME_TO_SEC(TIMEDIFF(conference_end_time, conference_start_time))) AS total_conference_duration FROM conference_table WHERE approved = 1 AND conference_date = '{$today}' GROUP BY room_name";
    $conferenceResult = mysqli_query($con, $conferenceQuery);

    // Store the total conference duration for each room
    $roomConferenceDuration = array();
    $totalDurationSum = 0;
    while ($conferenceRow = mysqli_fetch_assoc($conferenceResult))
    {
        $roomId = $conferenceRow['room_name'];
        $totalConferenceDuration = $conferenceRow['total_conference_duration'];
        $roomConferenceDuration[$roomId] = $totalConferenceDuration;
        $totalDurationSum += $totalConferenceDuration;
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
</head>
<body class="bg-dark">
    <div class="container">
        <div class="card">
            <div class="card-body bg-warning">
                <h1 class="text-danger text-center pb-3 pt-3">Access and Details of Rooms</h1>
            </div>
        </div>
        <div class="card mt-3">
            <div class="card-body">
                <h5 class="card-title">Add New Room</h5>
                <form id="addRoomForm" method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                    <div class="form-group">
                        <label for="roomName">Room Name</label>
                        <input type="text" class="form-control" id="roomName" name="room_name" required>
                    </div>
                    <button type="submit" class="btn btn-primary" onclick="return confirmAddRoom()">Add Room</button>
                </form>
            </div>
        </div>
        <?php
            $roomQuery = "SELECT room_id, room_name, approval_required,availability FROM room_details";
            $roomResult = mysqli_query($con, $roomQuery);

            if ($roomResult)
            {
                while ($roomRow = mysqli_fetch_assoc($roomResult))
                {
                    $roomId = $roomRow['room_id'];
                    $roomName = $roomRow['room_name'];
                    $approvalRequired = $roomRow['approval_required'];
                    $availability = $roomRow['availability'];
                    $conferenceDuration = isset($roomConferenceDuration[$roomId]) ? round(($roomConferenceDuration[$roomId]/3600), 2) : 0;
                    $percentageBooked = $totalDurationSum > 0 ? round(($conferenceDuration / 10) * 100, 2) : 0;
                    ?>
                    <div class="card mt-3">
                        <div class="card-body">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="room_<?php echo $roomId; ?>" <?php echo $approvalRequired ? 'checked' : ''; ?> onclick="updateApprovalRequired(<?php echo $roomId; ?>, this.checked ? 1 : 0)" style="zoom: 1.5;">
                                <label class="form-check-label" for="room_<?php echo $roomId; ?>" style="font-size: 1.2rem;"><?php echo $roomName ?> <?php echo $approvalRequired ? '(Approval Required)' : ''; ?></label>
                            </div>
                            <p class="mt-3">Total Conference Duration: <?php echo $conferenceDuration; ?> hours</p>
                            <div class="progress mt-3">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: <?php echo $percentageBooked; ?>%;" aria-valuenow="<?php echo $percentageBooked; ?>" aria-valuemin="0" aria-valuemax="100"><?php echo $percentageBooked; ?>%</div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="d-flex justify-content-end">
                                <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" onsubmit="confirmDeleteRoom(event, <?php echo $roomId; ?>)">
                                    <input type="hidden" name="delete_room_id" value="<?php echo $roomId; ?>">
                                    <button type="submit" class="btn btn-danger">Delete</button>
                                </form>
                                <div class="ml-auto">
                                    <button type="button" class="btn btn-success"  id="unblockButton_<?php echo $roomId; ?>" onclick="updateAvailability(<?php echo $roomId; ?>, 1)" <?php echo $availability == 1 ? 'disabled' : ''; ?>>Unblock</button>
                                    <button type="button" class="btn btn-warning" id="blockButton_<?php echo $roomId; ?>" onclick="updateAvailability(<?php echo $roomId; ?>, 2)" <?php echo $availability == 2 ? 'disabled' : ''; ?>>Block</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            }
        ?>
    </div>

    <script>
        function updateAvailability(roomId, availability)
        {
            const confirmation = availability === 1 ? 'Unblock' : 'Block';
            Swal.fire({
                title: `${confirmation} Room`,
                text: `Are you sure you want to ${confirmation.toLowerCase()} this room?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes',
                cancelButtonText: 'No'
            }).then((result) => {
                if (result.isConfirmed)
                {
                    if(availability === 2)
                    {
                        this.document.getElementById(`blockButton_${roomId}`).setAttribute('disabled','');
                        this.document.getElementById(`unblockButton_${roomId}`).removeAttribute('disabled','');
                    }
                    else
                    {
                        this.document.getElementById(`blockButton_${roomId}`).removeAttribute('disabled','');
                        this.document.getElementById(`unblockButton_${roomId}`).setAttribute('disabled','');
                    }
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', '<?php echo $_SERVER["PHP_SELF"]; ?>', true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    xhr.send(`room_id=${roomId}&availability=${availability}`);
                }
            });
        }

        function confirmAddRoom()
        {
            const roomName = document.getElementById('roomName').value;
            
            if (roomName.trim() === '')
            {
                Swal.fire({
                    title: 'Error',
                    text: 'Room name cannot be empty',
                    icon: 'error'
                });
                return false; // Prevent the form submission
            }
            
            Swal.fire({
                title: 'Add Room',
                text: 'Are you sure you want to add this room?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Add',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed)
                {
                    document.getElementById('addRoomForm').submit(); // Submit the form
                }
            });

            return false; // Prevent the default form submission
        }


        function confirmDeleteRoom(event, roomId)
        {
            event.preventDefault(); // Prevent the form from submitting immediately

            Swal.fire({
                title: 'Delete Room',
                text: 'Are you sure you want to delete this room?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Delete',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed)
                {
                    // Submit the form programmatically
                    document.querySelector(`form[action='<?php echo $_SERVER['PHP_SELF']; ?>'] input[name='delete_room_id']`).value = roomId;
                    event.target.submit();
                }
            });
        }

        function updateApprovalRequired(roomId, approvalRequired)
        {
            const confirmation = approvalRequired ? 'Enable' : 'Disable';
            Swal.fire({
                title: `${confirmation} Approval`,
                text: `Are you sure you want to ${confirmation.toLowerCase()} approval for this room?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes',
                cancelButtonText: 'No'
            }).then((result) => {
                if (result.isConfirmed)
                {
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', '<?php echo $_SERVER["PHP_SELF"]; ?>', true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    xhr.send(`room_id=${roomId}&approval_required=${approvalRequired}`);
                }
                else
                {
                    // Restore checkbox state if confirmation is canceled
                    document.getElementById(`room_${roomId}`).checked = !approvalRequired;
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