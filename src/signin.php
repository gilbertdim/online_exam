<?php
    if(!isset($cn)) header('Location: index.php');
    
    $withLogIn = isset($_POST['btnSignIn']);
    $login_err = 0;

    if($withLogIn) {
        $cn->OpenConnection();
        
        $username = $cn->escape($_POST['txtUsername']);
        $password = $cn->escape($_POST['txtPassword']);
        
        $password = hash('sha256', $password);
        $cn->query("CALL sp_validate_login('$username', '$password', '')");
        if($cn->hasrow()) {
            $row = $cn->getrow();
            if($row['isactive'] == 1) {
                $_SESSION['instr_id'] = $row['id'];
                $_SESSION['instr_name'] = $row['fname'] . ' '. $row['lname'];
                $_SESSION['instr_email'] = $row['email'];
                $_SESSION['instr_type'] = $row['usertype'];
                
                header('Location: cms');
            } else {
               $login_err = 1; 
            }
            
        }
        
        $cn->CloseConnection();
    }
?>