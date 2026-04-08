<?php
    $general->pageHeader($rModule['title']);
    $users = $db->selectAll('users','where type='.USER_TYPE_MPO.' and isActive in(1,0)','username,id');
?>
<div class="row">
    <div class="col-sm-12">
        <div class="white-box">
            <div class="row">
                <div class="col-sm-12 col-lg-12">   
                    <div class="col-md-2">
                        <h5 class="box-title">Date</h5>
                        <input type="text" id="dRange" class="daterangepickerMulti form-control" value="">
                    </div>   
                    <?php $general->inputBoxSelectForReport($users,'User','user_id','id','username',haveSelect:'Select'); ?>
                    <div class="col-md-2">
                        <h5 class="box-title">Search</h5>
                        <button class="btn btn-success" onclick="user_statment()" id="user_statment">Search</button>

                    </div>
                </div>   

                <div class="col-sm-12 col-lg-12">
                    <?php
                        show_msg();
                    ?>
                </div>
                <div class="col-sm-12 col-lg-12" id="reportArea">
                </div>
            </div>
        </div>
    </div>
</div>
