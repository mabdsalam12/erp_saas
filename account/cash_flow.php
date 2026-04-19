<?php
    $general->pageHeader($rModule['name']);
?>
<div class="row">
    <div class="col-sm-12" id="message_show_box"></div>
    <div class="col-sm-12">
        <div class="white-box">
            <div class="row">
                <div class="col-md-2">
                    <h5 class="box-title">Date</h5>
                    <input type="text" name="dRange" id="dRange" class="daterangepickerMulti form-control" value="">
                </div>
                <div class="col-md-2">
                    <h5 class="box-title">Type</h5>
                    <select id='vType' class="form-control select2">
                        <option value="">All</option>
                        <?php
                            $acc->voucherTypeOption();
                        ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <h5 class="box-title">Head</h5>
                    <select id='hID' class="form-control select2">
                        <option value="">All</option>
                        <?php
                            $heads=$acc->getAllHead();
                            foreach($heads as $cn){
                        ?>
                            <option value="<?php echo $cn['id'];?>"><?php echo $cn['title'];?></option>
                        <?php
                            }
                        ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <h5 class="box-title">User</h5>
                    <select id='uID' class="form-control select2">
                        <option value="">All</option>
                        <?php
                            $heads=$db->allUsers('order by username asc');
                            foreach($heads as $cn){
                        ?>
                            <option value="<?php echo $cn['id'];?>" <?php echo $general->selected($cn['id'],USER_ID);?>><?php echo $cn['username'];?></option>
                        <?php
                            }
                        ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <h5 class="box-title">Search</h5>
                    <input type="submit" value="Search"class="btn btn-success" name="s" onclick="cashFlowLoad(0);">
                </div>
                <div class="col-sm-12 col-lg-12"><?php show_msg();?></div>
                <div class="col-sm-12 col-lg-12" id="trDetailsArea"></div>
            </div>
        </div>
    </div>
</div>
<?php
$acc->voucher_details_html();