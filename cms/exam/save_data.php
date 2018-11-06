<?php
    session_start();
    
    include "../../db/clsconnection.php";
    $cn = New Connection;

    if(!isset($_SESSION['instr_id'])) die();
    $instr_id = $_SESSION['instr_id'];

    $cn->OpenConnection();

    if(isset($_POST['save_new_exam'])) {
        $ExamTitle = $cn->escape($_POST['ExamTitle']);
        $ExamDescrip = $cn->escape($_POST['ExamDescrip']);
        $ExamInstruction = $cn->escape($_POST['ExamInstruction']);
        $ExamSubj = $cn->escape($_POST['ExamSubj']);
        
        
        $cn->query("CALL sp_add_edit_delete_exam(0, 0, '$ExamTitle', '$ExamDescrip', '$ExamInstruction', $instr_id, '$ExamSubj')");
    }
    
    elseif (isset($_POST['update_exam'])) {
        $update_exam = $cn->escape($_POST['update_exam']);
        $ExamTitle = $cn->escape($_POST['ExamTitle']);
        $ExamDescrip = $cn->escape($_POST['ExamDescrip']);
        $ExamInstruction = $cn->escape($_POST['ExamInstruction']);
        $ExamSubj = $cn->escape($_POST['ExamSubj']);
        
        $cn->query("CALL sp_add_edit_delete_exam(1, $update_exam, '$ExamTitle', '$ExamDescrip', '$ExamInstruction', $instr_id, '$ExamSubj')");        
    } 
    
    elseif (isset($_POST['delete_exam'])) {
        $delete_exam = $cn->escape($_POST['delete_exam']);
        
        $cn->query("CALL sp_add_edit_delete_exam(2, $delete_exam, '', '', '', $instr_id, '')");        
    } 
    
    elseif (isset($_POST['NewEditQuestion'])) {
        $id = $cn->escape($_POST['NewEditQuestion']);
        $ExamID = $cn->escape($_POST['ExamID']);
        $QuestionType = $cn->escape($_POST['QuestionType']);
        
        $cn->query("SELECT 1 FROM exam_code WHERE exam_id = $ExamID");
        $hascode = $cn->hasrow();
        $cn->CloseRecordset();
        
        $instr_id = ($id == 0 ? 0 : $instr_id);
        $cn->query("SELECT * FROM vw_question WHERE examid = if($id = 0, 0, $ExamID) AND questionid = $id AND tech_id = $instr_id");
        
        if($cn->hasrow()) {
            $tr = '';
            $answer_opt = '';
            
            while($row = $cn->getrow()) {
                $question = $row['question'];
                $answer = $row['answer'];
                
                if($row['option_letter'] != '' || $row['descrip'] != '') {
                    $tr .= '<tr>
                        <td class="align-center">'.$row['option_letter'].'</td>
                        <td class="no-padding">
                        <div class="input-control no-margin textarea full-size">
                            <textarea id="txtQOption'.$row['option_letter'].'"'.($hascode ? 'readonly' : '').'>'.$row['descrip'].'</textarea>
                        </div>
                        </td>
                    </tr>';
                }
            }
            ?>
            <script>
                $('#btnSaveQuestion').click(function(){
                    var question_id = <?php echo $id; ?>;
                    var examid = <?php echo $ExamID; ?>;
                    var question_type = "<?php echo $QuestionType; ?>";
                    var question = $('#txtQuestion')[0].value;
                    var option_a = '';
                    var option_b = '';
                    var option_c = '';
                    var option_d = '';
                    var question_answer = $('#txtAnswer')[0].value;
                    
                    <?php if($QuestionType != 'True/False') { ?>
                        option_a = $('#txtQOptionA')[0].value;
                        option_b = $('#txtQOptionB')[0].value;
                        option_c = $('#txtQOptionC')[0].value;
                        option_d = $('#txtQOptionD')[0].value;
                    <?php } ?>
//                    var question_options = $('#tblAnswers>tbody').find('tr').map(function(){
//                                                return [$('td', this).map(function(){ return $(this).text();}).get()];
//                                            }).get();
                    
                    if (question_type == 'True/False' && (question == '' || question_answer == '')) {
                        $.Notify({
                            caption : 'Saving Failed',
                            content : 'Please enter Question and the correct answer.',
                            type : 'alert',
                            timeout : 6000
                        });
                    } else if (question_type != 'True/False' && (question == '' || option_a == '' || option_b == '' || option_c == '' || option_d == '' || question_answer == '')) {
                        $.Notify({
                            caption : 'Saving Failed',
                            content : 'Please enter a Question, complete the Question Options and the select the Correct Answer.',
                            type : 'alert',
                            timeout : 6000
                        });
                    } else {
                        $.post(
                            'save_data.php', {
                                save_question : question_id,
                                ExamId : examid,
                                txtQType : question_type,
                                txtQuestion : question,
                                txtOptionA : option_a,
                                txtOptionB : option_b,
                                txtOptionC : option_c,
                                txtOptionD : option_d,
//                                tblOption : question_options,
                                txtAnswer : question_answer
                            },function(data) {
                                GenerateTableQuestion();
                                
                                $.Notify({
                                    caption : 'Saving Complete',
                                    content : 'Question was successfully saved.',
                                    type : 'success',
                                    timeout : 6000
                                });
                                
                                metroDialog.close('#frmQuestion');
                            }
                        );
                    }
                });
            </script>
            <div class="cell size12">
                <div class="flex-grid">
                    <div class="row">
                        <div class="cell size12">
                            <label>Question Type : </label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="cell size12">
                            <div class="input-control full-size">
                                <input type="text" id="txtQuestionType" readonly value="<?php echo $QuestionType; ?>">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="cell size12">
                            <label>Question : </label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="cell size12">
                            <div class="input-control textarea full-size">
                                <textarea id="txtQuestion" <?php if($hascode) echo 'readonly' ?>><?php echo $question; ?></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="row" style="<?php if($QuestionType == 'True/False') echo 'display:none;'; ?>">
                        <div class="cell size12">
                            <label>Choices :</label>
                        </div>
                    </div>
                    <div class="row" style="<?php if($QuestionType == 'True/False') echo 'display:none;'; ?>">
                        <div class="cell size12" id="AnswerList">
                            <table id="tblAnswers" class="table border bordered hovered">
                                <thead>
                                    <th style="width: 5px">Letter</th>
                                    <th>Description</th>
                                </thead>
                                <tbody>
                                    <?php echo $tr; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="row">
                        <div class="cell size12">
                            <label>Correct Answer:</label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="cell size12">
                            <div class="input-control select full-size">
                                <select id="txtAnswer" <?php if($hascode) echo 'disabled' ?>>
                                    <option value=""></option>
                                    <?php if($QuestionType == 'True/False') { ?>
                                    <option value="True" <?php echo ($answer == 'True' ? 'selected' : ''); ?>>True</option>
                                    <option value="False" <?php echo ($answer == 'False' ? 'selected' : ''); ?>>False</option>
                                    <?php } else { ?>
                                    <option value="A" <?php echo ($answer == 'A' ? 'selected' : '') ?>>A</option>
                                    <option value="B" <?php echo ($answer == 'B' ? 'selected' : '') ?>>B</option>
                                    <option value="C" <?php echo ($answer == 'C' ? 'selected' : '') ?>>C</option>
                                    <option value="D" <?php echo ($answer == 'D' ? 'selected' : '') ?>>D</option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <?php if(!$hascode) { ?>
                    <div class="row flex-just-end">
                        <div class="cell size3">
                            <button id="btnSaveQuestion" class="button primary full-size">Save</button>
                        </div>
                    </div>
                    <?php } ?>
                </div>
            </div>
            <?php
        } else {
            ?>
            <script>
                $('#btnSaveQuestion').click(function(){
                    var question_id = <?php echo $id; ?>;
                    var examid = <?php echo $ExamID; ?>;
                    var question_type = "<?php echo $QuestionType; ?>";
                    var question = $('#txtQuestion')[0].value;
                    var option_a = '';
                    var option_b = '';
                    var option_c = '';
                    var option_d = '';
                    var question_answer = $('#txtAnswer')[0].value;
                    
                    <?php if($QuestionType != 'True/False') { ?>
                        option_a = $('#txtQOptionA')[0].value;
                        option_b = $('#txtQOptionB')[0].value;
                        option_c = $('#txtQOptionC')[0].value;
                        option_d = $('#txtQOptionD')[0].value;
                    <?php } ?>
                    
                    if (question_type == 'True/False' && (question == '' || question_answer == '')) {
                        $.Notify({
                            caption : 'Saving Failed',
                            content : 'Please enter Question and the correct answer.',
                            type : 'alert',
                            timeout : 6000
                        });
                    } else if (question_type != 'True/False' && (question == '' || option_a == '' || option_b == '' || option_c == '' || option_d == '' || question_answer == '')) {
                        $.Notify({
                            caption : 'Saving Failed',
                            content : 'Please enter a Question, complete the Question Options and the select the Correct Answer.',
                            type : 'alert',
                            timeout : 6000
                        });
                    } else {
                        $.post(
                            'save_data.php', {
                                save_question : question_id,
                                ExamId : examid,
                                txtQType : question_type,
                                txtQuestion : question,
                                txtOptionA : option_a,
                                txtOptionB : option_b,
                                txtOptionC : option_c,
                                txtOptionD : option_d,
//                                tblOption : question_options,
                                txtAnswer : question_answer
                            },function(data) {
                                GenerateTableQuestion();
                                
                                $.Notify({
                                    caption : 'Saving Complete',
                                    content : 'Question was successfully saved.',
                                    type : 'success',
                                    timeout : 6000
                                });
                                
                                metroDialog.close('#frmQuestion');
                            }
                        );
                    }
                });
            </script>
            <div class="cell size12">
                <div class="flex-grid">
                    <div class="row">
                        <div class="cell size12">
                            <label>Question Type : </label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="cell size12">
                            <div class="input-control full-size">
                                <input type="text" id="txtQuestionType" readonly value="<?php echo $QuestionType; ?>">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="cell size12">
                            <label>Question : </label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="cell size12">
                            <div class="input-control textarea full-size">
                                <textarea id="txtQuestion" <?php if($hascode) echo 'readonly' ?>></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="row" style="<?php if($QuestionType == 'True/False') echo 'display:none;'; ?>">
                        <div class="cell size12">
                            <label>Choices :</label>
                        </div>
                    </div>
                    <div class="row" style="<?php if($QuestionType == 'True/False') echo 'display:none;'; ?>">
                        <div class="cell size12" id="AnswerList">
                            <table id="tblAnswers" class="table border bordered hovered">
                                <thead>
                                    <th style="width: 5px">Letter</th>
                                    <th>Description</th>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="align-center">A</td>
                                        <td class="no-padding">
                                        <div class="input-control no-margin textarea full-size">
                                            <textarea id="txtQOptionA"></textarea>
                                        </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="align-center">B</td>
                                        <td class="no-padding">
                                        <div class="input-control no-margin textarea full-size">
                                            <textarea id="txtQOptionB"></textarea>
                                        </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="align-center">C</td>
                                        <td class="no-padding">
                                        <div class="input-control no-margin textarea full-size">
                                            <textarea id="txtQOptionC"></textarea>
                                        </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="align-center">D</td>
                                        <td class="no-padding">
                                        <div class="input-control no-margin textarea full-size">
                                            <textarea id="txtQOptionD"></textarea>
                                        </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="row">
                        <div class="cell size12">
                            <label>Correct Answer:</label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="cell size12">
                            <div class="input-control select full-size">
                                <select id="txtAnswer" <?php if($hascode) echo 'disabled' ?>>
                                    <option value=""></option>
                                    <?php if($QuestionType == 'True/False') { ?>
                                    <option value="True" >True</option>
                                    <option value="False" >False</option>
                                    <?php } else { ?>
                                    <option value="A" >A</option>
                                    <option value="B" >B</option>
                                    <option value="C" >C</option>
                                    <option value="D" >D</option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <?php if(!$hascode) { ?>
                    <div class="row flex-just-end">
                        <div class="cell size3">
                            <button id="btnSaveQuestion" class="button primary full-size">Save</button>
                        </div>
                    </div>
                    <?php } ?>
                </div>
            </div>
            <?php
        }
    }
    
    elseif (isset($_POST['save_question'])) {
//        $tblOption = $cn->escape($_POST['tblOption']);
        $qid = $cn->escape($_POST['save_question']);
        $ExamId = $cn->escape($_POST['ExamId']);
        $txtQType = $cn->escape($_POST['txtQType']);
        $txtQuestion = $cn->escape($_POST['txtQuestion']);
        $txtOptionA = $cn->escape($_POST['txtOptionA']);
        $txtOptionB = $cn->escape($_POST['txtOptionB']);
        $txtOptionC = $cn->escape($_POST['txtOptionC']);
        $txtOptionD = $cn->escape($_POST['txtOptionD']);
        $txtAnswer = $cn->escape($_POST['txtAnswer']);
        
        $cn->query("CALL sp_add_edit_delete_question(1, $ExamId, $qid, '$txtQType', '$txtQuestion', '$txtAnswer', $instr_id)");
        
        if ($txtQType != 'True/False') {
            $cn->query("CALL sp_update_question_option($ExamId, $qid, 'A', '$txtOptionA', $instr_id)");
            $cn->query("CALL sp_update_question_option($ExamId, $qid, 'B', '$txtOptionB', $instr_id)");
            $cn->query("CALL sp_update_question_option($ExamId, $qid, 'C', '$txtOptionC', $instr_id)");
            $cn->query("CALL sp_update_question_option($ExamId, $qid, 'D', '$txtOptionD', $instr_id)");
        }
    }
    
    elseif (isset($_POST['delete_question'])) {
        $qid = $cn->escape($_POST['delete_question']);
        $examid = $cn->escape($_POST['examid']);
        
        $cn->query("CALL sp_add_edit_delete_question(2, $examid, $qid, '', '', '', $instr_id)");
    }

    elseif (isset($_POST['GetQuestions'])) {
        $examid = $cn->escape($_POST['GetQuestions']);
        
        $cn->query("SELECT 1 FROM exam_code WHERE exam_id = $examid");
        $hascode = $cn->hasrow();
        $cn->CloseRecordset();
        
        $cn->query("SELECT * FROM exam_question WHERE examid = $examid AND tech_id = $instr_id");
        $exam_no = 1; 
        while($row = $cn->getrow()) {
    ?>
        <tr class="disable-selection" ondblclick="NewEditQuestion(<?php echo $row['id']; ?>,'<?php echo $row['question_type']; ?>')">
            <td><?php echo $exam_no++; ?></td>
            <td><?php echo $row['question_type']; ?></td>
            <td><?php echo $row['question']; ?></td>
            <td><?php echo $row['answer']; ?></td>
            <?php if(!$hascode) { ?>
            <td class="align-center v-align-center" style="width: 5px"><a href="#" onclick="DeleteQuestion(<?php echo $row['id']; ?>)" title="Delete Question"><span class="mif-cross fg-red"></span></a></td>
            <?php } ?>
        </tr>
    <?php }
    }
    
    elseif (isset($_POST['genExamCode'])) {
        $examid = $cn->escape($_POST['exam']);
        $dteDate = $cn->escape($_POST['dteDate']);
        $tmeTime = $cn->escape($_POST['tmeTime']);
        $numHour = $cn->escape($_POST['numHour']);
        
        $cn->query("CALL sp_generate_exam_code($examid, '$dteDate $tmeTime', $numHour, $instr_id)", true);
    }
    
    elseif (isset($_POST['get_examinee'])) {
        $examid = $cn->escape($_POST['get_examinee']);
        
        $cn->query("SELECT * FROM vw_exam_examinees WHERE exam_code = '$examid'");
        while($row = $cn->getrow()) { ?>
            <tr>
                <td><?php echo $row['course']; ?></td>
                <td><?php echo $row['sect']; ?></td>
                <td><?php echo $row['studentno']; ?></td>
                <td><?php echo $row['fullname']; ?></td>
                <td><?php echo $row['score'] . '/' . $row['totalscore']; ?></td>
                <td class="align-center"><a href="#" onclick="window.open('../reports/examsheet.php?exam=<?php echo $row['exam_id']; ?>&code=<?php echo $row['exam_code']; ?>&stno=<?php echo $row['studentno']; ?>', '_blank', 'width = 1000px, height = 500px');" title="Load Examinees"><span class="mif-file-empty" title="View Answer Sheet"></span></a></td>
            </tr>
        <?php }
    }

    $cn->CloseConnection();
    
?>