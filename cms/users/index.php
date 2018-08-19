<?php
    if (!isset($cn)) header("Location:../");

    session_start();
    
    if(!isset($_SESSION['instr_id'])) die();
    $instr_id = $_SESSION['instr_id'];

    $cn->OpenConnection();
    
    $cn->query("SELECT * FROM vw_users");
?>
   

<style>
    table tbody tr {
        cursor: pointer
    }
    
    .disable-selection {
        -moz-user-select: none;
        -ms-user-select: none;
        -khtml-user-select: none;
        -webkit-user-select: none;
        -webkit-touch-callout: none;
    }
</style>

<script>
    $(function(){
        $('#tblUser').dataTable();
    });
</script>

<div class="flex-grid padding10 no-padding-top">
    <div class="row">
        <div class="cell size12">
            <h1>User Accounts
            <span class="mif-users mif-x2 icon place-right"></span>
            </h1>
            <hr class="thin">
        </div>
    </div>
    <div class="row" style="overflow-x: auto; height: 100%">
        <div class="cell size12">
            <button class="button warning place-left" onclick="UserProfile()">New User</button>
            <table id="tblUser" class="table bordered border hovered">
                <thead>
                    <th>Name</th>
                    <th>Address</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th style="width: 50px">Admin</th>
                    <th style="width: 50px">Active</th>
                </thead>
                <tbody>
                    <?php while($row = $cn->getrow()) { ?>
                    <tr ondblclick="UserProfile(<?php echo $row['id']; ?>)">
                        <td><?php echo $row['fullname']; ?></td>
                        <td><?php echo $row['address']; ?></td>
                        <td><?php echo $row['phone']; ?></td>
                        <td><?php echo $row['email']; ?></td>
                        <td class="align-center"><?php echo ($row['usertype'] == 1 ? '<span class="mif-checkmark fg-green"></span>' : '' ); ?></td>
                        <td class="align-center"><?php echo ($row['isactive'] == 1 ? '<span class="mif-checkmark fg-green"></span>' : '<span class="mif-cross fg-red"></span>' ); ?></td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>