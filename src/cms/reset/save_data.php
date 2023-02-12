<?php
    session_start();
    
    include "../../db/clsconnection.php";
    $cn = New Connection;

    $cn->OpenConnection();

if(isset($_POST['reset_examinee_status'])) {
    $exam_code = $cn->escape($_POST['exam_code']);
    $examinee_code = $cn->escape($_POST['examinee_code']);
    
    $sql = "CALL sp_reset_examinee_status('$exam_code', '$examinee_code')";

    $cn->query($sql);
    
    if($cn->hasrow()) {
        $row = $cn->getrow();
        echo json_encode($row);
    }
}    
?>