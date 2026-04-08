<?php
    $general->pageHeader($rModule['title']);
?>
<div class="row">
    <div class="col-sm-12">
        <div class="white-box">
            <div class="row">
                   
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
<script type="text/javascript">
    $(document).ready(function(){
        user_balance_retport();
    })
    //$(document).on("click",'#purchase', function(){
//        purchaseReport();
//    })

</script>
