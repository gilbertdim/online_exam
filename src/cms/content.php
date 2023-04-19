<?php
    include "../db/clsconnection.php";
    $cn = New Connection;

    if (isset($_POST['getProfile'])) {
        if ($_POST['getProfile'] == 'Profile') {
            include 'profile/index.php';
        } else if ($_POST['getProfile'] == 'Exam') {
            include 'exam/index.php';
        } else if ($_POST['getProfile'] == 'Users') {
            include 'users/index.php';
        } else if ($_POST['getProfile'] == 'Reset') {
            include 'reset/index.php';
        }
    }

?>