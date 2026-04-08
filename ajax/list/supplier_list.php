<?php
    $q=array();
    $q[]='isActive in(1,0)';
    $sq='where '.implode(' and ',$q);
    $suppliers=$db->selectAll('suppliers',$sq);
    $rData=[];
    $sr=1;
    if(!empty($suppliers)){
        $types=$smt->get_all_product_type();
        foreach($suppliers as $k=>$sup){
            $status=$sup['isActive']==1?'Active':'Inactive';
            $rData[] = [
                //'s'=>$sr++,
                'name'=>$sup['name'],
                'code'=>$sup['code']??'',
                'type'=>$types[$sup['product_type']]['title']??'',
                'contact_person'=>$sup['contact_person'],
                'mobile'=>$sup['mobile'],
                'status'=>$status,
                'edit'=>'<a href="'.URL.'?mdl=supplyer&edit='.$sup['id'].'" class="btn btn-info">Edit</a>',
            ];
        }
    }
    $general->arraySortByColumn($rData,'code');
    foreach($rData as $k=>$v){
        $rData[$k]['s']=$k+1;
    }
    $fileName='supplier_list'.TIME.rand(0,999).'.txt';
    $report_data=array(
        'name'      => 'supplier_list',
        'title'     => 'Supplier List',
        'fileName'  => $fileName,
        'head'=>array(
            array('title'=>"#"            ,'key'=>'s','hw'=>5),
            array('title'=>"Name"         ,'key'=>'name'),
            array('title'=>"Code"         ,'key'=>'code'),
            array('title'=>"Type"       ,'key'=>'type'),
            array('title'=>"Contact person",'key'=>'contact_person'),
            array('title'=>"Mobile"     ,'key'=>'mobile'),
            array('title'=>"status"     ,'key'=>'status'),
            array('title'=>"Edit"         ,'key'=>'edit'),

        ),
        'data'=>$rData
    );
    //$jArray[__LINE__]=$report_data;
    $gAr['report_data']= $report_data;
    textFileWrite(json_encode($report_data),ROOT_DIR.'/print_file/'.$fileName);
    $jArray['html']     = $general->fileToVariable(ROOT_DIR.'/ajax/report.phtml');
    $jArray['status']=1;
