<?php
$aStatus      = $db->permission(35);
$eStatus      = $db->permission(36);


$tpID           = 'id';
$assets_type=$db->selectAll('fixed_assets_type','where isActive =1 order by title asc');
$general->arrayIndexChange($assets_type);
$cash_accounts=$acc->get_all_cash_accounts();


if(isset($_GET['add'])){                                        
    $data = array($pUrl=>$rModule['name'],1=>'Add');
    $general->pageHeader('Add '.$rModule['name'],$data);
    if(isset($_POST['add'])){
        $date           = intval(strtotime($_POST["date"]));
        $assets_type_id = intval($_POST["assets_type"]);
        $product        = $_POST["product"];
        $quantity       = intval($_POST["quantity"]);
        $unit_price     = floatval($_POST["unit_price"]);
        $bank_id        = intval($_POST["bank_id"]);


        if(empty($product)){setMessage(36,'Product');$error=fl();}
        if($date==0){$error=fl();setMessage(63,'Date');}
        if($assets_type_id==0){$error=fl();setMessage(63,'Fixed Assets Type');}
        if($quantity<1){$error=fl();setMessage(63,'Quantity');}
        if($unit_price<=0){$error=fl();setMessage(63,'Unit Price');}
        if(!isset($cash_accounts[$bank_id])){$error=fl(); setMessage(63,'Bank');}

        if(!isset($error)){
            if (date('Y-m-d', $date) === date('Y-m-d')) {
                $date = time(); // update to current timestamp
            }

            $total_amount=$quantity*$unit_price;
            $data = array(
                'type_id'   => $assets_type_id,
                'product'   => $product,
                'quantity'  => $quantity,
                'unit_price'=> $unit_price,
                'total'     => $total_amount, 
                'current_value'=> $total_amount, 
                'time'      => $date
            );
            $db->arrayUserInfoAdd($data);
            $db->transactionStart();
            $id=$db->insert('fixed_assets',$data,'getId');
            if($id!=false){
                $vType=V_T_FIXED_ASSETS_PURCHASE;
                $fixedAssetsHead=$acc->getSystemHead(AH_FIXED_ASSETS);
                if($fixedAssetsHead==false){$error=fl();setMessage(66);}

                $debit=$fixedAssetsHead;
                $credit=$bank_id;
                $note='Fixed Assets '.$product;

                $voucher=$acc->voucher_create($vType,$total_amount,$debit,$credit,$date,$note,$id);
                if($voucher==false){$error=fl();setMessage(66);}
            }
            else{
                $error=fl();SetMessage(66);
            }
            if(!isset($error)){
                $ac=true;
            }
            else{
                $ac=false;
            }
            $db->transactionStop($ac);
            if(!isset($error)){
                $general->redirect($pUrl,29,'Fixed Assets');
            }
            else{
                setErrorMessage($error);
            }
        }
    }
    ?>
    <div class="row"><div class="col-lg-12"><?php show_msg();?></div></div>
    <div class="row">
        <div class="col-sm-12">
            <div class="white-box border-box">
                <div class="row">
                    <div class="col-md-12">
                        <form method="post" action="">
                            <div class="row">
                                <div class="col-xs-6 col-sm-4">
                                    <?php $general->inputBoxText('date','Date',@$_POST['date'],'y','daterangepicker_e');?>
                                    <?php $general->inputBoxText('quantity','Quantity',@$_POST['quantity'],'y');?>
                                    <?php 
                                    $general->inputBoxSelect($cash_accounts,'Bank / Cash','bank_id','id','title');
                                    ?>
                                </div>
                                <div class="col-xs-6 col-sm-4">

                                    <div class="form-group row">
                                        <label class="col-md-4 col-form-label" for="edID">Type <?php echo BOOTSTRAP_REQUIRED;?></label>
                                        <div class="col-md-8">
                                            <?php
                                            $db->dropdownInputFromArray($assets_type,'assets_type','id','title',@$_POST['assets_type'],'y','','','Select assets type');
                                            ?>
                                        </div>
                                    </div>
                                    <?php $general->inputBoxText('unit_price','Unit price',@$_POST['unit_price'],'y');?>
                                </div>
                                <div class="col-xs-6 col-sm-4">
                                    <?php $general->inputBoxText('product','Product',@$_POST['product'],'y');?>
                                    <div class="form-group row">
                                        <label for="qty" class="col-md-4 col-form-label">Total</label>
                                        <div class="col-md-8">
                                            <div class="input-group qty-input-group">
                                                <input readonly class="form-control amount_td" value="<?php echo @$_POST['total'];?>" placeholder="Total" type="text" name="total" id="total">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <?php echo $general->addBtn();?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php

}
elseif(isset($_GET['edit'])){
    if($eStatus==false){$general->redirect($pUrl,146,'Edit');}
    $edit = intval($_GET['edit']);
    $e = $db->get_rowData('fixed_assets',$tpID,$edit);
    if(empty($e)){$general->redirect($pUrl,37,$rModule['name']);}
    $general->arrayContentShow($e);
    if(isset($_POST['edit'])){
        $jArray=[];
        $date           = intval(strtotime($_POST["date"]));
        $assets_type_id = intval($_POST["assets_type"]);
        $product        = $_POST["product"];
        $quantity       = intval($_POST["quantity"]);
        $unit_price     = floatval($_POST["unit_price"]);


        if(empty($product)){setMessage(36,'Product');$error=fl();}
        if($date==0){$error=fl();setMessage(63,'Date');}
        if($assets_type_id==0){$error=fl();setMessage(63,'Fixed Assets Type');}
        if($quantity<1){$error=fl();setMessage(63,'Quantity');}
        if($unit_price<=0){$error=fl();setMessage(63,'Unit Price');}

        if(!isset($error)){
            if (date('Y-m-d', $date) === date('Y-m-d')) {
                $date = time(); 
            }
            $pay_date=0;
            if(date('Y-m-d', $date)!=date('Y-m-d', $e['time'])){
                $pay_date =$date;
            }

            $total_amount=$quantity*$unit_price;
            $data = array(
                'type_id'   => $assets_type_id,
                'product'   => $product,
                'quantity'  => $quantity,
                'unit_price'=> $unit_price,
                'total'     => $total_amount, 
                'current_value'=> $total_amount, 
                'time'      => $date
            );
            $db->arrayUserInfoEdit($data);
            $db->transactionStart();
            $where=array($tpID=>$edit);
            $update=$db->update('fixed_assets',$data,$where);
            if($update){
                if(floatval($e['total'])!=$total_amount){
                    $old_voucher=$acc->voucherDetails(V_T_FIXED_ASSETS_PURCHASE,$edit);
                    $o=current($old_voucher);
                    if(!empty($old_voucher)){
                        $fixedAssetsHead=$acc->getSystemHead(AH_FIXED_ASSETS);
                        if($fixedAssetsHead==false){$error=fl();setMessage(66);}

                        $dHead=$fixedAssetsHead;
                        $cHead=$o['credit'];
                        $update=$acc->voucherEdit($o['id'],$total_amount,$o['note'],$dHead,$cHead,$pay_date,[],$jArray);
                        if($update==false){
                            $error=fl();setMessage(66);
                        }
                    } 
                }

            }
            else{
                $error=fl();SetMessage(66);
            }
            $ac=false;
            if(!isset($error)){
                $ac=true;
            }

            $db->transactionStop($ac);
            if($ac){
                $db->actionLogCreate('eID'.$e['id'].'_ fixedAssetsUpdate',$data);
                $general->redirect($pUrl,30,' Fixed Assets'); 
            }
        }
    }
    $data = array($pUrl=>$rModule['name'],'javascript:void()'=>$e['product'],'1'=>'Edit');
    $general->pageHeader('Edit '.$rModule['name'],$data);
    ?>
    <div class="row">
        <?php
        show_msg();
        ?>
    </div>  
    <div class="row">
        <div class="col-sm-12">
            <div class="white-box border-box">
                <div class="row">
                    <div class="col-md-12">
                        <form method="post" action="">
                            <div class="row">
                                <div class="col-xs-6 col-sm-4">
                                    <?php $general->inputBoxText('date','Date',@$general->make_date($e['time']),'y','daterangepicker_e');?>
                                    <?php $general->inputBoxText('quantity','Quantity',intval(@$e['quantity']),'y');?>
                                </div>
                                <div class="col-xs-6 col-sm-4">

                                    <div class="form-group row">
                                        <label class="col-md-4 col-form-label" for="edID">Type <?php echo BOOTSTRAP_REQUIRED;?></label>
                                        <div class="col-md-8">
                                            <?php
                                            $db->dropdownInputFromArray($assets_type,'assets_type','id','title',@$e['type_id'],'y','','','Select assets type');
                                            ?>
                                        </div>
                                    </div>
                                    <?php $general->inputBoxText('unit_price','Unit price',@floatval($e['unit_price']),'y');?>
                                </div>
                                <div class="col-xs-6 col-sm-4">
                                    <?php $general->inputBoxText('product','Product',@$e['product'],'y');?>
                                    <div class="form-group row">
                                        <label for="qty" class="col-md-4 col-form-label">Total</label>
                                        <div class="col-md-8">
                                            <div class="input-group qty-input-group">
                                                <input readonly class="form-control amount_td" value="<?php echo floatval(@$e['total']);?>" placeholder="Total" type="text" name="total" id="total">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <?php echo $general->editBtn();?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}
else{
    $data = array($pUrl=>$rModule['name']);
    $general->pageHeader($rModule['name'],$data,$general->addBtnHtml($pUrl));
    ?>
    <div class="row">
        <div class="col-sm-12">
            <div class="white-box border-box">
                <?php
                show_msg();
                $q=[];
                if(isset($_GET['dRange'])){
                    $dRange=$_GET['dRange'];
                    if(!empty($dRange)){   
                        $general->getFromToFromString($dRange,$from,$to);
                        $q[]="time between $from and $to";
                    }
                }

                if(isset($_GET['assets_type'])){
                    $type=intval($_GET['assets_type']);
                    if(!empty($type)){   
                        $q[]='designation_id='.$type;
                    }
                }
                $sq='';
                if(!empty($q)){
                    $sq='where '.implode(' and ',$q);
                }

                $assets=$db->selectAll('fixed_assets',$sq.' order by time asc');
                ?>
                <div class="row">
                    <div class="col-sm-12 col-lg-12 padding-left-0">

                        <form action="" method="get">
                            <input type="hidden" name="<?php echo MODULE_URL;?>" value="<?php echo $rModule['slug'];?>">
                            <div class="col-md-2">
                                <h5 class="box-title">Date</h5>  
                                <input type="text" class="form-control daterangepickerMulti" name="dRange" value="<?php echo @$_GET['dRange']?>" >  
                            </div>
                            <div class="col-md-2">
                                <h5 class="box-title">Type</h5>
                                <?php
                                $db->dropdownInputFromArray($assets_type,'assets_type','id','title',@$_GET['$assets_type'],'','form-control select2');                
                                ?>
                            </div>
                            <div class="col-md-2">
                                <h5 class="box-title">Search</h5>
                                <input type="submit" value="Search" class="btn btn-success" name="s">
                            </div>
                        </form>
                    </div>
                </div>
                <table class="table table-striped table-bordered table-hover only_show">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Type</th>
                            <th>Product</th>
                            <th>Date</th>
                            <th class="amount_td">Unit Price</th>
                            <th>Quantity</th>
                            <th class="amount_td">Total Amount</th>
                            <th>Depreciation</th>
                            <th>Depreciation history</th>
                            <th>Current Value</th>
                            <th>Edit</th>
                        </tr>
                    </thead> 
                    <tbody>
                        <?php
                        $sl=1;
                        foreach($assets as $e){
                            $type_title='';
                            if(isset($assets_type[$e['type_id']])){
                                $type_title=$assets_type[$e['type_id']]['title'];
                            }

                            ?>
                            <tr>
                                <td><?=$sl++?></td>
                                <td><?=$type_title?></td>
                                <td><?=$e['product']?></td>
                                <td><?=$general->make_date($e['time'])?></td>
                                <td><?=$general->numberFormat($e['unit_price'])?></td>
                                <td><?=$general->numberFormat($e['quantity'],0)?></td>
                                <td><?=$general->numberFormat($e['total'])?></td>    
                                <td>
                                    <button type="button" class="btn btn-info" onclick="depreciationForm(<?=$e['id']?>)">Depreciation</button>
                                </td>   
                                <td>
                                    <button type="button" class="btn btn-info" onclick="depreciationHistory(<?=$e['id']?>)">History</button>
                                </td>  
                                <td><?=$general->numberFormat($e['current_value'])?></td>
                                <td><a href="<?=$pUrl?>&edit=<?=$e[$tpID]?>" class="btn btn-info">Edit</a></td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php  
}
?>

<div class="modal fade" id="depreciationModal" tabindex="-1" aria-labelledby="depreciationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="depreciationModalLabel">Depreciation Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"> <!-- Updated for Bootstrap 4 -->
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <!-- Readonly Inputs -->
                <div class="form-group">
                    <label for="readonlyField1">Type</label>
                    <input type="hidden" id="fixed_assets_id" value="">
                    <input type="text" id="product_type" class="form-control" value="" readonly>
                </div>

                <div class="form-group">
                    <label for="readonlyField2">Product Name</label>
                    <input type="text" id="product_name" class="form-control" value="" readonly>
                </div>

                <div class="form-group">
                    <label for="readonlyField2">Purchase Date</label>
                    <input type="text" id="purchase_date" class="form-control" value="" readonly>
                </div>

                <div class="form-group">
                    <label for="readonlyField2">Depreciation</label>
                    <input type="text" id="depreciation_value" class="form-control" value="" readonly>
                </div>

                <div class="form-group">
                    <label for="readonlyField2">Current value</label>
                    <input type="text" id="current_value" class="form-control" value="" readonly>
                </div>

                <!-- Editable Inputs -->
                <div class="form-group">
                    <label for="depreciation_date">Date</label>
                    <input type="text" id="depreciation_date" class="form-control" placeholder="">
                </div>

                <div class="form-group">
                    <label for="depreciation_amount">Depreciation Amount</label>
                    <input type="text" id="depreciation_amount" class="form-control" placeholder="Enter amount">
                </div>

                <div class="form-group">
                    <label for="depreciation_notes">Notes</label>
                    <input type="text" id="depreciation_notes" class="form-control" placeholder="Enter notes">
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" id="" onclick="depreciationSave()" class="btn btn-success depreciationSaveBtn">Save</button>
            </div>

        </div>
    </div>
</div>  

<div class="modal fade" id="depreciationHistoryModal" tabindex="-1" aria-labelledby="depreciationHistoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered"> <!-- centered in the screen -->
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="depreciationHistoryModalLabel">Depreciation History</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>SL</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Note</th>
                            </tr>
                        </thead>
                        <tbody id="depreciationHistoryTableBody">
                            <!-- Table rows will go here dynamically -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="close_modal('depreciationHistoryModal')">Close</button>
            </div>
        </div>
    </div>
</div>  

<script type="text/javascript">
    $(document).ready(function () {
        $('#quantity, #unit_price').on('keyup change', function () {
            let quantity = parseFloat($('#quantity').val()) || 0;
            let unitPrice = parseFloat($('#unit_price').val()) || 0;
            let total = quantity * unitPrice;

            $('#total').val(total.toFixed(2)); // show result with 2 decimals
        });
    });
    function close_modal(modalId){
        $('#'+modalId).modal('hide');
    }

    function depreciationSave(){
        var error=0;
        buttonLoading('depreciationSaveBtn');
        var assets_id =$('#fixed_assets_id').val();
        var dep_date=$('#depreciation_date').val();
        var dep_amount=parse_float($('#depreciation_amount').val());
        var dep_notes=$('#depreciation_notes').val();

        if(dep_amount<=0){swMessage('Please enter depreciation Amount.');error=1;}

        if(error==0){
            $.ajax({
                type:'post',
                url:ajUrl,
                data:{
                    depreciation_save:1,
                    assets_id:assets_id,
                    dep_date:dep_date,
                    dep_amount:dep_amount,
                    dep_notes:dep_notes
                },
                success:function(data){
                    button_loading_destroy('income_and_expense_add','Add');
                    if(typeof data.status !== 'undefined'){
                        if(data.status==1){
                            $('#depreciation_amount').val(''); 
                            $('#depreciation_notes').val('');
                            setTimeout(function() {
                                location.reload();
                                }, 3000);
                        }
                        swMessageFromJs(data.m);
                        button_loading_destroy('depreciationSaveBtn','Save'); 
                    }
                    else{
                        swMessage(AJAX_ERROR_MESSAGE);
                    }
                },
                error:function(data){
                    button_loading_destroy('depreciationSaveBtn','Save');
                    swMessage(AJAX_ERROR_MESSAGE);
                }
            });
        }else{
            button_loading_destroy('depreciationSaveBtn','Save');
        }
    }

    function depreciationHistory(assets_id){


        $.post(ajUrl,{get_depreciation_history:1,assets_id:assets_id},function(data){
            if(data.status==1){
                $.each(data.depreciations,function(a,b){
                    $('#depreciationHistoryTableBody').append(`
                        <tr>
                        <td>${a + 1}</td>
                        <td>${b.date}</td>
                        <td>${b.amount}</td>
                        <td>${b.note}</td>
                        </tr>
                        `);
                });

                $('#depreciationHistoryModal').modal('show'); 
            }
            else{
                swMessageFromJs(data.m);
            }
        });
    }
    function depreciationForm(assets_id){
        $.post(ajUrl,{get_fixed_assets:1,assets_id:assets_id},function(data){
            if(data.status==1){
                var assets= data.assets
                $('#fixed_assets_id').val(assets_id);
                $('#product_type').val(assets.type);
                $('#product_name').val(assets.product);
                $('#purchase_date').val(assets.date);
                $('#depreciation_value').val(assets.depreciation);
                $('#current_value').val(assets.current_value);

                $('#depreciationModal').modal('show');   
                setTimeout(function() {
                    $('#depreciation_date').daterangepicker({
                        singleDatePicker: true,
                        showDropdowns: true,
                        locale: {
                            format: 'DD-MM-YYYY'
                        }
                    });
                    }, 300);
            }
            else{
                swMessageFromJs(data.m);
            }
        });

    }
</script>