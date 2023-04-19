<?php

    require 'lib/phpmailer/PHPMailerAutoload.php';
    include "db/clsconnection.php";

    $cn = New Connection;        
    $email_sent = false;

    if(isset($_POST['btnForgotPass'])) {
        $cn->OpenConnection();

        $txtUsername =  $cn->escape($_POST['txtUsername']);
        $txtEmail =  $cn->escape($_POST['txtEmail']);

        $cn->query("CALL sp_check_user('$txtUsername', '$txtEmail')");
        $result = $cn->getrow();

        $resetcode = $result['resetcode'];
        if ($resetcode == '') {
            $forgot_err = 'Account Not Found';
        } else {
            $url = $_SERVER['SERVER_NAME'] . ($_SERVER['SERVER_PORT'] == 80 ? '' : ':'.$_SERVER['SERVER_PORT']);
            
            if(Mail_Host != '' && Mail_Port != '' && Mail_Username != '' && Mail_Password != '') {
                $mail = new PHPMailer;
                $mail->isSMTP();

                $mail->Host = Mail_Host;
                $mail->Port = Mail_Port;

                $mail->SMTPSecure = Mail_SMTPSecure;
                $mail->SMTPAuth = Mail_SMTPAuth;

                $mail->Username = Mail_Username;
                $mail->Password = Mail_Password;

                $mail->From = Mail_FromEmail;
                $mail->sender = Mail_FromEmail;
                $mail->FromName = Mail_FromName;

                $mail->addAddress($txtEmail, $txtUsername);

                $mail->Subject = "Online Exam Reset Password";

                $msg = "Hi $txtUsername! <br><br> 
                Use this <a href='http://$url/online_exam/changepassword.php?resetcode=$resetcode'>Link</a> to reset your password.<br><br>
                If the above link don't work use this link instead.<br>
                http://$url/online_exam/changepassword.php?resetcode=$resetcode<br><br>
                Thanks,
                Online Exam";

                $mail->msgHTML($msg);

                $email_sent = false;
                while (!$email_sent) $email_sent = $mail->send();
            }
        }
    }

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
            <?php if(Mail_Host == '' || Mail_Port == '' || Mail_Username == '' || Mail_Password == '') { ?>
            $.Notify({
                caption: 'SMTP Not Define',
                content: 'Please configure your system email account to send an email for reseting a password.',
                type: 'alert',
                keepOpen : true
            });
            <?php } ?>
            
            <?php if($email_sent) { ?>
            $.Notify({
                caption: 'Email Sent',
                content: 'A reset code was successfully sent to <?php echo $txtEmail; ?>.',
                type: 'success',
                timeout: 6000
            });
            <?php } ?>
        });
    </script>

    <div data-show="true" data-role="dialog" id="dialog7" class="padding20" data-windows-style="true">
        <div class="container">
            <div class="flex-grid">
                <div class="row flex-just-center">
                    <div class="cell size4">
                        <form method="POST">
                            <div class="flex-grid">
                                <div class="row">
                                    <div class="cell size12">
                                        <h3 class="text-light">Forgot Password</h3>
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
                                            <input type="text" name="txtUsername">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="cell size12">
                                        <label>Email Address</label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="cell size12">
                                        <div class="input-control text full-size">
                                            <input type="email" name="txtEmail">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="cell size12">
                                        <button name="btnForgotPass" class="button primary full-size">Send Reset Code</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>