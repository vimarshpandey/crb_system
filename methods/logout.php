<?php
    session_start();
    $message = $_SESSION['message'];
    $_SESSION = array();
    session_unset();
    session_destroy();
    header('Location: admin.php?message=' . urlencode($message));
?>