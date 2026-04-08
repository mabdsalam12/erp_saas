<?php
    $dRange = $_POST['dRange'];
    $general->getFromToFromStringt($dRange,$from,$to);
    $q=['sDate between '.$from.' and '.$to];
    $sales = $db->selectAll('sale','where '.implode(' and ',$q),'sID,cID,sDate,total,createdBy');
    $rData=[];
    if(!empty($sales)){
        $cIDs = [];
        $uIDs = [0=>0];
        foreach($sales as $s){
            $cIDs[$s['cID']] = $s['cID'];
            $uIDs[$s['createdBy']] = intval($s['createdBy']);
        }
        $customers = $db->selectAll('customer','where cID in('.implode(',',$cIDs).')','cID,cName');
        $general->arrayIndexChange($customers,'cID');
        $users = $db->selectAll('users','where uID in('.implode(',',$uIDs).')','uID,username');
        $general->arrayIndexChange($users,'uID');
        $ss=1;
        foreach($sales as $s){
            $rData[]=[
                's'=>$ss++,
                'c'=>$customers[$s['cID']]['cName'],
                'd'=>$general->make_date($s['sDate']),
                'u'=>@$users[$s['uID']]['username'],
                'a'=>$general->numberFormat($s['total']),
                'r'=>'<button class="btn btn-danger" onclick="saleReturnInit('.$s['sID'].')">Return</button>'
            ];

        }
    }
    $fileName='saleReturnList'.TIME.rand(0,999).'.txt';
    $report_data=array(
        'name'      => 'saleReturnList',
        'title'     => 'Sale Return List',
        'fileName'  => $fileName,
        'head'=>array(
            array('title'=>"#"              ,'key'=>'s','hw'=>5),
            array('title'=>"Customer"       ,'key'=>'c'),
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
