<?php 
    session_start();

    if(!isset($_SESSION['instr_id'])) header('Location: ../');

    include "../db/clsconnection.php";
    $cn = New Connection;

    $instr_name = $_SESSION['instr_name'];
//    if (!isset($_GET['examid'])) header('Location: ../');
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
            $('li').click(function(){
                $('li').removeClass('active');
                $(this).addClass('active');
            });
            
            $('.table').dataTable({
                paging : false
            });
            
            $('.tabcontrol').tabcontrol();
            
            $('#btnSaveNew').click(function(){
                var id = $('#btnSaveNew')[0].value;
                var username = $('#txtUsername')[0].value;
                var firstname = $('#txtFirstname')[0].value;
                var lastname = $('#txtLastname')[0].value;
                var userlevel = $('#cboUserLevel')[0].value;
                var pass = $('#txtPassword')[0].value;
                var conf_pass = $('#txtConfirmPassword')[0].value;
                var active = $('#cboActive')[0].value;

                if(id == 0 && (firstname == '' || lastname == '' || pass == '' || conf_pass == '')) {
                    $.Notify({
                        caption : 'User Profile',
                        content : 'Please complete all the fields.',
                        type : 'alert',
                        timeout : 6000
                    });
                } else if (id == 0 && (pass != conf_pass)) {
                    $.Notify({
                        caption : 'User Profile',
                        content : 'Password and Confirm Password not match!',
                        type : 'alert',
                        timeout : 6000
                    });
                } else if (username == '') {
                    $.Notify({
                        caption : 'User Profile',
                        content : 'Please enter username.',
                        type : 'alert',
                        timeout : 6000
                    });
                } else {
                    $.post(
                        'users/save_data.php', {
                            check_username : username
                        }, function(data){
                            if(id == 0 && data.trim() == 'exists') {
                                $.Notify({
                                    caption : 'User Profile',
                                    content : 'Username entered is already exists.',
                                    type : 'alert',
                                    timeout : 6000
                                });
                            } else if(id > 0 || data.trim() == 'proceed') {
                                $.post(
                                    'users/save_data.php', {
                                        create_user : id,
                                        uname : username,
                                        fname : firstname,
                                        lname : lastname,
                                        password : pass,
                                        ulevel : userlevel,
                                        status : active
                                    }, function(data) {
                                        $.Notify({
                                            caption : 'User Profile',
                                            content : 'User Profile was successfully saved.',
                                            type : 'success',
                                            timeout : 6000
                                        });

                                        GetData('Users');
                                    }
                                );
                            }
                        }
                    );
                }
            });

            GetData('Exam');
        });
        
        function SetCode(id) {
            $('#btnSet')[0].value = id;

            metroDialog.open('#frmSetCode');
        }

        function GetData(value) {
            $.post(
                'content.php', {
                    getProfile : value
                }, function(data) {
                    $('#dvContent').html(data);
                }
            );
        }

        function UserProfile(id = 0) {
            if (id == 0) {
                $.when(
                    $('#btnSaveNew')[0].value = id,
                    $('#btnSaveNew').removeClass('success'),
                    $('#btnSaveNew').addClass('primary')
                ).done(function(){
                    $('#btnSaveNew').html('Create');
                });
            } else {
                $.when(
                    $('#btnSaveNew')[0].value = id,
                    $('#btnSaveNew').removeClass('primary'),
                    $('#btnSaveNew').addClass('success')
                ).done(function(){
                    $('#btnSaveNew').html('Update');
                });
            }
            
            $.post(
                'users/save_data.php', {
                    get_user_info : id
                }, function(data) {
                    $('#frmUserProfile_Body').html(data);
                    
                    metroDialog.open('#frmUserProfile');
                }
            );
        }
    </script>
    <div class="flex-grid no-responsive-future">
        <div class="row bg-darkCyan fg-white" style="border-bottom: 1px gray solid">
            <div class="cell size12">
                <div class="flex-grid padding10">
                    <div class="row">
                        <div class="cell size12">
                            <h3 class="text-light">Exam Management System</h3>
                            <h4 class="text-light" id="InstractorName">Hi, <?php echo $instr_name; ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="cell size2">
                <ul class="sidebar2">
                    <li><a href="#" onclick="GetData('Profile')"><span class="mif-user icon"></span>Instructor Profile</a></li>
                    <?php if ($_SESSION['instr_type'] != 0) { ?>
                    <li><a href="#" onclick="GetData('Users')"><span class="mif-users icon"></span>User Accounts</a></li>
                    <?php } ?>
                    <li class="active"><a href="#" onclick="GetData('Exam')"><span class="mif-files-empty icon"></span>My Exam List</a></li>
                    <li class="active"><a href="#" onclick="GetData('Reset')"><span class="mif-user icon"></span>Reset Examinee Status</a></li>
                    <li><a class="fg-white" style="background-color: #ce352c" href="logout.php"><span class="mif-exit icon"></span> Log Out</a></li>
                </ul>
            </div>
            <div id="dvContent" class="cell size10">
            </div>
        </div>
    </div>

    <div id="frmUserProfile" data-role="dialog" data-overlay="true" data-close-button="true" data-width="500">
        <div class="window-content">
            <div class="flex-grid padding10">
                <div id="frmUserProfile_Body">
                    
                </div>
                <div class="row flex-just-end">
                    <div class="cell size3">
                        <button id="btnSaveNew" class="button success full-size">Update</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>