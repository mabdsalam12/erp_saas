<?php
    $dRange =$_POST['dRange'];
    $general->getFromToFromString($dRange,$from,$to);

    $order = $db->selectAll('purchase_requisition','where date  between '.$from.' and '.$to);
    $rData=[];
    $sr=1;
    if(!empty($order)){
        $user_ids=[];
        foreach($order as $o){
           $user_ids[$o['createdBy']]= $o['createdBy'];
        }
        $users = $db->selectAll('users','where id in('.implode(',',$user_ids).')','id,name');
        $general->arrayIndexChange($users);
        foreach($order as $o){
            $rData[]=[
                's'=>$sr++,
                'date'=>$general->make_date($o['date']),
                'note'=>$o['note'],
                'i'=>$o['code'],
                'user'=>$users[$o['createdBy']]['name']??'',
                'p'=>'<button onclick="purchase_requisition_details_view('.$o['id'].')" class="btn btn-success">Details</button>
                <a href="'.URL.'/?print=purchase_requisition&id='.$o['id'].'" target="_blank" class="btn btn-success">Print</a>',
            ];
        }
    }
    $fileName='purchase_requisition_list'.TIME.rand(0,999).'.txt';
    $report_data=array(
        'name'      => 'purchase_requisition_list',
        'title'     => 'Purchase requisition list',
        'fileName'  => $fileName,
        'head'=>array(
            array('title'=>"#"          ,'key'=>'s','hw'=>5),
            array('title'=>"ID"         ,'key'=>'i'),
            array('title'=>"Date"       ,'key'=>'date'),
            array('title'=>"Note"       ,'key'=>'note'),
            array('title'=>"User"       ,'key'=>'user'),
            array('title'=>"Details"      ,'key'=>'p'),
        ),
        'data'=>$rData
    );
    $gAr['report_data']= $report_data;
    textFileWrite(json_encode($report_data),ROOT_DIR.'/print_file/'.$fileName);
    $jArray['html']     = $general->fileToVariable(ROOT_DIR.'/ajax/report.phtml');
    $jArray['status']=1;
