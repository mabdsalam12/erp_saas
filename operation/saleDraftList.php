<?php

    $suppliers=$db->selectAll($general->table(45),'where isActive=1 order by supName asc');
    $general->pageHeader($rModule['title']);
    if(isset($_GET['remove'])){
        $draftID=intval($_GET['draftID']);
        $oldDraft=$db->get_rowData($general->table(10),'sID',$draftID);
        if(!empty($oldDraft)){
            $data=array('isActive'=>'2');
            $where=array('sID'=>$draftID);
            $update=$db->update($general->table(10),$data,$where);
            $general->redirect($pUrl,2,'Draft removed');
        }
        //header("location:".URL.'/?mdl=saleDraftList');
        
    }
?>

<div class="row">
    <div class="col-sm-12">
        <div class="white-box">
            <div class="row">
                <div class="col-sm-12 col-lg-12">
                    
                    <div class="col-md-2">
                        <h5 class="box-title">Supplier</h5>
                        <select id='supID' class="form-control select2">
                            <option value="">All Supplier</option>
                            <?php
                                foreach($suppliers as $sup){
                            ?><option value="<?php echo $sup['supID'];?>"><?php echo $sup['supName'];?></option><?php
                                }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <h5 class="box-title">Search</h5>
                        <a href="javascript:void()" class="btn btn-success" onclick="saleDraftList()">Search</a>
                        <script type="text/javascript">
                            $(document).ready(function(){
                                saleDraftList();
                            })
                            function saleDraftList(){
                                var supID   = parse_int($('#supID').val());
                                var scID= parse_int($('#scID').val());
                                var dRange  = $('#dRange').val();
                                $('#reportArea').html(loadingImage);
                                $.ajax({
                                    type:'post',
                                    url:ajUrl,
                                    data:{saleDraftList:1,dRange:dRange,supID:supID},
                                    success:function(data){
                                        if(data.status==1){
                                            $('#reportArea').html(data.html);
                                        }
                                        swMessageFromJs(data.m);
                                    }
                                });
                            }
                        </script>
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
