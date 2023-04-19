<?php
    if (!isset($cn)) header("Location:../");

    session_start();
    
    if(!isset($_SESSION['instr_id'])) die();
    $instr_id = $_SESSION['instr_id'];

    $cn->OpenConnection();
    
    // $cn->query("SELECT * FROM vw_exams WHERE tech_id = $instr_id");
    $cn->query("CALL sp_exam_list($instr_id)");
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
        $('#tblExam').dataTable({
            "order" : [[7, 'desc']]
        });
        
        $('#btnSet').click(function(){
            var dte = $('#dteSet').calendar('getDate');
            var tme = $('#tmeSet')[0].value;
            var hr = $('#numHour')[0].value;
            var examid = $('#btnSet')[0].value;
            
            if(dte == 'NaN-NaN-NaN' || tme == '') {
                $.Notify({
                    caption : 'Saving Failed',
                    content : 'Please set the time and date.',
                    type : 'alert',
                    timeout : 6000
                })
            } else {
                // const offset = new Date(dte).getTimezoneOffset()/60;
                // console.log(offset);
                // convert entered date time to utc
                const dteutc = new Date(dte+'T'+tme).toISOString();
                const sdteutc = dteutc.toString().substr(0,19).replace('T', ' ')
                $.post(
                    'exam/save_data.php',{
                        genExamCode : '',
                        exam : examid,
                        dteDate : dte,
                        tmeTime : tme,
                        dteutc : sdteutc,
                        numHour : hr
                    },function(data){
                        $.Notify({
                            caption : 'Saving Complate',
                            content : 'Exam code was successfully generated.',
                            type : 'success',
                            timeout : 6000
                        });
                        
                        metroDialog.close('#frmSetCode');
                        
                        GetData('Exam');
                    }
                );
            }
        });
        
        $('#btnSaveNew').click(function(){
            var subj = $('#txtNewSubj')[0].value;
            var title = $('#txtNewTitle')[0].value;
            var descrip = $('#txtNewDescrip')[0].value;
            var instruction = $('#txtNewInstruction')[0].value;
            
            if (title.trim() == '' || descrip.trim() == '' || instruction.trim() == '') {
                $.Notify({
                    caption : 'Saving Failed',
                    content : 'Please enter the exam title, description and instruction.',
                    type : 'alert',
                    timeout : 6000
                });
            } else {
                $.post(
                    'exam/save_data.php', {
                        save_new_exam : '',
                        ExamSubj : subj,
                        ExamTitle : title,
                        ExamDescrip : descrip,
                        ExamInstruction : instruction
                    },function(data){
                        $.Notify({
                            caption : 'Saving Complete',
                            content : 'New exam was successfully saved.',
                            type : 'success',
                            timeout : 6000
                        });
                        
                        metroDialog.close('#frmNewExam');
                        
                        GetData('Exam');
                    }
                );
            }
        });
    });
</script>

<div class="flex-grid padding10 no-padding-top">
    <div class="row">
        <div class="cell size12">
            <h1>My Exam List
            <span class="mif-files-empty mif-x2 icon place-right"></span>
            </h1>
            <hr class="thin">
        </div>
    </div>
    <div class="row" style="overflow-x: auto; height: 100%">
        <div class="cell size12">
            <button class="button warning place-left" onclick="metroDialog.open('#frmNewExam')">New Exam</button>
            <table id="tblExam" class="table bordered border hovered">
                <thead>
                    <th>Exam Code</th>
                    <th>Subject</th>
                    <th>Title</th>
                    <th>Descriptions</th>
                    <th>Items</th>
                    <th style="width: 100px">Start Date</th>
                    <th style="width: 100px">Expiration Date</th>
                    <th>Examinees</th>
                    <th style="width: 100px">Date Created</th>
                </thead>
                <tbody>
                    <?php while($row = $cn->getrow()) {
                        $offset = '0800';
                        if (isset($_POST['offset'])) {
                            $offset = $_POST['offset'];
                        }
                        $dte1 = new DateTime($row['dtestart'], new DateTimeZone('UTC'));
                        $dte1->setTimeZone( new DateTimeZone($offset)) ;
                        $dtestart = $dte1->format('m/d/Y h:i:s A');

                        $dte1 = new DateTime($row['dteend'], new DateTimeZone('UTC'));
                        $dte1->setTimeZone( new DateTimeZone($offset)) ;
                        $dteend = $dte1->format('m/d/Y h:i:s A');
                        
                        $dte1 = new DateTime($row['dtecreated'], new DateTimeZone('UTC'));
                        $dte1->setTimeZone( new DateTimeZone($offset)) ;
                        $dtecreated = $dte1->format('m/d/Y h:i:s A');                        
                        ?>
                    <tr ondblclick="window.open('exam/edit.php?id=<?php echo $row['id']; ?>', 'newwindow', 'width = 1000px, height = 500px');">
                        <td>
                        <a href="#" onclick="SetCode(<?php echo $row['id']; ?>)" title="Generate New Exam Voucher Code"><span class="mif-loop2 fg-blue"></span></a>
                        <?php echo $row['exam_code']; ?>
                        </td>
                        <td><?php echo $row['subj'] ?></td>
                        <td class="disable-selection"><?php echo $row['title'] ?></td>
                        <td class="disable-selection"><?php echo $row['descrip'] ?></td>
                        <td class="align-center disable-selection"><?php echo $row['cnt'] ?></td>
                        <td class="disable-selection">
                            <?php if($row['dtestart'] == '') { ?> 
                            <?php } echo $dtestart; ?>
                        </td>
                        <td class="disable-selection">
                            <?php if($row['dteend'] == '') { ?>
                            <?php } echo $dteend; ?>
                        </td>
                        <td class="align-center disable-selection"><a href="#"><span class="mif-users icon"></span> <?php echo $row['examinees']; ?></a></td>
                        <td class="disable-selection"><?php echo $dtecreated; ?></td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="frmNewExam" data-role="dialog" data-overlay="true" data-close-button="true" data-width="500">
    <div class="window-content">
        <div class="flex-grid padding10">
            <div class="row">
                <div class="cell size12">
                    <h2 class="text-light">New Exam</h2>
                    <hr class="thin">
                </div>
            </div>
            <div class="row">
                <div class="cell size3">
                    <h5>Subject</h5>
                </div>
                <div class="cell auto-size">
                    <div class="input-control text full-size">
                        <input type="text" id="txtNewSubj">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="cell size3">
                    <h5>Title</h5>
                </div>
                <div class="cell auto-size">
                    <div class="input-control text full-size">
                        <input type="text" id="txtNewTitle">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="cell size3">
                    <h5>Description</h5>
                </div>
                <div class="cell auto-size">
                    <div class="input-control textarea full-size">
                        <textarea id="txtNewDescrip"></textarea>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="cell size3">
                    <h5>Instruction</h5>
                </div>
                <div class="cell auto-size">
                    <div class="input-control textarea full-size">
                        <textarea id="txtNewInstruction"></textarea>
                    </div>
                </div>
            </div>
            <div class="row flex-just-end">
                <div class="cell size3">
                    <button id="btnSaveNew" class="button primary full-size">Save</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="frmSetCode" data-role="dialog" data-overlay="true" data-close-button="true">
    <div class="window-content">
        <div class="flex-grid">
            <div class="row">
                <div class="cell size12">
                    <h2 class="text-light" id="lblSetDateName">Exam Code</h2>
                    <hr class="thin">
                </div>
            </div>
            <div class="row">
                <div class="cell size12">
                    <p>Generating Exam Code will lock your exam <br />
                    from any changes, this is to prevent any <br />
                    modification on or after the examination day.</p>
                </div>
            </div>
            <div class="row">
                <div class="cell size12">
                    <div id="dteSet" class="calendar" data-min-date="<?php echo date_format(date_create(), 'Y-m-d') ?>" data-role="calendar"></div>
                </div>
            </div>
            <div class="row">
                <div class="cell size12">
                    <label>Time start and exam duration</label>
                </div>
            </div>
            <div class="row">
                <div class="cell size6">
                    <div class="input-control full-size">
                        <input id="tmeSet" type="time">
                    </div>
                </div>
                <div class="cell size6">
                    <div class="input-control full-size">
                        <input id="numHour" max="8" min="1" value="1" type="number" placeholder="Hours">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="cell size12">
                    <button id="btnSet" class="button success full-size">Save</button>
                </div>
            </div>
        </div>
    </div>
</div>