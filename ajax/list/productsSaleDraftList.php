<?php
    $dRange = $_POST['dRange'];
    $base_id=intval($_POST['base_id']);
    $general->getFromToFromString($dRange,$from,$to);
    $q=['isActive=1','date between '.$from.' and '.$to];

    if($base_id>0){
        $q[]='base_id='.$base_id;
    }


    $draft = $db->selectAll('sale_draft','where '.implode(' and ',$q));
    $rData=[];
    $s=1;
    if(!empty($draft)){
        $cIDs=[];
        foreach($draft as $d){
            $cIDs[$d['customer_id']] = $d['customer_id'];
        }
        $customer = $db->selectAll('customer','where id in('.implode(',',$cIDs).')','id,name');

        if(!empty($customer)){$general->arrayIndexChange($customer,'id');}
        foreach($draft as $d){
            $customer_name='N/A';
            $base_name='N/A';
            if($d['base_id']){
                $b=$smt->base_info_by_id($d['base_id']);
                $customer_name=@$customer[$d['customer_id']]['name'];
                $base_name=$b['title'];
            }
            else{
                $b['title']='';
            }
            
            $rData[]=[
                's'=>$s++,
                'c'=>$customer_name,
                'b'=>$base_name,
                'd'=>$general->make_date($d['date']),
                'p'=>'<a href="'.URL.'/?mdl=sale&draftID='.$d['id'].'" class="btn btn-info">Process</a><a href="'.URL.'/?mdl=productsSaleDraftList&cancel='.$d['id'].'" class="btn btn-danger">Cancel</a>'
            ];
        }
    }
    ///$general->printArray($rData);
    $fileName='productsSaleDraftList'.TIME.rand(0,999).'.txt';
    $report_data=array(
        'name'      => 'productsSaleDraftList',
        'title'     => 'Products Sale Draft List',
        'fileName'  => $fileName,
        'head'=>array(
            array('title'=>"#"              ,'key'=>'s','hw'=>5),
            array('title'=>"Base"           ,'key'=>'b'),
            array('title'=>"Customer"       ,'key'=>'c'),
            array('title'=>"Sale Date"      ,'key'=>'d'),
            array('title'=>"Process"        ,'key'=>'p'),

        ),
        'data'=>$rData
    );
    $gAr['report_data']= $report_data;
    textFileWrite(json_encode($report_data),ROOT_DIR.'/print_file/'.$fileName);
    $jArray['html']     = $general->fileToVariable(ROOT_DIR.'/ajax/report.phtml');
    $jArray['status']=1;