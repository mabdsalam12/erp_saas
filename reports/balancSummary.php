<?php
    $general->pageHeader($rModule['name']);
    $c=$tkt->superadminAdd($pUrl);$coID=$c['coID'];
    $acPermission   = $db->modulePermission($rModule['cmID'],true);
    $b=$tkt->adminBrandAdd($pUrl,true,$acPermission);
    if(!empty($b)){
    ?>
    <div class="row">
        <div class="col-sm-12">
            <div class="white-box">
                <div class="row">
                    <div class="col-md-2">
                        <input type="text" name="dRange" id="dRange" class="daterangepickerMulti form-control" value="">
                    </div>
                    <div class="col-md-3">
                        <div class="form-group row">
                            <div class="col-xs-2 col-sm-8 col-md-8">
                                <select id="cnType" class="form-control">
                                    <option value="all">All Type</option>
                                    <option value="<?php echo COUNTER_TYPE_MAIN;?>">Own</option>
                                    <option value="<?php echo COUNTER_TYPE_POCKET;?>">Pocket</option>
                                    <option value="<?php echo COUNTER_TYPE_ONLINE;?>">Online</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group row">
                            <div class="col-xs-2 col-sm-8 col-md-8">
                                <select id="contID" class="form-control">
                                    <option value="all">All Country</option>
                                    <option value="18">Bangladesh</option>
                                    <option value="99">India</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <select id='cnID' name="cnID" class="form-control select2">
                            <option value="">All Counter</option>
                            <?php
                                foreach($counters as $cn){
                                ?>
                                <option value="<?php echo $cn['cnID'];?>"><?php echo $cn['cnTitle'];?></option>
                                <?php
                                }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="submit" value="<?php echo $db->l('search') ?> " class="btn btn-success" name="s" onclick="balanceSummaryLoad();">
                    </div>
                    <div class="col-sm-12 col-lg-12">
                        <?php show_msg();?>
                    </div>
                    <div class="col-sm-12 col-lg-12" id="trDetailsArea">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
    }
?>