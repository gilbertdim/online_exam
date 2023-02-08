<?php 
    session_start();
    if ((null == $_SESSION['examinee_code']) || ($_SESSION['examinee_code'] == '')) {
        header('Location: ../');
    }
    include "../db/clsconnection.php";
    $cn = New Connection;

    if (!isset($_GET['id'])) header('Location: ../');
    
    $cn->OpenConnection();
    
    $exam_code = $cn->escape($_GET['id']);
    $sid = $cn->escape($_GET['sid']);
    
    $cn->query("SELECT * FROM exam_code WHERE exam_code = '$exam_code'");
    $ecode = $cn->getrow();
    $examid = $ecode['exam_id'];
    
    $cn->query("SELECT * FROM vw_exams_with_instructor WHERE id = $examid");
    $exam = $cn->getrow();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Online Exam</title>
    
    <link href="../content/css/metro-responsive.css" rel="stylesheet">
    <link href="../content/css/metro-colors.css" rel="stylesheet">
    <link href="../content/css/metro-schemes.css" rel="stylesheet">
    <link href="../content/css/metro-icons.css" rel="stylesheet">
    <link href="../content/css/metro.css" rel="stylesheet">
    
    <script src="../content/js/jquery-2.1.3.min.js"></script>
    <script src="../content/js/dateFormat.js"></script>
    <script src="../content/js/jquery.dataTables.min.js"></script>
    <script src="../content/js/metro.js"></script>
</head>
<body>
    <script>
        $(function(){
            $('.sidebar2>li>a').click(function(){
                if($('#txtStNumber')[0].value != '' && $('#txtStfName')[0].value != '' && $('#txtStlName')[0].value != '' && $('#txtStSection')[0].value != '') {
                    if ($('#btnStSave')[0].value == 0) {
                        $.Notify({
                            caption : 'Examination Alert',
                            content : 'Please click Save button to proceed.',
                            type : 'alert',
                            timeout : 6000
                        });
                    } else if ($(this)[0].text.trim() == 'Exit') {
                        $.post(
                            'save_data.php', {
                                set_finish_time : '',
                                exam : <?php echo $examid; ?>,
                                examcode : '<?php echo $exam_code; ?>',
                                examinee : $('#btnStSave')[0].value
                            }, function(data) {
                                window.location = '../';
                            }
                        );
                        window.location = '../';
                    } else {
                        $('.sidebar2>li').removeClass('active');

                        $(this).parent().addClass('active');
                        
                        if (this.name != '') {
                            $('#StudentInfo').css('display', 'none');
                            $('#QuestionInfo').css('display', 'block');
                            
                            $('#QuestionInfo h1').html($(this)[0].text.trim());

                            $.post(
                                'save_data.php', {
                                    get_question : this.name,
                                    exam : <?php echo $examid; ?>,
                                    examcode : '<?php echo $exam_code; ?>',
                                    examinee : $('#btnStSave')[0].value
                                }, function(data) {
                                    $('#QuestionDetail').html(data);
                                }
                            );
                        } else {
                            $('#StudentInfo').css('display', 'block');
                            $('#QuestionInfo').css('display', 'none');
                        }
                    }
                } else {
                    $.Notify({
                        caption : 'Examination Alert',
                        content : 'Please fill the student information.',
                        type : 'alert',
                        timeout : 6000
                    });
                }
            });
            
            $('#btnStSave').click(function(){
                if($('#txtStNumber')[0].value != '' && $('#txtStfName')[0].value != '' && $('#txtStlName')[0].value != '' && $('#txtStSection')[0].value != '' && $('#txtStCourse')[0].value != '') {
                    if ($('#btnStSave')[0].value == 0) {
                        $.post(
                            'save_data.php', {
                                save_student_info : '',
                                examid : <?php echo $examid; ?>,
                                examcode : '<?php echo $exam_code; ?>',
                                st_number : $('#txtStNumber')[0].value,
                                st_fname : $('#txtStfName')[0].value,
                                st_lname : $('#txtStlName')[0].value,
                                st_section : $('#txtStSection')[0].value,
                                st_course : $('#txtStCourse')[0].value
                            }, function(data) {
                                $('#btnStSave')[0].value = data.trim();
                                
                                $.Notify({
                                    caption : 'Examination Alert',
                                    content : 'Proceed to answer all the exam questionaires.',
                                    type : 'success',
                                    timeout : 6000
                                });
                            }
                        );
                    }
                } else {
                    $.Notify({
                        caption : 'Examination Alert',
                        content : 'Please fill the student information.',
                        type : 'alert',
                        timeout : 6000
                    });
                }
            })
        });
    </script>
    <div class="flex-grid no-responsive-future">
        <div class="row bg-darkCyan" style="border-bottom: 1px gray solid">
            <div class="cell size12 fg-white">
                <div class="flex-grid padding10">
                    <div class="row">
                        <div class="cell size12">
                            <div class="place-right"><h3>Exam Ends @ <?php echo $ecode['dteend']; ?></h3></div>
                            <h2><?php echo $exam['title'] ?></h2>
                            <h3 class="text-light">Instructor Name : <?php echo $exam['instructor']; ?></h3>
                            <h4 class="text-light">Subject : <?php echo $exam['subj'] ?></h4>
                            <p class="no-margin"><b>Instruction :</b> <?php echo $exam['instruction']; ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
            $cn->query("SELECT * FROM vw_rand_question WHERE examid = $examid ORDER BY num");
        ?>
        <div class="row">
            <div class="cell size2">
                <ul class="sidebar2">
                    <li class="active"><a href="#"><span class="mif-user icon"></span> Student Profile</a></li>
                    <?php $i = 1; while($row = $cn->getrow()) { ?>
                        <li><a href="#" onclick="" name="<?php echo $row['id'] ?>"><span class="mif-question icon"></span> Question <?php echo $i++; ?></a></li>
                    <?php } ?>
<!--
                    <li><a href="#"><span class="mif-question icon"></span> Question 2</a></li>
                    <li><a href="#"><span class="mif-question icon"></span> Question 3</a></li>
                    <li><a href="#"><span class="mif-question icon"></span> Question 4</a></li>
                    <li><a href="#"><span class="mif-question icon"></span> Question 5</a></li>
                    <li><a href="#"><span class="mif-question icon"></span> Question 6</a></li>
                    <li><a href="#"><span class="mif-question icon"></span> Question 7</a></li>
                    <li><a href="#"><span class="mif-question icon"></span> Question 8</a></li>
                    <li><a href="#"><span class="mif-question icon"></span> Question 9</a></li>
                    <li><a href="#"><span class="mif-question icon"></span> Question 10</a></li>
-->
                    <li><a class="fg-white" style="background-color: #ce352c" href="#"><span class="mif-exit icon"></span> Exit</a></li>
                </ul>
            </div> 
            <div class="cell auto-size" id="StudentInfo">
                <div class="flex-grid padding10 no-padding-top">
                    <div class="row">
                        <div class="cell size12">
                            <h1 class="text-light">Student Information</h1>
                            <hr class="thin">
                        </div>
                    </div>
                    <div class="row">
                        <div class="cell size12">
                            <div class="flex-grid">
                                <div class="row">
                                    <div class="cell size3">
                                        <h5>Student Number</h5>
                                    </div>
                                    <div class="cell auto-size">
                                        <div class="input-control text full-size">
                                            <input type="text" id="txtStNumber">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="cell size3">
                                        <h5>Student Name</h5>
                                    </div>
                                    <div class="cell size4">
                                        <div class="input-control text full-size">
                                            <input type="text" id="txtStfName" placeholder="First Name">
                                        </div>
                                    </div>
                                    <div class="cell size4">
                                        <div class="input-control text full-size">
                                            <input type="text" id="txtStlName" placeholder="Last Name">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="cell size3">
                                        <h5>Section</h5>
                                    </div>
                                    <div class="cell auto-size">
                                        <div class="input-control text full-size">
                                            <input type="text" id="txtStSection">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="cell size3">
                                        <h5>Course</h5>
                                    </div>
                                    <div class="cell auto-size">
                                        <div class="input-control text full-size">
                                            <input type="text" id="txtStCourse">
                                        </div>
                                    </div>
                                </div>
                                <div class="row flex-just-end">
                                    <div class="cell size2">
                                        <button class="button primary full-size" id="btnStSave" value="0">Save</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="cell auto-size" id="QuestionInfo" style="display: none;">
                <div class="flex-grid padding10 no-padding-top">
                    <div class="row">
                        <div class="cell size12">
                            <h1 class="text-light">Question 1</h1>
                            <hr class="thin">
                        </div>
                    </div>
                    <div id="QuestionDetail">
                        <div class="row">
                            <div class="cell size12">
                                <div class="flex-grid">
                                    <div class="row">
                                        <div class="cell size12">
                                            <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Quia non culpa consequatur, quasi voluptate, placeat nemo odit, dolore ut maxime rerum, repellendus quaerat iure officiis assumenda aut deserunt? Harum, laboriosam?</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="cell size12">
                                            <p class="no-margin-top"><b>A</b>. Lorem ipsum dolor sit amet, consectetur adipisicing elit. Enim eveniet quidem consequatur, nisi possimus ab incidunt ullam expedita doloribus rem esse temporibus similique voluptates. Veniam consectetur, inventore magnam nesciunt maxime.</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="cell size12">
                                            <p class="no-margin-top"><b>B</b>. Lorem ipsum dolor sit amet, consectetur adipisicing elit. Enim eveniet quidem consequatur, nisi possimus ab incidunt ullam expedita doloribus rem esse temporibus similique voluptates. Veniam consectetur, inventore magnam nesciunt maxime.</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="cell size12">
                                            <p class="no-margin-top"><b>C</b>. Lorem ipsum dolor sit amet, consectetur adipisicing elit. Enim eveniet quidem consequatur, nisi possimus ab incidunt ullam expedita doloribus rem esse temporibus similique voluptates. Veniam consectetur, inventore magnam nesciunt maxime.</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="cell size12">
                                            <p class="no-margin-top"><b>D</b>. Lorem ipsum dolor sit amet, consectetur adipisicing elit. Enim eveniet quidem consequatur, nisi possimus ab incidunt ullam expedita doloribus rem esse temporibus similique voluptates. Veniam consectetur, inventore magnam nesciunt maxime.</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="cell size1">
                                            <h5>Answer:</h5>
                                        </div>
                                        <div class="cell size3">
                                            <div class="input-control select full-size">
                                                <select id="">
                                                    <option value=""></option>
                                                    <option value="A">A</option>
                                                    <option value="B">B</option>
                                                    <option value="C">C</option>
                                                    <option value="D">D</option>
                                                </select>
                                                <button class="button primary">Next <span class="mif-chevron-right icon"></span></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>


<?php $cn->OpenConnection(); ?>