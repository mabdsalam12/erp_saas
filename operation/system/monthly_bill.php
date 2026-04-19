<?php
    $eStatus=SUPERADMIN_USER==GROUP_ID?true:false;
    $general->arrayIndexChange($base,'id');
    if(isset($_GET['add'])){
        $data = [$pUrl=>$rModule['name'],1=>'Add'];
        $general->pageHeader('Add '.$rModule['name'],$data);
        if(isset($_POST['add'])){
            $date = strtotime($_POST['date']);
            $due_date = strtotime($_POST['due_date']);
            $amount = intval($_POST['amount']);
            if($date<1){$error=fl(); setMessage(36,'date');}
            if($due_date<1){$error=fl(); setMessage(36,'due date');}
            else if($amount<1){$error=fl(); setMessage(36,'amount');}
            else{
                $data=[
                    'date'=>$date,
                    'due_date'=>$due_date,
                    'amount'=>$amount,
                    'createdBy'=>USER_ID,
                    'createdOn'=>TIME
                ];
                $insert=$db->insert('monthly_bill',$data);
                if(!$insert){$error=fl(); setMessage(66);}
                else{
                    $general->redirect($pUrl,29,'monthly bill');
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
                            <div class="col-xs-6 col-sm-4">
                                <?php $general->inputBoxText('date','Date',@$_POST['date'],'','daterangepicker');?>
                                <?php $general->inputBoxText('due_date','Due date',@$_POST['due_date'],'','daterangepicker');?>
                                <?php $general->inputBoxText('amount','Amount',@$_POST['amount'],'y');?>
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

    elseif(isset($_GET['edit'])&&$eStatus==true){
        $id=intval($_GET['edit']);
        $bill=$db->get_rowData('monthly_bill','id',$id);
        if(empty($bill)){
            setMessage(64,'monthly bill');
            $general->redirect($pUrl,63,'edit request');
        }
        else{
            $data = [$pUrl=>$rModule['name'],1=>'Edit'];
            $general->pageHeader('Edit '.$rModule['name'],$data);
            if(isset($_POST['edit'])){
                $date = strtotime($_POST['date']);
                $due_date = strtotime($_POST['due_date']);
                $pay_date = strtotime($_POST['pay_date']);
                $amount = intval($_POST['amount']);
                if($date<1){$error=fl(); setMessage(36,'date');}
                if($due_date<1){$error=fl(); setMessage(36,'due date');}
                else if($amount<1){$error=fl(); setMessage(36,'amount');}
                else{
                    $data=[
                        'date'      => $date,
                        'due_date'  => $due_date,
                        'amount'    => $amount
                    ];
                    if($pay_date>0){
                        $data['pay_date']=$pay_date;
                    }
                    $update=$db->update('monthly_bill',$data,['id'=>$id],'d');
                    if(!$update){$error=fl(); setMessage(2,'Bill update successfully');}
                    else{
                        $general->redirect($pUrl,30,'monthly bill');
                    }
                }
            }
            $pv='';
            if($bill['pay_date']>0){
                $pv=date('d-m-Y',$bill['pay_date']);
            }
    ?>
        <div class="row"><div class="col-lg-12"><?php show_msg();?></div></div>
        <div class="row">
            <div class="col-sm-12">
                <div class="white-box border-box">
                    <div class="row">
                        <div class="col-md-12">
                            <form method="post" action="">
                                <div class="col-xs-6 col-sm-4">
                                    <?php $general->inputBoxText('date','Date',date('d-m-Y',$bill['date']),'','daterangepicker');?>
                                    <?php $general->inputBoxText('due_date','Due date',date('d-m-Y',$bill['due_date']),'','daterangepicker');?>
                                    <?php $general->inputBoxText('pay_date','Pay date',$pv);?>
                                    <?php $general->inputBoxText('amount','Amount',$bill['amount'],'y');?>
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
    }
    else{
        $data = [$pUrl=>$rModule['name']];
        $general->pageHeader($rModule['name'],$data,$general->addBtnHtml($pUrl));
        $bills=$db->selectAll('monthly_bill','order by date desc');  
    ?>

    
<div class="row">
    <div class="col-sm-12">
        <div class="white-box">
            <?php
                show_msg();
            ?>
            <div class="col-md-5">
            <table class="table table-striped table-bordered table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Due Date</th>
                        <th class="amount_td">Amount</th>
                        <th>Pay status</th>
                        <th>Details</th>
                        <?php
                        if($eStatus==true){
                            ?>
                            <th>Edit</th>
                            <?php
                        }
                        ?>
                    </tr>
                </thead> 
                <tbody>
                    <?php
                        $total=1;
                        foreach($bills as $bill){
                            $pay_date='<button class="btn btn-lg btn-success pay_buttone" data-id="'.$bill['id'].'">Pay now</button>';
                            if($bill['pay_date']>0){
                                $pay_date=$general->make_date($bill['pay_date']);
                            }
                        ?>
                        <tr>
                            <td><?=$total++?></td>
                            <td><?=$general->make_date($bill['date'])?></td>
                            <td><?=$general->make_date($bill['due_date'])?></td>
                            <td class="amount_td"><?=$bill['amount']?></td>
                            <td><?=$pay_date?></td>
                            <td>
                                <button class="btn btn-info bill_details btn-lg" data-id="<?=$bill['id']?>">Details</button>
                            </td>
                            <?php
                            if($eStatus==true){
                                ?>
                                <td>
                                    <a class="btn btn-warning edit_bill btn-lg" href="<?= $pUrl ?>&edit=<?= $bill['id'] ?>">Edit</a>
                                </td>
                                <?php
                            }
                            ?>
                        </tr>
                        <?php
                        }
                    ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).on('click', '.bill_details', function() {
        let billId = $(this).data('id');
        get_monthly_bill_details(billId);
    });
    $(document).on('click', '.pay_buttone', function() {
        let billId = $(this).data('id');
        pay_monthly_bill(billId);
    });
    function get_monthly_bill_details(billId){
        $('#details-body').html('<h2>Loading...</h2>');
        $('#details-modal-title').html('Monthly Bill Details');
        $.ajax({
            url: ajUrl,
            method: 'POST',
            data: {get_monthly_bill_details:1, id: billId },
            success: function(response) {
                if(response.status==1){
                    $('#details-body').html(response.html);
                }
            }
        });
        $('#details-modal-btn').click();
    }
    function pay_monthly_bill(billId){
        $.ajax({
            url: ajUrl,
            method: 'POST',
            data: {pay_monthly_bill:1, id: billId },
            success: function(response) {
                if(response.status==1){
                    //redirect a new url form resposne.url
                    window.location.href = response.url;
                }
            }
        });
    }
</script>
    <?php
    }
?>
<?php
include_once ROOT_DIR.'/common/details_modal.php';