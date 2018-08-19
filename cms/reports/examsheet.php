<?php 
    session_start();
    
    if(!isset($_SESSION['instr_id'])) die();
    $instr_id = $_SESSION['instr_id'];

    $report_header = '';
//    session_start();

    if(!isset($_GET['exam'])) header('Location: ../');
    
    include "../../db/clsconnection.php";
    $cn = New Connection;
    $cn->OpenConnection();

    $examid = $cn->escape($_GET['exam']);
    $examcode = $cn->escape($_GET['code']);

    $studentno = ''; $file_name = '';
    if(isset($_GET['stno'])) $studentno = $cn->escape($_GET['stno']);
    
    ob_end_clean();
    include "index.php";

    if($studentno != '') {
        include('student_exam_sheet.php');
    } else {
        include('students_exam_sheet.php');
    }

// set document information
$pdf->SetCreator('OnlineExamSystem');
$pdf->SetAuthor($instructor);
$pdf->SetTitle($file_name);
$pdf->SetSubject($exam_subj);
$pdf->SetKeywords('Online Exam System');

// print a block of text using Write()
//$pdf->Write(0, $txt, '', 0, 'C', true, 0, false, false, 0);

// ---------------------------------------------------------

//Close and output PDF document
$pdf->Output("$file_name.pdf", 'I');



?>
