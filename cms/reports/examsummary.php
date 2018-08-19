<?php 
session_start();

if(!isset($_SESSION['instr_id'])) die();
$instr_id = $_SESSION['instr_id'];

$report_header = '';

if(!isset($_GET['exam'])) header('Location: ../');

include "../../db/clsconnection.php";
$cn = New Connection;
$cn->OpenConnection();

$examid = $cn->escape($_GET['exam']);
//$examid = 2;

$file_name = '';

ob_end_clean();
include "index.php";

$cn->query("SELECT * FROM vw_exams_with_instructor WHERE id = $examid");

$row = $cn->getrow();
$exam_title = $row['title'];
$exam_subj = $row['subj'];
$exam_descrip = $row['descrip'];
$exam_instruction = $row['instruction'];
$instructor = $row['instructor'];

$cn->query("SELECT * FROM exam_code WHERE exam_id = $examid");
$ecodes = $cn->getrows();

foreach($ecodes as $ecode) {
    $exam_code = $ecode['exam_code'];
    $exam_start = date_format(date_create($ecode['dtestart']), 'M d, Y h:i A'); 
    $exam_end = date_format(date_create($ecode['dteend']), 'M d, Y h:i A');
    
    $cn->CloseRecordset();
    $cn->query("SELECT DISTINCT course, sect FROM vw_exam_examinees WHERE exam_code = '$exam_code'");
    $course_sects = $cn->getrows();
    
    foreach($course_sects as $course_sect) {
        $course = $course_sect['course']; 
        $section = $course_sect['sect'];
        
        $cn->CloseRecordset();
        $cn->query("SELECT * FROM vw_exam_examinees WHERE exam_id = $examid AND course = '$course' AND sect = '$section' AND exam_code = '$exam_code'");
        
        $examinees = '';
        $i = 1;
        while($row = $cn->getrow()) {
            $student_no = $row['studentno']; 
            $student_name = $row['fullname']; 
            $exam_score = $row['score']; 
            $exam_percent = $row['percent'];
            
            $examinees .= '<tr align="center">
                <td width="60">'.$i++.'</td>
                <td>'.$student_no.'</td>
                <td align="left" width="370">'.$student_name.'</td>
                <td width="60">'.$exam_score.'</td>
                <td width="60">'.$exam_percent.'</td>
            </tr>';
        }
        
        $html = '
        <table cellspacing="0" cellpadding="1" style="background-color: #0072c6; color: white">
            <tr>
                <td colspan="3" align="left"><h1>'.$exam_title.'</h1></td>
            </tr>
            <tr>
                <td align="left" width="75"><b>Exam Code :</b></td>
                <td width="295">'.$exam_code.'</td>
                <td width="75"></td>
                <td width="55"></td>
                <td width="75"><b></b></td>
                <td></td>
            </tr>
            <tr>
                <td align="left" width="75"><b>Course :</b></td>
                <td width="295">'.$course.'</td>
                <td width="75"></td>
                <td width="55"></td>
                <td width="75"><b>Date Start :</b></td>
                <td>'.$exam_start.'</td>
            </tr>
            <tr>
                <td align="left"><b>Section :</b></td>
                <td>'.$section.'</td>
                <td></td>
                <td></td>
                <td><b>Date End :</b></td>
                <td>'.$exam_end.'</td>
            </tr>
        </table>
        <hr>
        <table cellspacing="0" cellpadding="1">
            <tr>
                <td align="left" width="70">Instructor</td>
                <td width="300">'.$instructor.'</td>
                <td align="left" width="70">Subject</td>
                <td width="260">'.$exam_subj.'</td>
            </tr>
            <tr>
                <td align="left">Description</td>
                <td colspan="2">'.$exam_descrip.'</td>
            </tr>
        </table>
        <table cellspacing="0" cellpadding="2" border="1">
            <thead>
                <tr align="center" style="background-color: #333333; color: white; font-weight: bold">
                    <td width="60">No.</td>
                    <td>Student No.</td>
                    <td width="370">Name</td>
                    <td width="60">Score</td>
                    <td width="60">%</td>
                </tr>
            </thead>
            '.$examinees.'
        </table>
        ';
        
        $pdf->startPageGroup();
        $pdf->AddPage();
        // Print text using writeHTMLCell()
        //$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
        $pdf->writeHTML($html, true, false, false, false, '');
    }

    
}

$file_name = "Exam $exam_subj $exam_title";
$file_name = str_replace(' ', '_', $file_name);


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
