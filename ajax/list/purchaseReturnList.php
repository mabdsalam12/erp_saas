<?php
    $dRange = $_POST['dRange'];
    $general->getFromToFromStringt($dRange,$from,$to);
    $q=['purDate between '.$from.' and '.$to];
    $purchases = $db->selectAll('purchase','where '.implode(' and ',$q),'purID,supID,purDate,subTotal,netTotal,createdBy');
    $rData=[];
    if(!empty($purchases)){
        $supIDs = [];
        $uIDs = [0=>0];
        foreach($purchases as $s){
            $supIDs[$s['supID']] = $s['supID'];
            $uIDs[$s['createdBy']] = intval($s['createdBy']);
        }
        $suppliers = $db->selectAll('suppliers','where supID in('.implode(',',$supIDs).')','supID,supName');
        $general->arrayIndexChange($suppliers,'supID');
        $users = $db->selectAll('users','where uID in('.implode(',',$uIDs).')','uID,username');
        $general->arrayIndexChange($users,'uID');
        $ss=1;
        foreach($purchases as $s){
            $rData[]=[
                's'=>$ss++,
                'c'=>$suppliers[$s['supID']]['supName'],
                'd'=>$general->make_date($s['purDate']),
                'u'=>@$users[$s['uID']]['username'],
                'a'=>$general->numberFormat($s['netTotal']),
                'r'=>'<button class="btn btn-danger" onclick="purchaseReturnInit('.$s['purID'].')">Return</button>'
            ];

        }
    }
    $fileName='purchaseReturnList'.TIME.rand(0,999).'.txt';
    $report_data=array(
        'name'      => 'purchaseReturnList',
        'title'     => 'Purchase Return List',
        'fileName'  => $fileName,
        'head'=>array(
            array('title'=>"#"              ,'key'=>'s','hw'=>5),
            array('title'=>"Supplier"       ,'key'=>'c'),
            array('title'=>"Sale Date"      ,'key'=>'d'),
            array('title'=>"Sale by"        ,'key'=>'u'),
            array('title'=>"Total Amount"   ,'key'=>'a' ,'al'=>'r'),
            array('title'=>"Return"         ,'key'=>'r'),

        ),
        'data'=>$rData
    );
    //$jArray[__LINE__]=$report_data;
    $gAr['report_data']= $report_data;
    textFileWrite(json_encode($report_data),ROOT_DIR.'/print_file/'.$fileName);
    $jArray['html']     = $general->fileToVariable(ROOT_DIR.'/ajax/report.phtml');
    $jArray['status']=1;
?>
