<?php
if($_GET['print']=='sale'){
    $id=intval($_GET['id']);
    $s = $smt->saleInfoByID($id);
    if(!empty($s)){
        include(__DIR__."/sale.php");
        
    }
    else{
        echo 'Invalid Sale print request.';
    }
}
else if($_GET['print']=='gift_distribute'){
    $id=intval($_GET['id']);
    $g=$db->get_rowData('gift_distribute','id',$id);
    if(!empty($g)){
        include(__DIR__."/gift_distribute.php");
    }
    else{
        echo 'Invalid Gift distribute print request.';
    }
}
elseif($_GET['print']=='voucher'){
    $veID=intval($_GET['veID']);
    include("print/voucher.php");
}elseif($_GET['print']=='empSalary'){
    $eID=intval($_GET['eID']);
    include("print/empSalary.php");   
}
else{
    echo 'Invalid request';
}