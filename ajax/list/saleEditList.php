<?php
    $dRange = $_POST['dRange'];
    $general->getFromToFromString($dRange,$from,$to);
    $q=['date between '.$from.' and '.$to];
    $sales = $db->selectAll('sale','where '.implode(' and ',$q),'id,customer_id,date,total,createdBy');
    $rData=[];
    if(!empty($sales)){
        $cIDs = [];
        $uIDs = [0=>0];
        foreach($sales as $s){
            $cIDs[$s['customer_id']] = $s['customer_id'];
            $uIDs[$s['createdBy']] = intval($s['createdBy']);
        }
        $customers = $db->selectAll('customer','where id in('.implode(',',$cIDs).')','id,name');
        $general->arrayIndexChange($customers,'id');
        $users = $db->selectAll('users','where id in('.implode(',',$uIDs).')','id,username');
        $general->arrayIndexChange($users,'id');
        $ss=1;
        foreach($sales as $s){
            $rData[]=[
                's'=>$ss++,
                'c'=>$customers[$s['customer_id']]['name'],
                'd'=>$general->make_date($s['date']),
                'u'=>@$users[$s['createdBy']]['username'],
                'a'=>$general->numberFormat($s['total']),
                'r'=>'<a href="'.URL.'/?mdl=sale-update&edit='.$s['id'].'" class="btn btn-info">Eidt</a>'
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
            array('title'=>"Edit"         ,'key'=>'r'),

        ),
        'data'=>$rData
    );
    //$jArray[__LINE__]=$report_data;
    $gAr['report_data']= $report_data;
    textFileWrite(json_encode($report_data),ROOT_DIR.'/print_file/'.$fileName);
    $jArray['html']     = $general->fileToVariable(ROOT_DIR.'/ajax/report.phtml');
    $jArray['status']=1;
?>
