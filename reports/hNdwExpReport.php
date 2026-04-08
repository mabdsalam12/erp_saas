<?php
    $c=$tkt->superadminAdd($pUrl);$coID=$c['coID'];
    $b=$tkt->adminBrandAdd($pUrl,true);
    if(!empty($b)){
        $bID=$b['bID'];
        $counters=$tkt->allCounters($bID);
    ?>
    <div class="row">
        <div class="col-sm-12">
            <div class="white-box">
                <div class="row">
                    <div class="col-md-2">
                        <h5 class="box-title"><?php echo $db->l('data') ?> </h5>
                        <input type="text" id="dRange" class="daterangepickerMulti form-control" value="">
                    </div>
                    <div class="col-md-2">
                        <h5 class="box-title"><?php echo $db->l('counters') ?> </h5>
                        <select id='cnID' class="form-control select2">
                            <option value=""><?php echo $db->l('all') ?> </option>
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
                        <h5 class="box-title"><?php echo $db->l('head') ?> </h5>
                        <select id='hID' class="form-control select2">
                            <option value=""><?php echo $db->l('all') ?> </option>
                            <?php
                                $heads=$tkt->allHeads($bID);
                                foreach($heads as $cn){
                                ?>
                                <option value="<?php echo $cn['hID'];?>"><?php echo $cn['hTitle'];?></option>
                                <?php
                                }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <h5 class="box-title"><?php echo $db->l('search') ?> </h5>
                        <input type="submit" value="<?php echo $db->l('search') ?> " class="btn btn-success" name="s" onclick="hNdwExpReport();">
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
    <?php
    }
?>