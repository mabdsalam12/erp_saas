<?php
    $general->pageHeader($rModule['name']);
    $suppliers=$db->selectAll($general->table(45),'where scID='.$scID.' and isActive=1 order by supName asc');
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
                            ?><option value="<?php echo $e['supID'];?>"><?php echo $e['supName'];?></option><?php
                            }
                        ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <h5 class="box-title">Search</h5>
                    <a href="javascript:void()" class="btn btn-success" onclick="supHistoryReport()">Search</a>
                </div>
            </div> 

        </div>

        <div class="col-sm-12 col-lg-12"><?php show_msg();?></div>
        <div class="col-sm-12 col-lg-12" id="reportArea"></div>
    </div>
</div>
<script>
   function supHistoryReport(){
        var supID= parse_int($('#supIDd').val());
        var scID= parse_int($('#scID').val());
        var dRange  = $('#dRange').val();
        $('#reportArea').html(loadingImage);
        $.ajax({
            type:'post',
            url:ajUrl,
            data:{supHistoryReport:1,scID:scID,dRange:dRange,supID:supID},
            success:function(data){
                $('#reportArea').html('');
                if(data.status==1){
                    $('#reportArea').html(data.html);
                }
                swMessageFromJs(data.m);
            }
        });
    }
    </script>