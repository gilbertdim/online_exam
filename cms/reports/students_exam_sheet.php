<?php

if(!isset($report_header)) header('Location: ../');

$cn->query("SELECT * FROM vw_exams_with_instructor WHERE id = $examid");

$row = $cn->getrow();
$exam_title = $row['title'];
$exam_subj = $row['subj'];
$exam_descrip = $row['descrip'];
$exam_instruction = $row['instruction'];
$instructor = $row['instructor'];

$cn->CloseRecordset();
$cn->query("SELECT * FROM vw_exam_examinees WHERE exam_id = $examid AND exam_code = '$examcode'");

$examinees = $cn->getrows();
foreach($examinees as $examinee) {
    $studentno = $examinee['studentno'];
    $student_name = $examinee['fullname']; 
    $course = $examinee['course']; 
    $section = $examinee['sect'];
    $exam_score = $examinee['score']; 
    $exam_total = $examinee['totalscore'];
    $exam_start = date_format(date_create($examinee['examstart']), 'M d, Y h:i A'); 
    $exam_end = date_format(date_create($examinee['examend']), 'M d, Y h:i A');
        
    $cn->CloseRecordset();
    $cn->query("CALL sp_select_student_exam_sheet($examid, '$examcode', '$studentno')");

    $questions = '';
    $old_question = '';
    $i = 0;
    while($row = $cn->getrow()) {
        $question = $row['question'];
        $answer = $row['student_answer'];
        $correct_answer = $row['correct_answer'];

        if($old_question == '' || $old_question != $row['id']) {
            $i++;
            $questions .= '
    <tr>
        <td align="center" width="30" style="color: '.($answer == $correct_answer ? 'green' : 'red').'"><b>'.$answer.'</b><hr></td>
        <td align="center" width="30">'.$i.'.</td>
        <td width="605">'.$question.'</td>
    </tr>';

        }

        if($row['question_type'] == 'True/False') {
            $questions .= '<tr>
                <td align="center"></td>
                <td align="center"></td>
                <td style="'.($correct_answer == 'True' ? 'border: 1px solid green' : '').'">True</td>
            </tr><tr>
                <td align="center"></td>
                <td align="center"></td>
                <td style="'.($correct_answer == 'False' ? 'border: 1px solid green' : '').'">False</td>
            </tr>';
        } else {
            $questions .= '<tr>
                <td align="center"></td>
                <td align="center"></td>
                <td style="'.($correct_answer == $row['option_letter'] ? 'border: 1px solid green' : '').'">'.$row['option_letter'].'. '.$row['option_descrip'].'</td>
            </tr>';
        }

        $old_question = $row['id'];
    }
    
    $html = '
    <table cellspacing="0" cellpadding="1" style="background-color: #0072c6; color: white">
        <tr>
            <td colspan="3" align="left"><h1>'.$student_name.' - ['.$studentno.']</h1></td>
        </tr>
        <tr>
            <td align="left" width="70"><b>Course :</b></td>
            <td width="300">'.$course.'</td>
            <td width="75"><b>Score :</b></td>
            <td width="55">'.$exam_score.'</td>
            <td width="75"><b>Date Start :</b></td>
            <td>'.$exam_start.'</td>
        </tr>
        <tr>
            <td align="left"><b>Section :</b></td>
            <td>'.$section.'</td>
            <td><b>Total Item :</b></td>
            <td>'.$exam_total.'</td>
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
            <td align="left">Exam Title</td>
            <td colspan="2" width="">'.$exam_title.'</td>
        </tr>
        <tr>
            <td align="left">Description</td>
            <td colspan="2">'.$exam_descrip.'</td>
        </tr>
        <tr>
            <td align="left">Instruction</td>
            <td colspan="2">'.$exam_instruction.'</td>
        </tr>
    </table>
    <hr>
    <table cellspacing="0" cellpadding="1">
        <tr>
            <td align="center" width="60"></td>
            <td align="center" width="40"></td>
        </tr>
        '.$questions.'
        <tr>
            <td></td>
            <td></td>
        </tr>
    </table>
    ';
    
    $pdf->startPageGroup();
    $pdf->AddPage();
    // Print text using writeHTMLCell()
    //$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
    $pdf->writeHTML($html, true, false, false, false, '');
}


$file_name = "Exam $exam_subj $exam_title";
$file_name = str_replace(' ', '_', $file_name);

?>