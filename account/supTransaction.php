<?php
    include(__DIR__."/splrTransaction.php");
    $suppliers=$db->selectAll('suppliers','where isActive=1 order by name asc');
?>
<div class="row">
    <div class="col-sm-12">
        <div class="white-box">
            <div class="row">
                <div class="col-sm-12 col-lg-12">
                    <div class="col-md-2">
                        <h5 class="box-title">Date</h5>
                        <input type="text" name="dRange" id="dRange" class="daterangepickerMulti form-control" value="">
                    </div>
                    <div class="col-md-2">
                        <h5 class="box-title">Supplier</h5>
                        <select id='supIDd' class="form-control select2">
                            <option value="">All Supplier</option>
                            <?php
                                foreach($suppliers as $sup){
                            ?><option value="<?php echo $sup['id'];?>"><?php echo $sup['name'];?></option><?php
                                }
                            ?>
                        </select>
                    </div>


                    <div class="col-md-2">
                        <h5 class="box-title">Search </h5>
                        <input type="submit" value="Search" class="btn btn-success" name="s" onclick="get_supplier_transactions();">
                    </div>
                </div>
                <div class="col-sm-12 col-lg-12"><?php show_msg();?></div>
                <div class="col-sm-12 col-lg-12" id="reportArea"></div>
            </div>
        </div>
    </div>
</div>
<script>
    function get_supplier_transactions(){
        var supID= parse_int($('#supIDd').val());
        var dRange  = $('#dRange').val();
        $('#reportArea').html(loadingImage);
        $.ajax({
            type:'post',
            url:ajUrl,
            data:{get_supplier_transactions:1,dRange:dRange,supplier_id:supID},
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