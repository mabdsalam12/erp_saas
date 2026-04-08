<?php
    $dRange = $_POST['dRange']  ;
    $base_id = intval($_POST['base_id']);
    $bazar_id=intval($_POST['bazar_id']);
    $reportInfo=['Date:  '.$dRange];
    $general->getFromToFromString($dRange,$from,$to);
    $rData=[];
    $sr=1;
    $q=["entry_time between $from  and  $to"];
    if($bazar_id>0){
        $q[]="bazar_id= $bazar_id";
    }  
    if($base_id>0){
        $bazars = $db->selectAll('bazar','where base_id='.$base_id,'id,title,base_id');
        $general->arrayIndexChange($bazars);
        if(!empty($bazars)){
            $q[]='bazar_id in('.implode(',',array_keys($bazars)).')';
        }
        else{
            $q[]='bazar_id=0';
        }
    } 
    $visit_list =$db->selectAll('bazar_visit','where '.implode(' and ',$q));
    if(!empty($visit_list)){
        $bazar_ids=[];
        $user_ids=[];
        foreach($visit_list as $vl){
            $bazar_ids[$vl['bazar_id']]=$vl['bazar_id'];
            $user_ids[$vl['createdBy']]=$vl['createdBy'];
        }
        if($base_id==0){
            $bazars = $db->selectAll('bazar','where id in ('.implode(',',$bazar_ids).')','id,title,base_id');
            $general->arrayIndexChange($bazars);
        }
        $user= $db->allUsers('and id in('.implode(',',$user_ids).')');
        $base = $db->selectAll('base');
        $general->arrayIndexChange($base);
        foreach($visit_list as $vl){
            $rData[]=[
                's'=>$sr++,
                'd'=>$general->make_date($vl['entry_time'],'time'),
                'ba'=>$base[$bazars[$vl['bazar_id']]['base_id']]['title']??'',
                'v'=>$bazars[$vl['bazar_id']]['title'],
                'r'=>$general->content_show($vl['note']),
                'c'=>$user[$vl['createdBy']]['name']??'',
                'de'=>'<button onclick="bazar_visit_details_view('.$vl['id'].')" class="btn btn-success">Details</button>',
            ];
        }
    }
    $head=[
        ['title'=>'SL'          ,'key'=>'s','hw'=>5],
        ['title'=>'Date'        ,'key'=>'d'],
        ['title'=>'Base'        ,'key'=>'ba'],
        ['title'=>'Bazar'       ,'key'=>'v'],
        ['title'=>'Note'        ,'key'=>'r' ],
        ['title'=>'Created by'  ,'key'=>'c' ],
        ['title'=>'Details'     ,'key'=>'de'],
    ];
    $fileName='bazar_visit_list_'.TIME.rand(0,999).'.txt';
    $report_data=[
        'name'      => 'bazar_visit_list',
        'title'     => 'bazar visit list',
        'info'      => $reportInfo,
        'head'=>$head,
        'data'=>$rData
    ];
    $gAr['report_data']= $report_data;
    textFileWrite(json_encode($report_data),ROOT_DIR.'/print_file/'.$fileName);
    $jArray['html']     = $general->fileToVariable(ROOT_DIR.'/ajax/report.phtml');
    $jArray['status']=1;
