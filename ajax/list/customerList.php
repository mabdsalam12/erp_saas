<?php
    $cName=$_POST['cName'];
    $customer_id=$_POST['customer_id'];
    $cMobile=$_POST['cMobile'];
    $customer_category_id = intval($_POST['customer_category_id']);
    $status = intval($_POST['status']);
    $q=[];
    if($status==0){
        $q[]='isActive in(1,0)';
    }
    else{
        $q[]='isActive=3';
    }
    if($customer_category_id>0){
        $q[]="customer_category_id=$customer_category_id";
    }
    if(!empty($cName)){
        $q[]="name like '%".$cName."%'";
    }
    if(!empty($customer_id)){
        $q[]="code like '%".$customer_id."%'";
    }
    if(!empty($cMobile)){
        $q[]="mobile like '%".$cMobile."%'";
    }
    $base_id  = intval($_POST['base_id']);
    $bazar_id  = intval($_POST['bazar_id']);
    if($base_id>0){
        $q[]='base_id='.$base_id;
    }
    if($bazar_id>0){
        $q[]='bazar_id='.$bazar_id;
    }

    $sq='where '.implode(' and ',$q);
    $customer=$db->selectAll('customer',$sq.' order by code asc');
    $serial=1;
    $rData=[];
    $total=0;
    $total_credit_limit=0;
    if(!empty($customer)){  
        $base = $db->selectAll('base','','id,title');
        $bazars = $db->selectAll('bazar','','id,title');
        $general->arrayIndexChange($base,'id');
        $general->arrayIndexChange($bazars,'id');
        $customer_category = $db->selectAll('customer_category','where isActive=1','id,title');
    $general->arrayIndexChange($customer_category);
        foreach($customer as $c){
            $checkest='';
            if($c['isActive']==1){
                $checkest='checked="checked"'; 
            }
            $balance=$acc->headBalance($c['ledger_id']);
            $total+=$balance;
            $data=$general->getJsonFromString($c['data']);
            $credit_limit=0;
            if(isset($data['credit_limit'])){
                $credit_limit=$data['credit_limit'];
            }
            if(isset($data['credit_limit'])){
                $credit_limit=$data['credit_limit'];
            }
            $total_credit_limit+=$credit_limit;
            $rData[]=[
                's' => $serial++,
                'id' => $c['code'],
                'n' => $c['name'],
                'mpo' => $base[$c['base_id']]['title']??'',
                'bz' => $bazars[$c['bazar_id']]['title']??'',
                'cc' => $customer_category[$c['customer_category_id']]['title']??'',
                'm' => $c['mobile'],
                'c' => $c['owner_name'],
                'ad' => $c['address'],
                'b' => $general->numberFormat($balance,0),
                'cl' => $general->numberFormat($credit_limit,0),
                'd'=>$c['due_day'],
                'st' => '<a href="'.URL.'?mdl=customers&edit='.$c['id'].'" class="btn btn-info">Edit</a>',
                //'stu' => $general->onclickChangeBTN($c['cID'],$general->checked($c['isActive'])),
                'stu' =>'<div class="checkbox checkbox-info checkbox-circle"> <input type="checkbox" class="checkbox-circle" '.$checkest.' onclick="actinact('.$c['id'].',this.checked);"  id="act_'.$c['id'].'"  name="act_'.$c['id'].'"><label for="act_'.$c['id'].'"></label></div>',
                'e' => '<a href="'.URL.'?mdl=customerStatment&customer_id='.$c['id'].'&dRange='.date('d-m-Y').' to '.date('d-m-Y',strtotime('+30 day')).'" class="btn btn-info">Statment</a>',

            ];
        }

        $rData[]=[
            's' => '',
            'id' => ['t'=>'Total','b'=>1],
            'n' => '',
            'mpo' => '',
            'bz' => '',
            'cc' => '',
            'm' => '',
            'c' => '',
            'ad' => '',
            'b' => ['t'=>$general->numberFormat($total),'b'=>1],
            'cl' => ['t'=>$general->numberFormat($total_credit_limit),'b'=>1],
            'd'=>'',
            'st' => '',
            'stu' =>'',
            'e' => '',
        ]; 
    }
    $fileName='customerList'.TIME.rand(0,999).'.txt';
    $report_data=array(
        'name'      => 'purRep',
        'title'     => 'Profit Distribute List',
        'fileName'  => $fileName,
        'head'=>array(
            array('title'=>"#"            ,'key'=>'s','hw'=>5),
            array('title'=>"ID"         ,'key'=>'id'),
            array('title'=>"Name"         ,'key'=>'n'),
            array('title'=>"Base"         ,'key'=>'mpo'),
            array('title'=>"Bazar"         ,'key'=>'bz'),
            array('title'=>"Category"         ,'key'=>'cc'),
            array('title'=>"Mobile"       ,'key'=>'m'),
            array('title'=>"Owner name"      ,'key'=>'c'),
            array('title'=>"Address"      ,'key'=>'ad'),
            array('title'=>"Balance"      ,'key'=>'b','al'=>'r'),
            array('title'=>"CL"         ,'key'=>'cl','al'=>'r'),
            array('title'=>"Due day"         ,'key'=>'d','al'=>'r'),
            array('title'=>"Statement"     ,'key'=>'st'),
            
            

        ),
        'data'=>$rData
    );
    if($status==0){
       $report_data['head'][] =  array('title'=>"Status"       ,'key'=>'stu');
    }
    $report_data['head'][] = array('title'=>"Edit"         ,'key'=>'e');
    //$jArray[__LINE__]=$report_data;
    $gAr['report_data']= $report_data;
    textFileWrite(json_encode($report_data),ROOT_DIR.'/print_file/'.$fileName);
    $jArray['html']     = $general->fileToVariable(ROOT_DIR.'/ajax/report.phtml');
    $jArray['status']=1;
?>
