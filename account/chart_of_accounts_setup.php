<?php
$chart_of_accounts = $acc->chart_of_accounts();

$chart_accounts = $db->selectAll('a_charts_accounts','','id,title,code');
$general->arrayIndexChange($chart_accounts);
$company_data = $db->get_company_data();
$chartOfAccount = $company_data['chart_of_account']??[];
if(isset($_GET['edit'])){
    $edit = $_GET['edit'];
    if(!isset($chart_of_accounts[$edit])){
        $general->redirect($pUrl,37,$rModule['name']);
    }
    if(isset($_POST['edit'])){
        $id = intval($_POST['id']);
        $chartOfAccount[$edit]=$id;
        foreach($chartOfAccount as $k=>$c){
            if(!isset($chart_of_accounts[$k])){unset($chartOfAccount[$k]);}
        }
        $company_data['chart_of_account']=$chartOfAccount;
        

        $update = $db->company_data_update($company_data);
        if(!$update){$error=fl();setMessage(66);}
        else{$general->redirect($pUrl,30,'Chart of Account');}

    }
    $data = [$pUrl=>$rModule['name'],1=>'Edit'];
    $general->pageHeader('Edit '.$chart_of_accounts[$edit]['title'],$data);
    $value = $chartOfAccount[$edit]??0;
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
                                <?php $general->inputBoxSelect($chart_accounts,'Chart of Account','id','id','title',$value);?>

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
    $data = [$pUrl=>$rModule['name']];
    $general->pageHeader($rModule['name'],$data);
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
                        <th>Chart of Account</th>
                        <th>Edit</th>
                    </tr>
                </thead> 
            
                <tbody>
                    <?php
                        $sr=1;
                        foreach($chart_of_accounts as $id=>$b){
                            $chart_account_id = $chartOfAccount[$id]??0;
                            $title = $chart_accounts[$chart_account_id]['title']??'';
                            $code= $chart_accounts[$chart_account_id]['code']??'';
                            ?>
                            <tr>
                            <td><?=$sr++?></td>
                            <td><?=$b['title']?></td>
                            <td><?=$b['id']?></td>
                            <td><?=$code?> <?=$title?> (<?=$chart_account_id?>)</td>
                            
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