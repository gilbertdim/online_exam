<?php
    session_start();
    
    include "../../db/clsconnection.php";
    $cn = New Connection;

    if(!isset($_SESSION['instr_id'])) die();
    $instr_id = $_SESSION['instr_id'];

    $cn->OpenConnection();

    if (isset($_POST['check_username'])) {
        $username = $cn->escape($_POST['check_username']);
        
        $cn->query("CALL sp_validate_username('$username')");
        $row = $cn->getrow();
        
        echo $row['result'];
        
        die();
    } 
    elseif (isset($_POST['get_user_info'])) {
        $id = $cn->escape($_POST['get_user_info']);
        
        $cn->query("CALL sp_select_user_account($id)");
        if($cn->hasrow()) {
            $row = $cn->getrow();

            $username = $row['username'];
            $fname = $row['fname'];
            $lname = $row['lname'];
            $usertype = $row['usertype'];
            $isactive = $row['isactive'];
        } else {
            $username = '';
            $fname = '';
            $lname = '';
            $usertype = 0;
            $isactive = 1;
        }
        
        ?>
            <div class="row">
                <div class="cell size12">
                    <h2 class="text-light">Update User</h2>
                    <hr class="thin">
                </div>
            </div>
            <div class="row">
                <div class="cell size3">
                    <h5>Username</h5>
                </div>
                <div class="cell auto-size">
                    <div class="input-control full-size">
                        <input type="text" id="txtUsername" value="<?php echo $username ?>" <?php if ($username != '') echo 'readonly'; ?>>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="cell size3">
                    <h5>Name</h5>
                </div>
                <div class="cell size4">
                    <div class="input-control text full-size">
                        <input type="text" id="txtFirstname" value="<?php echo $fname ?>" <?php if ($fname != '') echo 'readonly'; ?> placeholder="First Name">
                    </div>
                </div>
                <div class="cell auto-size">
                    <div class="input-control text full-size">
                        <input type="text" id="txtLastname" value="<?php echo $lname; ?>" <?php if ($lname != '') echo 'readonly'; ?> placeholder="Last Name">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="cell size3">
                    <h5>Level</h5>
                </div>
                <div class="cell auto-size">
                    <div class="input-control full-size">
                        <select id="cboUserLevel" <?php if ($_SESSION['instr_type'] == 2) echo 'disabled'; ?>>
                            <option value="0" <?php echo ($usertype == 0 ? 'selected' : ''); ?>>User</option>
                            <option value="2" <?php echo ($usertype == 2 ? 'selected' : ''); ?>>Super User</option>
                            <option value="1" <?php echo ($usertype == 1 ? 'selected' : ''); ?>>Admin</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="cell size3">
                    <h5>Status</h5>
                </div>
                <div class="cell auto-size">
                    <div class="input-control full-size">
                        <select id="cboActive">
                            <option value="1" <?php echo ($isactive == 1 ? 'selected' : ''); ?>>Active</option>
                            <option value="0" <?php echo ($isactive == 0 ? 'selected' : ''); ?>>InActive</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row" <?php if ($username != '') echo 'style="display: none"'; ?>>
                <div class="cell size3">
                    <h5>Password</h5>
                </div>
                <div class="cell auto-size">
                    <div class="input-control full-size">
                        <input type="password" id="txtPassword">
                    </div>
                </div>
            </div>
            <div class="row" <?php if ($username != '') echo 'style="display: none"'; ?>>
                <div class="cell size3">
                    <h5>Confirm Password</h5>
                </div>
                <div class="cell auto-size">
                    <div class="input-control full-size">
                        <input type="password" id="txtConfirmPassword">
                    </div>
                </div>
            </div>
        <?php 
    }
    elseif (isset($_POST['create_user'])) {
        $id = $cn->escape($_POST['create_user']);
        $uname = $cn->escape($_POST['uname']);
        $fname = $cn->escape($_POST['fname']);
        $lname = $cn->escape($_POST['lname']);
        $pwd = $cn->escape($_POST['password']);
        $ulevel = $cn->escape($_POST['ulevel']);
        $status = $cn->escape($_POST['status']);
            
        $pwd = hash('sha256', $pwd);
            
        $cn->query("CALL sp_add_edit_user($id, '$fname', '$lname', '$uname', '$pwd', $status, $ulevel)");
    }

    $cn->CloseConnection();
    
?>