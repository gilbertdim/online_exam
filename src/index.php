<?php 
    session_start();

    $withLogIn = false;
    $LogInSuccess = false;
    $username = "";
    $forgot_err = "";

    include "db/clsconnection.php";

    $cn = New Connection;        

    include "signin.php"; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Online Exam</title>
    
    <link href="content/css/metro-responsive.css" rel="stylesheet">
    <link href="content/css/metro-colors.css" rel="stylesheet">
    <link href="content/css/metro-schemes.css" rel="stylesheet">
    <link href="content/css/metro-icons.css" rel="stylesheet">
    <link href="content/css/metro.css" rel="stylesheet">
    
    <script src="content/js/jquery-2.1.3.min.js"></script>
    <script src="content/js/dateFormat.js"></script>
    <script src="content/js/jquery.dataTables.min.js"></script>
    <script src="content/js/metro.js"></script>

</head>
<body>
    <script>
        $(function(){
            <?php if($withLogIn && !$LogInSuccess) { ?>
            $.Notify({
                caption: 'Login Failed',
                content: 'Please check your username or password.',
                type: 'alert',
                timeout: 6000
            });
            <?php } elseif ($withLogIn && $login_err == 1) { ?>
            $.Notify({
                caption: 'Login Failed',
                content: 'You\'re account is being deactivated.<br><br>Please contact the system administrator.',
                type: 'alert',
                timeout: 6000
            });
            <?php } ?>
            
            $('#btnGoExam').click(function(){
                
                if($('#txtExamCode')[0].value != '' && $('#txtExamineeCode')[0].value != '' ) {
                    $.post(
                        'exam/save_data.php', {
                        check_exam_code : $('#txtExamCode')[0].value,
                        examinee_code : $('#txtExamineeCode')[0].value
                        },function(data) {
                            valid_codes = false;
                            if (data > '') {
                                ret = JSON.parse(data);
                                if (ret.student_id > 0) {
                                    valid_codes = true;
                                    window.location = 'exam/?id=' + $('#txtExamCode')[0].value + '&sid=' + ret.student_id;
                                }
                            }
                            if (!valid_codes) {
                                $.Notify({
                                    caption:'Invalid Exam code',
                                    content:'Exam code is either not found or the exam is not yet started or the exam has already expired.<br><br>Please coordinate with your exam coordinator regarding the exam details.',
                                    type:'alert',
                                    timeout:10000
                                });
                            }
                        }
                    );
                } else {
                    $.Notify({
                        caption:'Online Exam',
                        content:'Please enter exam code and examinee code to start the exam.',
                        type:'alert',
                        timeout:6000
                    });
                }
            });
        });
    </script>
    <div class="app-bar" data-role="appbar">
        <label for="" class="app-bar-element"><span class="mif-home"></span></label>
        <span class="app-bar-divider"></span>
        <label for="" class="app-bar-element"></label>
        <a href="#" onclick="metroDialog.open('#frmSignIn')" class="app-bar-element place-right"><span class="mif-key"></span></a>
    </div>

    <div data-role="dialog" data-show="true">
        <div class="window-content">
<!--            <form action="exam/">-->
                <div class="flex-grid padding10">
                    <div class="row">
                        <div class="cell size12">
                            <h1>Online Examination System</h1>
                            <hr class="thin">
                        </div>
                    </div>
                    <div class="row">
                        <div class="cell size12">
                            <label>Enter Examination Session Code</label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="cell size12">
                            <div class="input-control text full-size">
                                <input type="text" id="txtExamCode">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="cell size12">
                            <label>Enter Examinee ID</label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="cell size12">
                            <div class="input-control text full-size">
                                <input type="text" id="txtExamineeCode">
                                <button id="btnGoExam" class="button primary"><span class="mif-enter"></span></button>
                            </div>
                        </div>
                    </div>
                </div>
<!--            </form>-->
        </div>
    </div>
    
    <div id="frmSignIn" class="window" data-role="dialog" data-overlay="true" data-overlay-color="op-dark" data-overlay-click-close="true" data-show="<?php if($withLogIn) echo "true"; ?>">
        <div class="window-caption fg-white bg-darkGray">
            <span class="window-caption-icon"><span class="mif-user"></span></span>
            <span class="window-caption-title">Sign In</span>
        </div>
        <div class="window-content">
            <form action="index.php" method="post">
                <div class="flex-grid padding10">
                    <div class="row" style="margin-bottom: 20px">
                        <div class="cell size12">
                            <h4 class="text-light">Login to Content Management Module</h4>
                            <hr class="thin">
                        </div>
                    </div>
                    <div class="row">
                        <div class="cell size12">
                            <label>Username</label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="cell size12">
                            <div class="input-control text full-size">
                                <input type="text" name="txtUsername" value="<?php echo $username; ?>">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="cell size12">
                            <label>Password</label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="cell size12">
                            <div class="input-control text full-size">
                                <input type="password" name="txtPassword">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="cell size12">
                            <button name="btnSignIn" class="button primary full-size">Sign In</button>
                        </div>
                    </div>
                    <div class="row">
                        <div class="cell size12">
                            <!-- TODO: enable button when forgot password feature is working -->
                            <!-- <button type="button" onclick="window.location = 'forgotpassword.php'" class="button full-size">Forgot Password</button> -->
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</body>
</html>