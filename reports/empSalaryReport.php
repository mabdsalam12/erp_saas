<?php
    $general->pageHeader($rModule['title']);
    $employees=$db->selectAll($general->table(74),'where isActive =1 order by eName asc');
?>
<div class="white-box border-box">
    <div class="row">
        <div class="col-md-12">
            <div class="col-sm-12 col-md-12 col-lg-12">
                <div class="col-md-2">
                    <h5 class="box-title">Date</h5>
                    <input type="text" id="dRange" class="daterangepickerMulti form-control" value="">
                </div>
                <div class="col-md-2">
                    <h5 class="box-title">Employee</h5>
                    <select id="eID" class="col-md-8 form-control select2">
                        <option value="">Select Employee</option>
                        <?php
                            foreach($employees as $e){
                        ?><option value="<?php echo $e['eID'];?>"><?php echo $e['eName'];?></option><?php
                            }
                        ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <h5 class="box-title">Type</h5>
                    <select id="type" class="col-md-8 form-control select2">
                        <option value="0">Summery</option>
                        <option value="1">Details</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <h5 class="box-title">Search</h5>
                    <a href="javascript:void()" class="btn btn-success" onclick="employeeSalaryReport()">Search</a>
                </div>
            </div> 

        </div>

        <div class="col-sm-12 col-lg-12"><?php show_msg();?></div>
        <div class="col-sm-12 col-lg-12" id="reportArea"></div>
    </div>
</div>