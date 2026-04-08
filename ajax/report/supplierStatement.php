<?php
    $dRange=$_POST['dRange'];
    $reportInfo=['Date :'.$dRange];
    $general->getFromToFromString($dRange,$from,$to);
    $scID   = intval($_POST['scID']);
    $supID  = intval($_POST['supID']);
    $sup=$smt->supplierInfoByID($supID);
    if(empty($sup)){ $jArray[fl()]=1;$error=fl();setMessage(63,'Supplier');}
    else{
        $jArray[fl()]=1;
        $hID=$acc->getSupplierHead($sup);
        if($hID==false){$error=fl();setMessage(66);}
    }
    if(!isset($error)){
        $rData=[];
        $closingBalance=$acc->headBalance($hID,$from,jArray:$jArray);
        $od=0;
        $oc=0;
        $balance=0;
        if($closingBalance<0){
            $oc=-$closingBalance;
        }
        else{
            $od=$closingBalance;
        }
        $balance+=$od;
        $balance-=$oc;
        $serial=1;
        $rData[]=[
            's'=>$serial++,
            'h'=>'',
            'd'=>'',
            'p'=>['t'=>'Opening','b'=>1],
            'u'=>'',
            'in'=>'',
            'out'=>'',
            'b'=>$general->numberFormat($balance),
        ];


        $statement=$acc->headStatement($scID,$hID,$from,$to,$jArray);
        $tIn=0;
        $tOut=0;
        if(!empty($statement)){
            $users=$db->allUsers();
            foreach($statement as $s){
                $balance+=$s['in'];
                $balance-=$s['out'];
                $tIn+=$s['in'];
                $tOut+=$s['out'];
                $rData[]=[
                    's'=>$serial++,
                    'h'=>$s['head_title'],
                    'd'=>$general->make_date($s['time'],'time'),
                    'p'=>$s['note'],
                    'u'=>@$users[$s['createdBy']]['username'],
                    'in'=>$general->numberFormat($s['in']),
                    'out'=>$general->numberFormat($s['out']),
                    'b'=>$general->numberFormat($balance),
                ];
            }
        }

        $rData[]=[
            's'=>'',
            'h'=>['t'=>'Total','b'=>1,'col'=>4],
            'd'=>['t'=>false],
            'p'=>['t'=>false],
            'u'=>['t'=>false],
            'in'=>['t'=>$general->numberFormat($tIn),'b'=>1],
            'out'=>['t'=>$general->numberFormat($tOut),'b'=>1],
            'b'=>['t'=>$general->numberFormat($balance),'b'=>1],
        ];

        $fileName='purRep_'.TIME.rand(0,999).'.txt';
        $report_data=array(
            'name'      => 'HeadStatement'.date('d_m_Y',$from).'_'.date('d_m_Y',$to),
            'title'     => 'Head Statement',
            //'info'      => $reportInfo,
            'fileName'  => $fileName,
            'head'=>array(
                array('title'=>"#"          ,'key'=>'s'),
                array('title'=>"Head"       ,'key'=>'h'     ,'hw'=>15),
                array('title'=>"Date Time"  ,'key'=>'d'),
                array('title'=>"Particular" ,'key'=>'p'),
                array('title'=>"User"       ,'key'=>'u'),
                array('title'=>"In"         ,'key'=>'in'    ,'al'=>'r'),
                array('title'=>"Out"        ,'key'=>'out'     ,'al'=>'r'),
                array('title'=>"Balance"    ,'key'=>'b'    ,'al'=>'r'),
            ),
            'data'=>$rData
        );
        $jArray[__LINE__]=$report_data;
        $gAr['report_data']= $report_data;
        textFileWrite(json_encode($report_data),ROOT_DIR.'/print_file/'.$fileName);
        $jArray['html']     = $general->fileToVariable(ROOT_DIR.'/ajax/report.phtml');
        $jArray['status']=1;   
    }
    $jArray['m']=show_msg('y');
    $general->jsonHeader($jArray);
?>