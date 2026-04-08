<?php
    $general->pageHeader($rModule['title']);
    
    $employees=$db->selectAll('employees',' order by name asc');

    $dRange=date('d-m-Y').' to '.date('d-m-Y',strtotime('+30 day'));
    if(isset($_GET['dRange'])){$dRange=$_GET['dRange'];}
    //$general->printArray($members);
?>
<div class="white-box border-box">
    <div class="row">
        <div class="col-md-12">
            <div class="col-sm-12 col-md-12 col-lg-12">
                <div class="col-md-3">
                    <h5 class="box-title">Date</h5>
                    <input type="text" name="dRange" id="dRange" class="daterangepickerMulti form-control" value="<?php echo $dRange; ?>">
                </div>
                <div class="col-md-3">
                    <h5 class="box-title">Employee</h5>
                    <select id="id" class="col-md-8 form-control select2">
                        <option value="">Select employee</option>

                        <?php
                            foreach($employees as $c){
                        ?><option <?=$general->selected($c['id'],@$_GET['employee_id'])?> value="<?php echo $c['id'];?>"><?php echo $c['name'];?></option><?php
                            }
                        ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <h5 class="box-title">Search</h5>
                    <a href="javascript:void()" class="btn btn-success" onclick="employeeStatement()">Search</a>
                </div>
                <div class="col-md-4">
                    <h5 class="box-title" id="cMobile"></h5>
                    <h5 class="box-title" id="cAddress"></h5>
                </div>

            </div> 
            <?php

                if(isset($_GET['employee_id'])){
            ?>
                <script>
                    $(document).ready(function(){
                        employeeStatement();
                    })

                </script>
            <?php
                }
            ?>
            <script>
                function employeeStatement(){
                    var id   = parse_int($('#id').val());

                    var dRange   = $('#dRange').val();

                    if(id>0){
                        $('#reportArea').html(loadingImage);
                        $.ajax({
                            type:'post',
                            url:ajUrl,
                            data:{employeeStatement:1,dRange:dRange,id:id},
                            success:function(data){
                                $('#reportArea').html('');
                                if(data.status==1){
                                    $('#reportArea').html(data.html);
                                }
                                swMessageFromJs(data.m);
                            }
                        });
                    }else{swMessage('Please Select employee');}

                }
            </script>
            <?php

            ?>
        </div>

        <div class="col-sm-12 col-lg-12"><?php show_msg();?></div>
        <div class="col-sm-12 col-lg-12" id="reportArea"></div>
    </div>
</div>