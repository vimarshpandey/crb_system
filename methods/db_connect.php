<?php
    date_default_timezone_set("Asia/Kolkata");
    $cur_date = date('Y-m-d');
    $cur_time = date('H:i:s');
    $con = mysqli_connect("localhost","root","","ckb_conference_room");
    if(!$con)
    {
        die("Connection Error");
    }
?>