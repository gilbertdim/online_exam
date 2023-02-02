<?php
    session_start();

    unset($_SESSION['instr_id']);
    unset($_SESSION['instr_name']);
    unset($_SESSION['instr_email']);

    header("Location: ../");
?>