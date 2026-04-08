<div class="row">
    <div class="col-sm-12">
        <div class="white-box">
            <div class="row">
                <div class="col-md-2">
                    <h5 class="box-title">Head</h5>
                    <select id='hID' class="form-control select2">
                        <option value="">All</option>
                        <?php
                            $heads=$acc->getAllHead();
                            foreach($heads as $cn){
                        ?>
                            <option value="<?php echo $cn['id'];?>" <?php echo $general->selected($cn['id'],@$_GET['ledger_id']);?>><?php echo $cn['title'];?></option>
                        <?php
                            }
                        ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <h5 class="box-title">Data</h5>
                    <input type="text" name="dRange" id="dRange" class="daterangepickerMulti form-control" value="">
                </div>
                <div class="col-md-2">
                    <h5 class="box-title">User</h5>
                    <select id="uID" class="form-control select2">
                        <option value="">All</option>
                        <?php
                            $heads=$db->allUsers(' order by username asc');
                            foreach($heads as $cn){
                        ?>
                            <option value="<?php echo $cn['id'];?>"><?php echo $cn['username'];?></option>
                        <?php
                            }
                        ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <h5 class="box-title">Search</h5>
                    <input type="submit" value="Search" class="btn btn-success" name="s" onclick="HeadWiseTransaction();">
                </div>
                <div class="col-sm-12 col-lg-12">
                    <?php
                        show_msg();
                    ?>
                </div>
                <div class="col-sm-12 col-lg-12" id="trDetailsArea"></div>
            </div>
        </div>
    </div>
</div>