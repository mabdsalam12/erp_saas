<?php
$general->pageHeader($rModule['name']);
$base = $db->selectAll('base','where status=1 order by code');

if(isset($_GET['cancel'])){
    $cancel=intval($_GET['cancel']);
    $draft = $db->get_rowData('sale_draft','id',$cancel);
    if(empty($draft)){$general->redirect($pUrl,array(37,$pageTitle));}
    elseif($draft['isActive']==2){$general->redirect($pUrl,array(21,$pageTitle));}
    else{
        $data = [
            'isActive'     => 2, 
        ];

        $where = ['id'=>$cancel];
        $db->arrayUserInfoEdit($data);
        $update=$db->update('sale_draft',$data,$where);
        if($update){
            $general->redirect($pUrl,2,'Pending orders cancel successfully.');

        }
        else{$error=fl(); setMessage(66);}
    }



}
else{
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
                    <div class="col-sm-12 col-lg-12">   
                        <div class="col-md-2">
                            <h5 class="box-title">Date</h5>
                            <input type="text" id="dRange" class="daterangepickerMulti form-control" value="">
                        </div>               
                        <?php
                        $general->inputBoxSelectForReport($base,'Base','base_id','id','title');
                        ?>


                        <div class="col-md-2">
                            <h5 class="box-title">Search</h5>
                            <a href="javascript:void()" class="btn btn-success" onclick="productsSaleDraftList()">Search</a>

                        </div>
                    </div>

                    <div class="col-sm-12 col-lg-12" id="reportArea" style="overflow: auto;">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php  
}
?>




<script type="">

    function productsSaleDraftList(){
        let dRange= $('#dRange').val();
        let base_id= $('#base_id').val();
        $('#reportArea').html(loadingImage);
        $.ajax({
            type:'post',
            url:ajUrl,
            data:{productsSaleDraftList:1,dRange:dRange,base_id:base_id},
            success:function(data){
                $('#reportArea').html('');
                if(typeof(data.status)  !== "undefined"){ 
                    if(data.status==1){
                        $('#reportArea').html(data.html);
                    }
                    swMessageFromJs(data.m);
                }
                else{
                    swMessage(AJAX_ERROR_MESSAGE); 
                }

            },
            error: function(XMLHttpRequest, textStatus, errorThrown) { 
                $('#reportArea').html('');
                swMessage(AJAX_ERROR_MESSAGE); 
            }
        });
    }
    $(document).ready(function(){
        productsSaleDraftList()
    });
</script>