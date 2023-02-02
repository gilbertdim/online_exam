<?php 
    session_start();

    include "../../db/clsconnection.php";
    $cn = New Connection;
    
    $cn->OpenConnection();

    $instr_id = 0; $examid = 0; $exam = array(); $hascode = false;
    if(isset($_SESSION['instr_id'])) $instr_id = $_SESSION['instr_id'];
    
    if(isset($_GET['id'])) {
        $examid = $cn->escape($_GET['id']);
        $cn->query("SELECT * FROM exam_head WHERE id = $examid AND tech_id = $instr_id");
        $hasrow = $cn->hasrow();
        if($hasrow) $exam = $cn->getrow();        
        $cn->CloseRecordset();
        
        $cn->query("SELECT 1 FROM exam_code WHERE exam_id = $examid");
        $hascode = $cn->hasrow();
        $cn->CloseRecordset();        
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Online Exam</title>
    
    <link href="../../content/css/metro-responsive.css" rel="stylesheet">
    <link href="../../content/css/metro-colors.css" rel="stylesheet">
    <link href="../../content/css/metro-schemes.css" rel="stylesheet">
    <link href="../../content/css/metro-icons.css" rel="stylesheet">
    <link href="../../content/css/metro.css" rel="stylesheet">
    
    <script src="../../content/js/jquery-2.1.3.min.js"></script>
    <script src="../../content/js/dateFormat.js"></script>
    <script src="../../content/js/jquery.dataTables.min.js"></script>
    <script src="../../content/js/metro.js"></script>
    
    <style>
        .disable-selection {
            -moz-user-select: none;
            -ms-user-select: none;
            -khtml-user-select: none;
            -webkit-user-select: none;
            -webkit-touch-callout: none;
        }

    </style>
</head>
<body>
    <script>
        var tblmain;
        var tblexaminee;
        
        <?php if (!isset($_GET['id']) || !isset($_SESSION['instr_id']) || !$hasrow) { ?>
            if(window.name == 'newwindow') {
                window.close();
            } else {
                window.location = "../";
            }
        <?php } ?>
       
        $(function(){
            $('#btnHome').click(function(){
                opener.GetData('Exam');
                
                if(window.name == 'newwindow') {
                    window.close();
                } else {
                    window.location = "../";
                }
            });
            
            tblmain = $('.table.tblmain').dataTable({
                paging : false
            });
            tblexaminee = $('.table.tblexaminee').dataTable({
                paging : false
            });
            
            $('.table.tblVoucher').dataTable({
                paging : false
            });
            $('.tabcontrol').tabcontrol();
            
            <?php if(!$hascode) { ?>
            $('#btnProceedDelete').click(function(){
                var examid = <?php echo $examid; ?>;
                
                $.post(
                    'save_data.php', {
                        delete_exam : examid
                    },function(data){
                        $('#btnHome').click();
                    }
                );
                
            });
            
            $('#btnExamUpdate').click(function(){
                var title = $('#txtExamTitle')[0].value;
                var descrip = $('#txtExamDescrip')[0].value;
                var instruction = $('#txtExamInstruction')[0].value;
                var subj = $('#txtExamSubj')[0].value;
                
                var examid = <?php echo $examid; ?>;
                
                if (title.trim() == '' || descrip.trim() == '' || instruction.trim() == '') {
                    $.Notify({
                        caption : 'Updating Failed',
                        content : 'Please enter the exam title, description and instruction.',
                        type : 'alert',
                        timeout : 6000
                    });
                } else {
                    $.post(
                        'save_data.php', {
                            update_exam : examid,
                            ExamSubj : subj,
                            ExamTitle : title,
                            ExamDescrip : descrip,
                            ExamInstruction : instruction
                        }, function(data) {
                            $.Notify({
                                caption : 'Updating Complete',
                                content : 'Exam information was successfully updated.',
                                type : 'success',
                                timeout : 6000
                            });
                        }
                    );
                }
            });
            
            $('#btnConfirmDeleteQuestion').click(function(){
                var exam_id = <?php echo $examid; ?>;
                
                $.post(
                    'save_data.php',{
                        delete_question : $('#btnConfirmDeleteQuestion')[0].value,
                        examid : exam_id
                    },function(data){
                        metroDialog.close('#frmDeleteQuestion');
                        GenerateTableQuestion();
                        
                        $.Notify({
                            caption : 'Deleting Complete',
                            content : 'Exam question was successfully deleted.',
                            type : 'success',
                            timeout : 6000
                        });
                    }
                );
            });
            <?php } ?>
            
            GenerateTableQuestion();
        });
        
        function NewEditQuestion(id, type = '') {
            var examid = <?php echo $examid; ?>;
            
            $.post(
                'save_data.php',{
                    NewEditQuestion : id,
                    ExamID : examid,
                    QuestionType : type
                },function(data){
                    $('#frmQuestion_Data').html(data);
                    console.log(data);
                }
            );
            
            metroDialog.open('#frmQuestion');
        }
        
        function GenerateTableQuestion() {
            var examid = <?php echo $examid; ?>;
            
            tblmain.fnDestroy();
            
            $.post(
                'save_data.php', {
                    GetQuestions : examid
                }, function(data) {
                    $('#tblQuestion_rows').html(data);
                    
                    tblmain = $('.table.tblmain').dataTable({
                        paging : false
                    });
                }
            );
        }
        
        function GenerateTableExaminee(id) {
            tblexaminee.fnDestroy();

            $.post(
                'save_data.php', {
                    get_examinee : id
                },function(data) {
                    $('#tblExaminee_rows').html(data);
                    
                    tblexaminee = $('.table.tblexaminee').dataTable({
                        paging : false
                    });
                }
            );
        }
        
        <?php if(!$hascode) { ?>
        function DeleteQuestion(id) {
            $('#btnConfirmDeleteQuestion')[0].value = id;
            metroDialog.open('#frmDeleteQuestion');
        }
        <?php } ?>
    </script>
    <div class="app-bar">
        <a id="btnHome" href="#" class="app-bar-element"><span class="mif-home icon"></span></a>
        <span class="app-bar-divider"></span>
        <label class="app-bar-element">Edit Exam Details</label>
        <?php if(!$hascode) { ?>
        <a class="app-bar-element place-right" href="#" onclick="metroDialog.open('#frmDeleteExam')" id="btnDelete"><span class="mif-bin fg-red"></span></a>
        <?php } else { ?>
        <a class="app-bar-element place-right" href="#" onclick="window.open('../reports/examsummary.php?exam=<?php echo $examid; ?>', '_blank', 'width = 1000px, height = 500px');" title="Print Score Summary"><span class="mif-print"></span></a>
        <?php } ?>
    </div>
    <div class="tabcontrol">
        <ul class="tabs">
            <li><a href="#ExamInfo">Exam Information</a></li>
            <li><a href="#ExamQuestion">Questionnaire</a></li>
            <li><a href="#ExamTaker">Examinees</a></li>
        </ul>
        <div class="frames">
            <div class="frame bg-white" id="ExamInfo">
                <div class="flex-grid">
                    <div class="row">
                        <div class="cell size3">
                            <h5>Subject</h5>
                        </div>
                        <div class="cell auto-size">
                            <div class="input-control text full-size">
                                <input type="text" id="txtExamSubj"  value="<?php echo $exam['subj']; ?>"
                                <?php if($hascode) echo 'readonly' ?>>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="cell size3">
                            <h5>Title</h5>
                        </div>
                        <div class="cell auto-size">
                            <div class="input-control text full-size">
                                <input type="text" id="txtExamTitle" value="<?php echo $exam['title']; ?>"
                                <?php if($hascode) echo 'readonly' ?>>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="cell size3">
                            <h5>Description</h5>
                        </div>
                        <div class="cell auto-size">
                            <div class="input-control textarea full-size">
                                <textarea rows="10" id="txtExamDescrip" <?php if($hascode) echo 'readonly' ?>><?php echo $exam['descrip']; ?></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="cell size3">
                            <h5>Instruction</h5>
                        </div>
                        <div class="cell auto-size">
                            <div class="input-control textarea full-size">
                                <textarea rows="10" id="txtExamInstruction" <?php if($hascode) echo 'readonly' ?>><?php echo $exam['instruction']; ?></textarea>
                            </div>
                        </div>
                    </div>
                    <?php if(!$hascode) { ?>
                    <div class="row flex-just-end">
                        <div class="cell size3">
                            <button id="btnExamUpdate" class="button primary full-size">Update</button>
                        </div>
                    </div>
                    <?php } ?>
                </div>
            </div>
            <div class="frame bg-white no-padding" id="ExamQuestion">
                <?php if(!$hascode) { ?>
                <button class="button warning dropdown-toggle place-left">New Question</button>
                <ul class="d-menu" data-role="dropdown" style="margin-top: 40px">
                    <li class="menu-title">Question Type</li>
                    <li><a href="#" onclick="NewEditQuestion(0, 'True/False')">True/False</a></li>
                    <li><a href="#" onclick="NewEditQuestion(0, 'Multiple Choice')">Multiple Choice</a></li>
<!--                    <li><a href="#" onclick="NewEditQuestion(0, 'Ordering')">Ordering</a></li>-->
<!--                    <li><a href="#">Short Answer/Essay</a></li>-->
<!--                    <li><a href="#" onclick="NewEditQuestion(0, 'Fill-in-the-blank')">Fill-in-the-blank</a></li>-->
<!--                    <li><a href="#" onclick="NewEditQuestion(0, 'Matching')">Matching</a></li>-->
                </ul>
                <?php } ?>
                <table class="tblmain table border bordered hovered">
                    <thead>
                        <th style="width: 65px">#</th>
                        <th>Type</th>
                        <th>Question</th>
                        <th>Answer</th>
                        <?php if(!$hascode) { ?>
                        <th style="width: 5px"></th>
                        <?php } ?>
                    </thead>
                    <tbody id="tblQuestion_rows">
                    </tbody>
                </table>
            </div>
            <div class="frame bg-white no-padding" id="ExamTaker">
                <div class="flex-grid">
                    <div class="row">
                        <div class="cell size4">
                            <table class="tblVoucher table border bordered hovered">
                                <thead>
                                    <th>Code</th>
                                    <th>Start</th>
                                    <th>End Date</th>
                                    <th style="width: 50px"></th>
                                </thead>
                                <tbody>
                                    <?php
                                        $cn->CloseRecordset();
                                        $cn->query("SELECT * FROM exam_code WHERE exam_id = $examid");
                                        while($row = $cn->getrow()) {
                                    ?>
                                    <tr ondblclick="GenerateTableExaminee('<?php echo $row['exam_code']; ?>')">
                                        <td><?php echo $row['exam_code'] ?></td>
                                        <td><?php echo date_format(date_create($row['dtestart']), 'M d, Y h:i A') ?></td>
                                        <td><?php echo date_format(date_create($row['dteend']), 'M d, Y h:i A') ?></td>
                                        <td class="align-center"><a href="#" onclick="window.open('../reports/examsheet.php?exam=<?php echo $row['exam_id']; ?>&code=<?php echo $row['exam_code']; ?>', '_blank', 'width = 1000px, height = 500px');" title="Load Examinees"><span class="mif-file-empty" title="View Answer Sheet"></span></a></td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="cell size8">
                            <table class="tblexaminee table border bordered hovered">
                                <thead>
                                    <th>Course</th>
                                    <th>Section</th>
                                    <th>Student No</th>
                                    <th>Name</th>
                                    <th>Score</th>
                                    <th style="width: 50px"></th>
                                </thead>
                                <tbody id="tblExaminee_rows">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div id="frmQuestion" data-role="dialog" data-overlay="true" data-close-button="true" style="max-height: 450px">
        <div class="flex-grid padding10">
            <div class="row">
                <div class="cell size12">
                    <h1 class="text-light">New Multiple Choice Question</h1>
                    <hr class="thin">
                </div>
            </div>
            <div id="frmQuestion_Data" class="row" style="max-height: 360px; overflow-x: auto; overflow-y: none;">
                <div class="cell size12">
                    <div class="flex-grid">
                        <div class="row">
                            <div class="cell size12">
                                <label>Question : </label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="cell size12">
                                <div class="input-control textarea full-size">
                                    <textarea id="txtQuestion"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="cell size12">
                                <label>Choices :</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="cell size12" id="AnswerList">
                                <table id="tblAnswers" class="table border bordered hovered">
                                    <thead>
                                        <th>Letter</th>
                                        <th>Description</th>
                                        <th style="width: 25px"><a href="#" onclick="metroDialog.open('#frmAddChoices')"><span class="mif-plus icon"></span></a></th>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>A</td>
                                            <td>Lorem ipsum dolor sit amet.</td>
                                            <td><a href="#"><span class="mif-cross fg-red icon"></span></a></td>
                                        </tr>
                                        <tr>
                                            <td>B</td>
                                            <td>Lorem ipsum dolor sit amet.</td>
                                            <td><a href="#"><span class="mif-cross fg-red icon"></span></a></td>
                                        </tr>
                                        <tr>
                                            <td>C</td>
                                            <td>Lorem ipsum dolor sit amet.</td>
                                            <td><a href="#"><span class="mif-cross fg-red icon"></span></a></td>
                                        </tr>
                                        <tr>
                                            <td>D</td>
                                            <td>Lorem ipsum dolor sit amet.</td>
                                            <td><a href="#"><span class="mif-cross fg-red icon"></span></a></td>
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
                                <div class="input-control text full-size">
                                    <input type="text" id="txtAnswer">
                                </div>
                            </div>
                        </div>
                        <div class="row flex-just-end">
                            <div class="cell size3">
                                <button id="btnSaveQuestion" class="button primary full-size">Save</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div id="frmAddChoices" data-role="dialog" data-overlat="true" data-close-button="true">
        <div class="flex-grid padding10">
            <div class="row">
                <div class="cell size12">
                    <h3 class="text-light">Choice Item</h3>
                    <hr class="thin">
                </div>
            </div>
            <div class="row">
                <div class="cell size12">
                    <label>Description</label>
                </div>
            </div>
            <div class="row">
                <div class="cell size12">
                    <div class="input-control textarea full-size">
                        <textarea id="txtNewOption"></textarea>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="cell size12">
                    <button id="btnSaveNewOption" class="button primary full-size">Add</button>
                </div>
            </div>
        </div>
    </div>
    
    <div id="frmDeleteQuestion" data-role="dialog" data-overlay="true">
        <div class="window-content">
            <div class="flex-grid padding10">
                <div class="row">
                    <div class="cell size12">
                        <h2 class="text-light">Delete Question</h2>
                        <hr class="thin">
                        <p>Do you want to delete this question?</p>
                    </div>
                </div>
                <div class="row flex-just-sb">
                    <div class="cell size3">
                        <button class="button full-size" onclick="metroDialog.close('#frmDeleteQuestion')">Cancel</button>
                    </div>
                    <div class="cell size3">
                        <button id="btnConfirmDeleteQuestion" class="button alert full-size">Delete</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div id="frmDeleteExam" data-role="dialog" data-overlay="true">
        <div class="window-content">
            <div class="flex-grid padding10">
                <div class="row">
                    <div class="cell size12">
                        <h2 class="text-light">Delete Exam</h2>
                        <hr class="thin">
                        <p>Do you want to delete this exam?</p>
                    </div>
                </div>
                <div class="row flex-just-sb">
                    <div class="cell size3">
                        <button class="button full-size" onclick="metroDialog.close('#frmDeleteExam')">Cancel</button>
                    </div>
                    <div class="cell size3">
                        <button id="btnProceedDelete" class="button alert full-size">Delete</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>