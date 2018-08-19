<?php
    session_start();
    
    include "../../db/clsconnection.php";
    $cn = New Connection;

    if(!isset($_SESSION['instr_id'])) die();
    $instr_id = $_SESSION['instr_id'];

    $cn->OpenConnection();

    if(isset($_POST['save_profile'])) {
        $firstn = $cn->escape($_POST['firstn']);
        $lastn = $cn->escape($_POST['lastn']);
        $addr = $cn->escape($_POST['addr']);
        $city = $cn->escape($_POST['city']);
        $phone = $cn->escape($_POST['phone']);
        $email = $cn->escape($_POST['email']);        
        
        $cn->query("CALL sp_update_user_profile($instr_id, '$firstn', '$lastn', '$addr', '$city', '$email', '$phone')");
    } else if(isset($_POST['change_pass'])) {
        $old_pass = $cn->escape($_POST['old_pass']);
        $new_pass = $cn->escape($_POST['new_pass']);
        
        $old_pass = hash('sha256', $old_pass);
        $new_pass = hash('sha256', $new_pass);
        
        $cn->query("CALL sp_update_user_password($instr_id, '$old_pass', '$new_pass')");
        
        $row = $cn->getrow();
        
        echo $row['result'];
        die();
    }
    
    $cn->CloseConnection();
    
?>