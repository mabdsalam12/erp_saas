<?php
$ledger_accounts = $acc->ledger_accounts();

$ledgers = $db->selectAll('a_ledgers','','id,title,code');
$general->arrayIndexChange($ledgers);
$company_data = $db->get_company_data();
$ledgerAccounts = $company_data['ledger_accounts']??[];
if(isset($_GET['edit'])){
    $edit = $_GET['edit'];
    if(!isset($ledger_accounts[$edit])){
        $general->redirect($pUrl,37,$rModule['title']);
    }
    if(isset($_POST['edit'])){
        $id = intval($_POST['id']);
        $ledgerAccounts[$edit]=$id;
        foreach($ledgerAccounts as $k=>$c){
            if(!isset($ledger_accounts[$k])){unset($ledgerAccounts[$k]);}
        }
        $company_data['ledger_accounts']=$ledgerAccounts;
        

        $update = $db->company_data_update($company_data);
        if(!$update){$error=fl();setMessage(66);}
        else{$general->redirect($pUrl,30,'Ledger Account');}

    }
    $data = [$pUrl=>$rModule['title'],'javascript:void()'=>$ledger_accounts[$edit]['title'],1=>'Edit'];
    $general->pageHeader('Edit '.$ledger_accounts[$edit]['title'],$data);
    $value = $ledgerAccounts[$edit]??0;
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
                                <?php $general->inputBoxSelect($ledgers,'Ledger','id','id','title',$value);?>

                            </div>

                        </div>
                        <div class="row">
                            <?= $general->editBtn();?>
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
    $data = [$pUrl=>$rModule['title']];
    $general->pageHeader($rModule['title'],$data);
?>

<div class="col-sm-12">
    <div class="white-box border-box">
        <?php
            show_msg();
        ?>
        <div class="row">
            <div class="col-sm-12 col-lg-12" id="reportArea">
            <table class="table table-striped table-bordered table-hover only_show">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Key</th>
                        <th>Ledger Account</th>
                        <th>Edit</th>
                    </tr>
                </thead> 
            
                <tbody>
                    <?php
                        $sr=1;
                        foreach($ledger_accounts as $id=>$b){
                            $ledger_account_id = $ledgerAccounts[$id]??0;
                            $title = $ledgers[$ledger_account_id]['title']??'';
                            $code= $ledgers[$ledger_account_id]['code']??'';
                            ?>
                            <tr>
                            <td><?=$sr++?></td>
                            <td><?=$b['title']?></td>
                            <td><?=$b['id']?></td>
                            <td><?=$code?> <?=$title?> (<?=$ledger_account_id?>)</td>
                            
                            <td><a href="<?=$pUrl?>&edit=<?=$b['id']?>" class="btn btn-info">Edit</a></td>
                            
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
<?php
}