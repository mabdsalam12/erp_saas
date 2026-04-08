<?php
    $scID=SECTION_DEALER;
    $suppliers=$db->selectAll($general->table(45),'where scID='.$scID.' and isActive=1 order by supName asc');
?>
<div class="row">
    <div class="col-sm-12">
        <div class="white-box">
            <div class="row">
                <div class="col-sm-12 col-lg-12">
                    <div class="col-md-2">
                        <h5 class="box-title">Date</h5>
                        <input type="text" id="dRange" class="daterangepickerMulti form-control" value="<?php echo date('d-m-Y').' to '.date('d-m-Y');?>">
                    </div>
                    <div class="col-md-2">
                        <h5 class="box-title">Supplier</h5>
                        <select id='supID' class="form-control select2">
                            <option value="">All Supplier</option>
                            <?php
                                foreach($suppliers as $sup){
                            ?>
                                <option value="<?php echo $sup['supID'];?>"><?php echo $sup['supName'];?></option>
                            <?php
                                }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <h5 class="box-title">Search</h5>
                        <a href="javascript:void()" class="btn btn-success" onclick="purStokStatmant()">Search</a>
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
<script>
function purStokStatmant(){
    var supID   = parse_int($('#supID').val());
    var dRange  = $('#dRange').val();

    if(supID>0){
        $('#reportArea').html(loadingImage);
        $.ajax({
            type:'post',
            url:ajUrl,
            data:{purStokStatmant:1,dRange:dRange,supID:supID},
            success:function(data){
                if(data.status==1){
                    $('#reportArea').html(data.html);
                }
                swMessageFromJs(data.m);
            }
        });
    }else{
        swMessage('Please select supplier');
    }


}
</script>