<?php

include "sysconfig.php";

//System Connection String

DEFINE('Mail_Host' , 'smtp.gmail.com');
DEFINE('Mail_Port' , 587);
DEFINE('Mail_SMTPSecure' , 'tls');
DEFINE('Mail_SMTPAuth' , true);

DEFINE('Mail_Username' , '');
DEFINE('Mail_Password' , '');

DEFINE('Mail_FromEmail' , '');
DEFINE('Mail_FromName' , 'Online Exam Administrator');

if (RunType == "Develop") {
    DEFINE('ServerName' , 'localhost');
    DEFINE('DbName'     , 'online_exam');
    DEFINE('DbUser'     , 'exammanager');
    DEFINE('DbPwd'      , 'examinstructor09!@#');
}


?>