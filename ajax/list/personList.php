<?php
    $name=$_POST['name'];
    $mobile=$_POST['mobile'];
    $balanceType=intval($_POST['balanceType']);
    $q=[];
    $q[]='isActive in(1,0)';
    if(!empty($name)){
        $q[]="name like '%".$name."%'";
    }
    if(!empty($mobile)){
        $q[]="mobile like '%".$mobile."%'";
    }

    $sq='where '.implode(' and ',$q);
    $persons=$db->selectAll('person',$sq.' order by name asc');
    $serial=1;
    $rData=[];
    $total=0;
    if(!empty($persons)){
        foreach($persons as $p){
            $checkest='';
            if($p['isActive']==1){
                $checkest='checked="checked"'; 
            }
            $balance=$acc->headBalance($p['ledger_id']);
            if($balanceType==1 && $balance==0){continue;}
            $total+=$balance;
            $rData[]=[
                's' => $serial++,
                'n' => $p['name'],
                'm' => $p['mobile'],
                'b' => $general->numberFormat($balance),
                'st' => '<a href="'.URL.'?mdl=personsStatment&person_id='.$p['id'].'&dRange='.date('d-m-Y').' to '.date('d-m-Y',strtotime('+30 day')).'" class="btn btn-info">Statment</a>',
                'e' => '<a href="'.URL.'?mdl=persons&edit='.$p['id'].'" class="btn btn-info">Edit</a>',

            ];
        }

        $rData[]=[
            's' => '',
            'n' => ['t'=>'Total','b'=>1],
            'm' => '',
            'b' => ['t'=>$general->numberFormat($total),'b'=>1],
            'st' => '',
            'stu' =>'',
            'e' => '',
        ]; 
    }
    $fileName='personList'.TIME.rand(0,999).'.txt';
    $report_data=array(
        'name'      => 'personList',
        'title'     => 'Person List',
        'fileName'  => $fileName,
        'head'=>array(
            array('title'=>"#"            ,'key'=>'s','hw'=>5),
            array('title'=>"Name"         ,'key'=>'n'),
            array('title'=>"Mobile"       ,'key'=>'m'),
            array('title'=>"Balance"      ,'key'=>'b','al'=>'r'),
            array('title'=>"Statement"     ,'key'=>'st'),
            array('title'=>"Edit"         ,'key'=>'e'),

        ),
        'data'=>$rData
    );
    //$jArray[__LINE__]=$report_data;
    $gAr['report_data']= $report_data;
    textFileWrite(json_encode($report_data),ROOT_DIR.'/print_file/'.$fileName);
    $jArray['html']     = $general->fileToVariable(ROOT_DIR.'/ajax/report.phtml');
    $jArray['status']=1;

