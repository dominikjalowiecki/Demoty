<?php
    require 'shared/cookie_httponly.php';
    session_start();
    $is_logged = $_SESSION['is_logged'] ?? False;
    if($is_logged)
    {
        session_destroy();
    }
    header('Location: index.php');
?>