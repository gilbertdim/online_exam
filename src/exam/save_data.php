<?php
    session_start();
    
    include "../db/clsconnection.php";
    $cn = New Connection;

    $cn->OpenConnection();
    
    if(isset($_POST['save_student_info'])) {
        $examid = $cn->escape($_POST['examid']);
        $examcode = $cn->escape($_POST['examcode']);
        $st_number = $cn->escape($_POST['st_number']);
        $st_fname = $cn->escape($_POST['st_fname']);
        $st_lname = $cn->escape($_POST['st_lname']);
        $st_section = $cn->escape($_POST['st_section']);
        $st_course = $cn->escape($_POST['st_course']);
        
        $cn->query("CALL sp_create_student_exam_profile($examid, '$examcode', '$st_number', '$st_fname', '$st_lname', '$st_section', '$st_course')");
        
        $row = $cn->getrow();
        
        echo $row['id'];
        
        die();
    }
    else if(isset($_POST['check_exam_code'])) {
        $exam_code = $cn->escape($_POST['check_exam_code']);
        $examinee_code = $cn->escape($_POST['examinee_code']);
        
        // $cn->query("SELECT * FROM exam_code WHERE exam_code = '$exam_code' AND dtestart <= now() AND dteend > now()");
        $sql = "CALL sp_check_exam_examinee('$exam_code', '$examinee_code')";

        $cn->query($sql);
        
        if($cn->hasrow()) {
            $row = $cn->getrow();
            if ($row['student_id'] > 0) {
                $_SESSION['examinee_code'] = $examinee_code;
                // echo 'proceed';
                echo json_encode($row);
                die();
            }
        }
    }
    else if(isset($_POST['get_question'])) {
        $get_question = $cn->escape($_POST['get_question']);
        $exam = $cn->escape($_POST['exam']);
        $examcode = $cn->escape($_POST['examcode']);
        $examinee = $cn->escape($_POST['examinee']);
        
        $cn->query("CALL sp_select_exam_question($exam, '$examcode', $get_question, $examinee)");
        
        if($cn->hasrow()) {
            $row = $cn->getrow();
    ?>
        <script>
            $('#btnSaveAnswer').click(function(){
                var stanswer = $('#optAnswer<?php echo $get_question; ?>')[0].value;
                
                if (stanswer != '') { 
                    $.post(
                        'save_data.php',{
                            save_question_answer : <?php echo $get_question; ?>,
                            answer : stanswer,
                            examid : <?php echo $exam; ?>,
                            examcode : '<?php echo $examcode; ?>',
                            student : <?php echo $examinee; ?>,
                        },function(data){
                            if (data > '') {
                                ret = JSON.parse(data);
                                if (ret.updated > 0) {
                                    $.Notify({
                                        caption : 'Saving Complete',
                                        content : 'Answer was successfully saved',
                                        type : 'success',
                                        timeout : 6000
                                    });
                                } else {
                                    $.Notify({
                                        caption : 'Saving Failed',
                                        content : 'Answer not saved',
                                        type : 'alert',
                                        timeout : 1000
                                    });
                                    location.href = 'http://online_exam.mpglobalservices.net';
                                }
                            }
                        }
                    );
                } else {
                    $.Notify({
                        caption : 'Saving Failed',
                        content : 'Please select the correct answer.',
                        type : 'alert',
                        timeout : 6000
                    });
                }
            });
        </script>
        <div class="row">
            <div class="cell size12">
                <div class="flex-grid">
                    <div class="row">
                        <div class="cell size12">
                            <p><?php echo $row['question']; ?></p>
                        </div>
                    </div>
                    <?php
                        $cn->CloseRecordset();
                        $cn->query("SELECT * FROM exam_option WHERE examid = $exam AND questionid = $get_question");
                        while($opt = $cn->getrow()) {
                    ?>
                    <div class="row">
                        <div class="cell size12">
                            <p class="no-margin-top"><b><?php echo $opt['option_letter']; ?></b>. <?php echo $opt['descrip']; ?></p>
                        </div>
                    </div>
                    <?php } ?>
                    <div class="row">
                        <div class="cell size1">
                            <h5>Answer:</h5>
                        </div>
                        <div class="cell size3">
                            <div class="input-control select full-size">
                                <select id="optAnswer<?php echo $get_question; ?>">
                                <?php if($row['question_type'] == 'Multiple Choice') { ?>
                                    <option value=""></option>
                                    <option value="A" <?php echo ($row['answer'] == 'A' ? 'selected' : ''); ?>>A</option>
                                    <option value="B" <?php echo ($row['answer'] == 'B' ? 'selected' : ''); ?>>B</option>
                                    <option value="C" <?php echo ($row['answer'] == 'C' ? 'selected' : ''); ?>>C</option>
                                    <option value="D" <?php echo ($row['answer'] == 'D' ? 'selected' : ''); ?>>D</option>
                                <?php } else if ($row['question_type'] == 'True/False') { ?>
                                    <option value=""></option>
                                    <option value="True" <?php echo ($row['answer'] == 'True' ? 'selected' : ''); ?>>True</option>
                                    <option value="False" <?php echo ($row['answer'] == 'False' ? 'selected' : ''); ?>>False</option>
                                <?php } ?>
                                </select>
                                <button class="button primary" id="btnSaveAnswer">Save <span class="mif-chevron-right icon"></span></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php
        }
    }
    else if(isset($_POST['save_question_answer'])) {
        $questionid = $cn->escape($_POST['save_question_answer']);
        $answer = $cn->escape($_POST['answer']);
        $examid = $cn->escape($_POST['examid']);
        $examcode = $cn->escape($_POST['examcode']);
        $student = $cn->escape($_POST['student']);
        
        $cn->query("CALL sp_update_student_exam_answer($examid, '$examcode', $questionid, $student, '$answer')");

        if ($cn->hasrow()) {
            $row = $cn->getrow();
            echo json_encode($row);
        } else {
            $_SESSION['examinee_code'] = '';
        }
    }
    else if(isset($_POST['set_finish_time'])) {
        $_SESSION['examinee_code'] = '';
        // $exam = $cn->escape($_POST['exam']);
        // $examcode = $cn->escape($_POST['examcode']);
        // $examinee = $cn->escape($_POST['examinee']);

        // $cn->CloseRecordset();
        // $sql = "CALL sp_set_student_exam_finish_date($exam, $examinee)";
        // $cn->query($sql, true);
        // $cn->query("CALL sp_set_student_exam_finish_date($exam, $examinee)", true);
    }
?>