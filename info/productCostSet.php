<?php
    $aStatus      = true;
    $eStatus      = true;
    $tPTbl          = 104;
    $tpID           = 'pID';
    $tpTitle        = 'title';
    $pageTitle      = $rModule['title'];


    //$pLink='?'.MODULE_URL.'=dealerProductStatment&supID='.$supID;
    $pLink=$pUrl;
    $stLink='?'.MODULE_URL.'=dealerProductStatment';
    $units=$smt->getAllUnit();
    $general->arrayIndexChange($units,'id');
    $categorys=$db->selectAll('product_category','where isActive=1','id,parent,title'); 



    $general->arrayIndexChange($categorys,'id');
    $data = array($pUrl=>$pageTitle);
    //$sortLink='<a style="font-size: 20px; color: #228AE6;margin-left: 20px;" href="'.$pUrl.'&sort=1"><i class="fa fa-arrows-v"></i></a>';
    $general->pageHeader($rModule['title']);
    $prodcutData=[];

?>
<div class="row">
    <div class="col-sm-12">
        <div class="white-box">
            <?php
                show_msg();
                $products=$db->selectAll('products');
            ?>
            <div class="col-md-12" id="reportArea">
                <table class="table table-striped table-bordered table-hover">
                    <thead>
                        <tr>

                            <th>SN</th>
                            <th>Product</th>
                            <th>Stock</th>
                            <th>Unit Cost</th>
                            <th>Unit Cost Update</th>

                        </tr>
                    </thead> 
                    <tbody>
                        <?php
                            $total=1;

                            foreach($products as $u){
                            ?>
                            <tr>
                                <td><?=$total++?></td>
                                <td ><?=$u[$tpTitle]?></td>
                                <td ><?=$general->numberFormat($u['stock'])?></td>
                                <td id="unitcost_<?=$u['id']?>"><?=$general->numberFormat($u['unit_cost'])?></td>
                                <td ><a href="javascript:void()" class="btn btn-success" onclick="productUnitCostUpdateInit(<?= $u['id'] ?>)">Update</a></td>




                            </tr>
                            <?php
                                $prodcutData[$u['id']]=[
                                    'pID'           =>$u['id'],
                                    'stock'         => $u['stock'],
                                    'title'         => $u['title'],
                                    'unit_cost'     => $u['unit_cost'],
                                ];
                            }
                        ?>
                    </tbody>

                </table>
            </div>
        </div>
    </div>
</div>
<script type=""> 
    <?php echo 'var prodcutData='.json_encode($prodcutData).';';?>

    function productUnitCostUpdateInit(pID){
        let p = prodcutData[pID];
        $('#productTitle').html(p.title);
        $('#pID').val(p.pID);
        $('#unitCost').val(p.unitCost);
        $("#unitCostUpdateBtn").click();
    }
    function productUnitCostUpdate(){
        buttonLoading('updateBtn');
        let pID = $('#pID').val();
        let unitCost = $('#unitCost').val();
        $.ajax({
            type:'post',
            url:ajUrl,
            data:{productUnitCostUpdate:1,pID:pID,unit_cost:unitCost},
            success:function(data){
                button_loading_destroy('updateBtn','Update');
                if(typeof(data.status)  !== "undefined"){ 
                    if(data.status==1){
                        $('#pID').val('');
                        $('#unitCost').val('');
                        $('#unitcost_'+pID).html(unitCost);
                        $(".close").click();
                    }
                    swMessageFromJs(data.m);
                }
                else{
                    swMessage('Some problem there. Please try again later'); 
                }
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) { 
                button_loading_destroy('updateBtn','Update');
                swMessage('Some problem there. Please try again later');
            }
        });
    }



</script>
<div style="display: none;">
    <a href="javascript:void()" data-toggle="modal" data-target="#unitCostUpdateModul" id="unitCostUpdateBtn" href="javascript:void()">ddddddd</a>
</div>
<div class="modal fade" id="unitCostUpdateModul" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title d-inline-block" id="productTitle">Cost</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="pID"> 

                <div class="form-group">
                    <label for="payNote" class="col-form-label">Unit Cost :</label>
                    <input type="text" class="form-control" id="unitCost">
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button updateBtn" onclick="productUnitCostUpdate()" class="btn btn-info">Update</button>
            </div>
        </div>
    </div>
</div>

