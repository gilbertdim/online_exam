<?php
    if (!isset($cn)) header("Location:../");

    session_start();

    if(!isset($_SESSION['instr_id'])) die();
    $instr_id = $_SESSION['instr_id'];

    $cn->OpenConnection();
    
    $cn->query("SELECT * FROM ausers WHERE id = $instr_id");
    $row = $cn->getrow();
?>

<script>
    $(function(){
        $('#btnSave').click(function(){
            $.post(
                'profile/save_data.php',{
                    save_profile: '',
                    firstn : $('#txtFirstName')[0].value,
                    lastn : $('#txtLastName')[0].value,
                    addr : $('#txtAddress')[0].value,
                    city : $('#txtCity')[0].value,
                    phone : $('#txtPhone')[0].value,
                    email : $('#txtEmail')[0].value
                },function(data){
                    $('#InstractorName').html('Hi, ' + $('#txtFirstName')[0].value + ' ' + $('#txtLastName')[0].value);
                    
                    $.Notify({
                        caption:'Profile Saved',
                        content:'Profile was successfully updated.',
                        type:'success',
                        timeout:6000
                    });
                }
            );
        });
        
        $('#btnChangePass').click(function(){
            var oldp = $('#txtOldPass')[0].value;
            var newp = $('#txtNewPass')[0].value;
            var newp_confirm = $('#txtNewConfirmPass')[0].value;
            
            if(oldp == '') {
                $.Notify({
                    caption : 'Change Password',
                    content : 'Please enter old password!',
                    type : 'alert',
                    timeout : 6000
                });
            } else if(newp != newp_confirm) {
                $.Notify({
                    caption : 'Change Password',
                    content : 'New and confirm password not match!',
                    type : 'alert',
                    timeout : 6000
                });
            } else {
                $.post(
                    'profile/save_data.php', {
                        change_pass : '',
                        old_pass : oldp,
                        new_pass : newp
                    }, function(data) {
                        
                        if(data.trim() == 'wrong pass') {
                            $.Notify({
                                caption : 'Change Password',
                                content : 'Incorrect old password.',
                                type : 'alert',
                                timeout : 6000
                            });
                        } else if(data.trim() == 'success') {
                            $.Notify({
                                caption : 'Change Password',
                                content : 'Password was successfully updated.',
                                type : 'success',
                                timeout : 6000
                            });
                        }
                    }
                );
                
            }
        });
    });
</script>
<div class="flex-grid padding10 no-padding-top">
    <div class="row">
        <div class="cell size12">
            <h1>Instructor's Profile
            <span class="mif-user mif-x2 icon place-right"></span>
            </h1>
            <hr class="thin">
        </div>
    </div>
    <div class="row flex-just-center">
        <div class="cell size6">
            <div class="flex-grid">
<!--
                <div class="row">
                    <div class="cell size4">
                        <h5>Instructor ID</h5>
                    </div>
                    <div class="cell size8">
                        <div class="input-control text full-size">
                            <input type="text">
                        </div>
                    </div>
                </div>
-->
                <div class="row">
                    <div class="cell size4">
                        <h5>Instructor Name</h5>
                    </div>
                    <div class="cell size4">
                        <div class="input-control text full-size">
                            <input type="text" id="txtFirstName" value="<?php echo $row['fname']; ?>">
                        </div>
                    </div>
                    <div class="cell size4">
                        <div class="input-control text full-size">
                            <input type="text" id="txtLastName" value="<?php echo $row['lname']; ?>">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="cell size4">
                        <h5>Address</h5>
                    </div>
                    <div class="cell size8">
                        <div class="input-control text full-size">
                            <input type="text" id="txtAddress" value="<?php echo $row['addr1']; ?>">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="cell size4">
                        <h5>City</h5>
                    </div>
                    <div class="cell size8">
                        <div class="input-control text full-size">
                            <input type="text" id="txtCity" value="<?php echo $row['city']; ?>">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="cell size4">
                        <h5>Email Address</h5>
                    </div>
                    <div class="cell size8">
                        <div class="input-control text full-size">
                            <input type="text" id="txtEmail" value="<?php echo $row['email']; ?>">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="cell size4">
                        <h5>Contact Number</h5>
                    </div>
                    <div class="cell size8">
                        <div class="input-control text full-size">
                            <input type="text" id="txtPhone" value="<?php echo $row['phone']; ?>">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="cell size4">
                    </div>
                    <div class="cell size8">
                        <div class="flex-grid">
                            <div class="row flex-just-sb">
                                <div class="cell size5">
                                    <button class="button full-size" onclick="metroDialog.open('#frmChangePassword');"> Change Password</button>
                                </div>
                                <div class="cell size4">
                                    <button class="button success full-size" id="btnSave"> Update</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="frmChangePassword" data-role="dialog" data-overlay="true" data-close-button="true">
    <div class="window-content">
        <div class="flex-grid">
            <div class="row">
                <div class="cell size12">
                    <h2 class="text-light" id="lblSetDateName">Change Password</h2>
                    <hr class="thin">
                </div>
            </div>
            <div class="padding10">
                <div class="row">
                    <div class="cell size12">
                        <label>Old Password</label>
                        <div class="input-control text full-size">
                            <input id="txtOldPass" type="password">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="cell size12">
                        <label>New Password</label>
                        <div class="input-control text full-size">
                            <input id="txtNewPass" type="password">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="cell size12">
                        <label>Confirm New Password</label>
                        <div class="input-control text full-size">
                            <input id="txtNewConfirmPass" type="password">
                        </div>
                    </div>
                </div>
                <div class="row flex-just-end">
                    <div class="cell size5">
                        <button id="btnChangePass" class="button success full-size">Update</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>