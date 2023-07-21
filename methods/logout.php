<?php
    ob_start();
    session_start();
    $message = isset($_SESSION['message']) ? $_SESSION['message'] : 'Successfully logout!';
    $_SESSION = array();
    session_unset();
    session_destroy();
    header('Location: admin.php?message=' . urlencode($message));
?>