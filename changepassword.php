<?php
    include "db/clsconnection.php";
    $cn = new Connection;
    $cn->OpenConnection();

    $showChangePassword = true;
    
    if(isset($_POST['changepassword'])) {
        $resetcode = $cn->escape($_POST['changepassword']);
        $password = $cn->escape($_POST['password']);
        $confirmpassword = $cn->escape($_POST['confirmpassword']);
        
        $passnotmatch = ($password != $confirmpassword);
        
        if (!$passnotmatch) {
            $password = hash('sha256', $password);
            
            $cn->execute("CALL sp_update_user_password('$resetcode', '$password')");
            
            $showChangePassword = false;
        }
    } else {
        if (!isset($_GET['resetcode'])) header ("Location: index.php");    
        $resetcode = $cn->escape($_GET['resetcode']);
        
        $cn->query("SELECT * FROM ausers WHERE resetcode='$resetcode'");
        $hasrow = $cn->hasrow();
        $cn->CloseRecordset();

        if (!$hasrow) header ("Location: index.php");
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
        <meta name="title" content="Online Exam">
        
        <link href="content/css/metro-responsive.css" rel="stylesheet">
        <link href="content/css/metro-colors.css" rel="stylesheet">
        <link href="content/css/metro-schemes.css" rel="stylesheet">
        <link href="content/css/metro-icons.css" rel="stylesheet">
        <link href="content/css/metro.css" rel="stylesheet">

        <script src="content/js/jquery-2.1.3.min.js"></script>
        <script src="content/js/dateFormat.js"></script>
        <script src="content/js/jquery.dataTables.min.js"></script>
        <script src="content/js/metro.js"></script>
        
        <script>
            $(function(){
                <?php
                    if(isset($passnotmatch)) {
                        if($passnotmatch) {
                            echo "$.Notify({
                                caption: 'Password not Match',
                                content: 'Please check password',
                                type: 'alert',
                                timeout: 6000
                            });";
                        }
                    }
                ?>
            });
        </script>
    </head>

    <body>
        <div class="dialog" data-role="dialog" data-show="<?php echo !$showChangePassword; ?>">
            <div class="flex-grid padding10">
                <div class="row">
                    <div class="cell size12">
                        <h4>Password has been set!</h4>
                    </div>
                </div>
                <div class="row flex-just-end">
                    <div class="cell size3">
                        <button class="button primary full-size" onclick="window.location = 'index.php'">Okay</button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="dialog" data-role="dialog" data-show="<?php echo $showChangePassword; ?>">
            <form data-role="validator" data-show-required-state="false" method="post">
                <h1 class="padding10">Change Password</h1>
                <hr>
                <div class="padding10 no-padding-top">
                    <div data-role="input" class="input-control modern password iconic full-size ">
                        <input type="password" name="password" data-validate-func="required">
                        <span class="label">Password</span>
                        <span class="informer">Please enter your password</span>
                        <span class="placeholder">Enter Password</span>
                        <span class="icon mif-lock"></span>
                        <span class="input-state-error mif-warning"></span>
                        <button class="button helper-button reveal" style="background-color: rgba(0,0,0,0)"><span class="mif-looks"></span></button>
                    </div>
                    <div data-role="input" class="input-control modern password iconic full-size">
                        <input type="password" name="confirmpassword" data-validate-func="required">
                        <span class="label">Password</span>
                        <span class="informer">Please confirm password</span>
                        <span class="placeholder">Confirm Password</span>
                        <span class="icon mif-lock"></span>
                        <span class="input-state-error mif-warning"></span>
                        <button class="button helper-button reveal" style="background-color: rgba(0,0,0,0)"><span class="mif-looks"></span></button>
                    </div>
                    <br><br>
                    <div class="form-actions">
                        <div class="flex-grid">
                            <div class="row">
                                <div class="cell size12">
                                    <button class="button primary full-size" name="changepassword" value="<?php echo $resetcode; ?>"> Change Password</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </body>
</html>


<?php
    $cn->CloseConnection();
?>