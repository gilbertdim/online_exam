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
        $('#btnReset').click(function() {
            $.post(
                'reset/save_data.php', {
                    reset_examinee_status : '',
                    exam_code : $('#txtExamCode')[0].value,
                    examinee_code : $('#txtExamineeCode')[0].value
                },function(data) {
                    console.log(data);
                    debugger;
                    ret = JSON.parse(data);
                    if (ret.updated > 0) {
                        $.Notify({
                            caption:'Reset Examinee Stats',
                            content:'Examinee status has been reset.',
                            type:'success',
                            timeout:10000
                        });
                    } else {
                        $.Notify({
                            caption:'Reset Examinee Stats',
                            content:'Examinee status has NOT been reset.',
                            type:'alert',
                            timeout:10000
                        });
                    }
                }
            )
        });
    });
</script>
<div class="flex-grid padding10 no-padding-top">
    <div class="row">
        <div class="cell size12">
            <h1>Reset Examinee Status
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
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="cell size4">
                    </div>
                    <div class="cell size8">
                        <div class="flex-grid">
                            <div class="row flex-just-sb">
                                <div class="cell size4">
                                    <button class="button success full-size" id="btnReset">Reset</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
