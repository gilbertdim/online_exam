# Online Exam System
An exam management system that helps you manage all your exams and your students.

[Demo]<br>
http://gilbertdim.000webhostapp.com/online_exam/<br>
Use the above link to check out this application.
    <dd> Username : admin
    <dd> Password : admin
      
[Libraries]<br>
<ol>
  <li><b>PHP Mailer</b> https://github.com/PHPMailer/PHPMailer</li>
  <li><b>TCPDF</b> https://github.com/tecnickcom/TCPDF</li>
  <li><b>Metro UI CSS</b> https://metroui.org.ua/v3/</li>
</ol>

[System Requirements]<br>
<ol>
  <li>PHP 5.3.5 or higher</li>
  <li>MySQL 5.5.8 or higher</li>
</ol>

[Setup]<br>
<ol>
  <li>Run the <b>MySQL Database Setup.sql</b> in <b>Install</b> folder using a root account.</li>
  <li>Update the <b>config.php</b> and configure Mail settings</li>
  <li>Use the following account to access the system</li>
    <dd> Username : admin
    <dd> Password : admin
</ol>

[Modules]
<ol>
  <li><b>Exam Answer Sheet</b></li>
    <dd> Enter the correct examination code and the user will be redirected to exam answer sheet.
  <li><b>Login</b></li>
  <li><b>Forgot Password</b></li>
    <dd> System will ask your username and email address. 
    If you enter the correct details, system will send an email with a reset code link to change your password.
  <li><b>Change Password</b></li>
    <dd> With the correct reset code system will ask the user to change your password.
  <li><b>User Profile</b></li>
    <dd> This module will update your user information.
  <li><b>Exam Management</b></li>
    <ul>
      <li>Add Exam</li>
      <li>Edit Exam</li>
        <dd> Update the exam title and description. 
        <dd> Set Questionnaire and question option and answer
      <li>Generate Exam Code</li>
        <dd> After generating an exam code, exam will be locked for changes.
    </ul>
  <li><b>User Management</b></li>
    <dd> 
</ol>

[Contact]
For question and other related concern email me @
ark.gdimaano@gmail.com
