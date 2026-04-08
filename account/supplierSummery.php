<?php
exit;
    $scID=SECTION_DEALER;
    $suppliers=$db->selectAll($general->table(45),'where scID='.$scID.' and isActive=1 order by name asc');
    $general->pageHeader($rModule['title']);
?>
<div class="white-box border-box">
    <div class="row">
        <div class="col-md-12">
            <div class="col-sm-12 col-md-12 col-lg-12">
                <input type="hidden" id="scID" value="<?php echo $scID;?>">
                <div class="col-md-2">
                    <h5 class="box-title">Date</h5>
                    <input type="text" id="dRange" class="daterangepickerMulti form-control" value="">
                </div>
                <div class="col-md-2">
                    <h5 class="box-title">Supplier</h5>
                    <select id="supID" class="col-md-8 form-control select2">
                        <option value="">Select Supplier</option>
                        <?php
                            foreach($suppliers as $e){
                        ?><option value="<?php echo $e['supID'];?>"><?php echo $e['name'];?></option><?php
                            }
                        ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <h5 class="box-title">Search</h5>
                    <a href="javascript:void()" class="btn btn-success" onclick="suuplierSummery()">Search</a>
                </div>
            </div> 

        </div>

        <div class="col-sm-12 col-lg-12"><?php show_msg();?></div>
        <div class="col-sm-12 col-lg-12" id="reportArea"></div>
    </div>
</div>