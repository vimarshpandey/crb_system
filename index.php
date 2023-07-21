<?php
session_start();

require_once('methods/db_connect.php');

if (isset($_SESSION['success_message']))
{
    echo "<script>alert('" . $_SESSION['success_message'] . "')</script>";
    unset($_SESSION['success_message']);
}
elseif (isset($_SESSION['error_message']))
{
    echo "<script>alert('" . $_SESSION['error_message'] . "')</script>";
    unset($_SESSION['error_message']);
}
?>



<!DOCTYPE html>
<html>

<head>
  <title>Conference Room Booking</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="css/bootstrap5.8.min.css">
  <link rel="stylesheet" href="css/bootstrap3.7.min.css">
  <link rel="stylesheet" href="css/datepicker.min.css">

  <!-- DEPENDENT -->
  <link rel="stylesheet" href="https://netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap-glyphicons.css">

  <script src="js/jquery.min.js"></script>
  <script src="js/moment.min.js"></script>
  <script src="js/locales.min.js"></script>
  <script src="js/datetimepicker.min.js"></script>
  
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@200;300;400;600&family=Playfair+Display&family=Poppins:wght@200;300;400;500;600;700;800&display=swap');
    body
    {
      font-family: 'Poppins', sans-serif;
      color: #3d3a3a;
    }
    .wooden-input
    {
      background-color: #daacac;
      color: #000;
    }
    .custom-border
    {
      border-right: 2px solid black;
    }
  </style>
</head>

<body style="background-image: url(img/bficr.jpg); background-size: cover;">
  <div class="container-fluid">
    <div class="bg">
      <div class="row">
        <div class="col-2 text-center mt-3">
          <img src="img/cklogo.jpg" height="80px" width="180px"></img>
        </div>
        <div class="col-8" style="color: #050069;">
          <div class="text-center h1 fw-bolder">CONFERENCE ROOM BOOKING</div>
          <div class="h6 text-center">
            1st Floor, CRS Tower, 77B, IFFCO Road, Sec-18, Gurugram - Haryana
          </div>
        </div>
        <div class="col-2 pt-5">
          <img src="img/bfilogo.jpg" height="50px" width="150px"></img>
        </div>
      </div>
      <form action = "methods/process.php" method="POST" class="pt-3">



      <?php

        $sql = "SELECT room_id, room_name, availability, approval_required FROM room_details where availability=1 OR availability=2";
        $result = $con->query($sql);

        $sql1 = "SELECT branch_id, branch_name FROM branch_details";
        $result1 = $con->query($sql1);

        $sql2 = "SELECT department_id, department_name, availability FROM department_details";
        $result2 = $con->query($sql2);

        $options = "";
        if ($result->num_rows > 0)
        {
            while ($row = $result->fetch_assoc())
            {
                $id = $row['room_id'];
                $name = $row['room_name'];
                $availability = isset($row['availability']) ? $row['availability'] : 1;
                $approvalRequired = isset($row['approval_required']) ? $row['approval_required'] : 0;

                if ($availability == 2)
                {
                    $options .= "<option value='$id' disabled>$name (Blocked by Admin)</option>";
                }
                else
                {
                    if ($approvalRequired == 1)
                    {
                        $options .= "<option value='$id'>$name (Approval Required)</option>";
                    }
                    else
                    {
                        $options .= "<option value='$id'>$name</option>";
                    }
                }
            }
        }


        $options1 = "";
        if ($result1->num_rows > 0)
        {
            while ($row1 = $result1->fetch_assoc())
            {
                $id1 = $row1['branch_id'];
                $name1 = $row1['branch_name'];
                $options1 .= "<option value='$id1'>$name1</option>";
            }
        }

        $options2 = "";
        if ($result2->num_rows > 0)
        {
            while ($row2 = $result2->fetch_assoc())
            {
                $id2 = $row2['department_id'];
                $name2 = $row2['department_name'];
                $availability2 = isset($row2['availability']) ? $row2['availability'] : 1;
                if ($availability2 == 2)
                {
                    $options2 .= "<option value='$id2' disabled>$name2 (Blocked by Admin)</option>";
                }
                else
                {
                  $options2 .= "<option value='$id2'>$name2</option>";
                }
            }
        }
      ?>



        <div class="row">
          <div class="col-4 custom-border">
            <div class="form-group">
              <p class="fw-bolder h4">Select Branch:</p>
              <select name="options1" class="form-control wooden-input" id="options1" required>
              <?php echo $options1;?>
              </select>
            </div>
            <div class="row mx-1">
              <div class="col-4">
                <p class="fw-bolder h4 pt-4">Meeting Date:</p>
                <div class='input-group date pb-2' id='dpicker'>
                  <input type='text' name="options6" class="form-control wooden-input" required>
                  <span class="input-group-addon">
                    <span class="glyphicon glyphicon-calendar"></span>
                  </span>
                </div>
              </div>
              <div class="col-4">
                <p class="fw-bolder h4" style="color: rgb(5, 243, 13);">Meeting Start Time:</p>
                <div class='input-group date pb-2' id='spicker'>
                  <input type='text' name="options7" class="form-control wooden-input" required>
                  <span class="input-group-addon">
                    <span class="glyphicon glyphicon-time"></span>
                  </span>
                </div>
              </div>
              <div class="col-4">
                <p class="fw-bolder h4" style="color: rgb(244, 3, 3);">Meeting End Time:</p>
                <div class='input-group date pb-2' id='epicker'>
                  <input type='text' name="options8" class="form-control wooden-input" required>
                  <span class="input-group-addon">
                    <span class="glyphicon glyphicon-time"></span>
                  </span>
                </div>
              </div>
            </div>
            <div class="form-group pb-2">
              <p class="fw-bolder h4">Select Department:</p>
              <Select name="options3" class="form-control wooden-input" id="options3" required>
                <option value="0" disabled selected>--Select Department--</option>
                <?php echo $options2;?>
              </Select>
            </div>
            <div class="form-group">
              <p class="fw-bolder h4">Select Room:</p>
              <select name="options2" class="form-control wooden-input" id="options2" required>
                <option value="0" disabled selected>--Select Room--</option>
                <?php echo $options;?>
              </select>
            </div>
            <div class="form-group pb-2">
              <p class="fw-bolder h4">Enter your Name:</p>
              <input type="text" name="options4" class="form-control wooden-input" placeholder="Enter Your Name" id="options4" required>
            </div>
            <div class="form-group pb-2">
              <p class="fw-bolder h4">Purpose of Booking:<span class="h5 fw-bold">(Minimun 5 words)</span></p>
              <textarea name="options5" class="form-control wooden-input" rows="3" id="options5" required></textarea>
              <span id="wordCountMessage"></span>
            </div>
            <input type="hidden" name="options9" id="durationField" value="">
            <div>
              <button type="submit" name="submit" value="submit" class="btn btn-primary">Proceed for Approval</button>
              <button type="button" onclick="window.location.reload()" class="btn btn-danger">Reset</button>
              <a href="methods/upcoming_bookings.php" type="button" class="btn btn-primary">Upcoming Bookings</a>
              <a href="methods/admin.php" type="button" class="btn btn-primary mt-3">Login as Admin</a>
            </div>
          </div>
          
          <div class="col-3 px-4">
            
            <div class="pt-5 mt-1">
              <div class="fw-bolder text-center h3 mt-5 pt-5">Approved Bookings:</div>
              <ul class="list-group" id="bookedRooms">
              <?php
                $sql = "SELECT cr.room_name, ct.conference_start_time, ct.conference_end_time, ct.entered_by FROM conference_table AS ct 
                INNER JOIN room_details cr ON ct.room_name = cr.room_id WHERE ct.approved = 1 AND ct.conference_date = '$cur_date'";
                $result = $con->query($sql);

                if ($result->num_rows > 0)
                {
                    while ($row = $result->fetch_assoc()) {
                        $roomName = $row['room_name'];
                        $startTime = $row['conference_start_time'];
                        $endTime = $row['conference_end_time'];
                        $person = $row['entered_by'];
                        echo "<li class='list-group-item text-success'>$roomName - $startTime to $endTime by $person</li>";
                    }
                }
                else
                {
                    echo "<li class='list-group-item'>No rooms booked at the moment</li>";
                } 
            ?>
              </ul>
            </div>
          </div>
          <div class="col-3 px-3">
            <div class="h1 fw-bolder"><u>Booking Status</u></div>
            <div class="fw-bolder text-center h3 mt-5">Pending for Approval:</div>
            <ul class="list-group" id="pendingRooms">
            <?php
              $sql = "SELECT cr.room_name, ct.conference_start_time, ct.conference_end_time, ct.entered_by FROM conference_table AS ct 
              INNER JOIN room_details cr ON ct.room_name = cr.room_id WHERE ct.approved = 0 AND ct.conference_date = '$cur_date' AND ct.conference_end_time > '$cur_time' ";
              $result = $con->query($sql);

              if ($result->num_rows > 0)
              {
                  while ($row = $result->fetch_assoc())
                  {
                      $roomName = $row['room_name'];
                      $startTime = $row['conference_start_time'];
                      $endTime = $row['conference_end_time'];
                      $person = $row['entered_by'];
                      echo "<li class='list-group-item'>$roomName - $startTime to $endTime by $person</li>";
                  }
              }
              else
              {
                  echo "<li class='list-group-item'>No rooms in the pending list.</li>";
              }   
            ?>
            </ul>
          </div>
          <div class="col-2 px-1">
            <div class="pt-5 mt-1">
            <div class="fw-bolder text-center h3 mt-5 pt-5">Declined Bookings:</div>
            <ul class="list-group" id="pendingRooms">
            <?php

              $sql = "SELECT cr.room_name, ct.conference_start_time, ct.conference_end_time, ct.entered_by, ct.comment FROM conference_table AS ct 
              INNER JOIN room_details cr ON ct.room_name = cr.room_id WHERE ct.approved = 2 AND ct.conference_date = '$cur_date'";
              $result = $con->query($sql);

              if ($result->num_rows > 0)
              {
                  while ($row = $result->fetch_assoc())
                  {
                      $roomName = $row['room_name'];
                      $startTime = $row['conference_start_time'];
                      $endTime = $row['conference_end_time'];
                      $person = $row['entered_by'];
                      $comment = $row['comment'];
                      echo "<li class='list-group-item text-danger'>$roomName - $startTime to $endTime by $person Reason: $comment</li>";
                  }
              }
              else
              {
                  echo "<li class='list-group-item'>No rooms in the pending list.</li>";
              }   
            ?>
            </ul>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
  <script type="text/javascript">
    $(document).ready(function ()     // imp function
    {
      var startTime = moment();
      var defaultstartTime = startTime.add(12, 'minutes');  // for start time
      var endTime = moment(); // Object to track booked time slots for each conference room
      var defaultendTime = endTime.add(42, 'minutes');  // for end time
      var textarea = document.getElementById('options5');
      var wordCountMessage = document.getElementById('wordCountMessage');

      textarea.addEventListener('input', function()
      {
        var wordCount = textarea.value.trim().split(/\s+/).length;

        if (wordCount < 3)
        {
          //wordCountMessage.textContent = 'Minimum 3 words required.';
          textarea.setCustomValidity('Minimum 3 words required.');
        }
        else
        {
          wordCountMessage.textContent = '';
          textarea.setCustomValidity('');
        }
      });

      $('#dpicker').datetimepicker({
        format: 'YYYY-MM-DD',
        minDate: moment().startOf('day'),
        defaultDate: moment().startOf('day')
      });

      // Initialize timepicker for start time
      $('#spicker').datetimepicker({
        format: 'HH:mm',
        defaultDate: defaultstartTime
      });

      // Initialize timepicker for end time
      $('#epicker').datetimepicker({
        format: 'HH:mm',
        defaultDate: defaultendTime
      });

      // Compare start and end time on change
      $('#spicker').on('dp.change', function (e) {
        startTime = moment($('#spicker input').val(), 'HH:mm');
        calculateDuration();
      });

      $('#epicker').on('dp.change', function (e) {
        endTime = moment($('#epicker input').val(), 'HH:mm');
        calculateDuration();
      });

      $('#dpicker').on('dp.change', function (e) {
        compareTimes();
      });

      $('#options2, #options3, #options4, #options5').change(function () {
        compareTimes();
      });

      function compareTimes() 
      {
        var now = moment().add(10, 'minute');
        //var reset_start_time = moment().add(12, 'minute');
        var selectedDate = moment($('#dpicker input').val(), 'YYYY-MM-DD');
        var startTime = moment($('#spicker input').val(), 'HH:mm');
        var endTime = moment($('#epicker input').val(), 'HH:mm');
        //var reset_end_time = moment().add(45, 'minute');

        if (selectedDate.isSame(moment(), 'day')) {
          // Selected date is today's date
          if (startTime.isBefore(now)) {
            // Start time is before the present time
            alert('Start time should be after 10 minutes from present time.');
            // Reset the start time to the present time
            $('#options3').val(0);
            $('#options2').val(0);
            
          }
        }

        if (endTime.isBefore(startTime)) {
          // End time is before start time
          alert('End time must be after start time.');
          $('#epicker input').val(reset_end_time.format('HH:mm'));
        }

        checkMeetingDuration();

      }

      function checkMeetingDuration()
      {
        var duration = moment.duration(endTime.diff(startTime));
        var minutes = duration.asMinutes();
        // console.log("Minutes " + minutes);
        if (minutes < 30) {
          alert('The duration of the meeting should be at least 30 minutes.');
        }
      }

      function calculateDuration()
      {
        var duration = moment.duration(endTime.diff(startTime));
        var hours = duration.hours();
        var minutes = duration.minutes();
        var seconds = duration.seconds();

        // Convert duration to HH:MM:SS format
        var formattedDuration = formatDuration(hours, minutes, seconds);

        // Update the hidden field value
        $('#durationField').val(formattedDuration);
      }

      function formatDuration(hours, minutes, seconds)
      {
        // Add leading zeros to ensure two digits for hours, minutes, and seconds
        var formattedHours = ('0' + hours).slice(-2);
        var formattedMinutes = ('0' + minutes).slice(-2);
        var formattedSeconds = ('0' + seconds).slice(-2);

        // Concatenate hours, minutes, and seconds with colons
        var formattedDuration = formattedHours + ':' + formattedMinutes + ':' + formattedSeconds;

        return formattedDuration;
      }

      calculateDuration();

    });

    // Set the timeout duration in milliseconds
    var timeoutDuration = 55000; // 55 seconds

    // Initialize the timeout variable
    var timeout;

    // Start the timeout function
    function startTimeout() {
      // Clear the previous timeout, if any
      clearTimeout(timeout);

      // Set the new timeout
      timeout = setTimeout(refreshPage, timeoutDuration);
    }

    // Function to refresh the page
    function refreshPage() {
      location.reload();
    }

    // Attach event listeners to restart the timeout on user activity
    document.addEventListener("mousemove", startTimeout);
    document.addEventListener("mousedown", startTimeout);
    document.addEventListener("keypress", startTimeout);
    document.addEventListener("touchmove", startTimeout);

    // Start the initial timeout
    startTimeout();

  </script>
</body>

</html>