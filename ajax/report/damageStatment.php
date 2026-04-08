<?php
    $dRange=$_POST['dRange'];
    $reportInfo=['Date :'.$dRange];
    $general->getFromToFromStringt($dRange,$from,$to);
    $supID=intval($_POST['supID']);
    $q[]='purType='.PRODUCT_TYPE_DAMAGE;
    $q[]='purDate between '.$from.' and '.$to;
    $q[]='supID='.$supID;
    $rData=[];
    $serial=1;
    $statmentData=[];

    $purchases=$db->selectAll($general->table(11),'where '.implode(' and ',$q));
    if(!empty($purchases)){
        foreach($purchases as $pur){
            $statmentData[]=[
                'i' => $pur['supInvNo'],
                'd' => $pur['purDate'],
                'st'=> $pur['subTotal'],
                'di'=> $pur['discount'],
                'n' => $pur['netTotal'],
                't' => 'p'
            ];
        }
    }
    $q=['supID='.$supID,'purrDate between '.$from.' and '.$to];
    $return=$db->selectAll($general->table(25),'where '.implode(' and ',$q));
    if(!empty($return)){
        foreach($return as $pur){
            $statmentData[]=[
                'i' => $pur['supInvNo'],
                'd' => $pur['purrDate'],
                'st'=> $pur['subTotal'],
                'di'=> $pur['discount'],
                'n' => $pur['netTotal'],
                't' => 'r'
            ];
        }
    }

    $tPayable=0;
    $tRece=0;
    $balance=0;
    if(!empty($statmentData)){
        $general->arraySortByColumn($statmentData,'d');
        $serial=1;
        
        foreach($statmentData as $s){

            $payable=0;
            $rece=0;

            if($s['t']=='r'){
                $balance+=$s['n'];
                $t='Return';
                $rece=$s['n'];
            }
            else{
                $t='Purchase';
                $balance-=$s['n'];
                $payable=$s['n'];
            }
            $tPayable+=$payable;
            $tRece+=$rece;
            $rData[]=[
                's'=>$serial++,
                't'=>$t,
                'd'=>$general->make_date($s['d']),
                'i'=>$s['i'],
                'st'=>$general->numberFormat($s['st']),
                'di'=>$general->numberFormat($s['di']),
                'p'=>$general->numberFormat($payable),
                'r'=>$general->numberFormat($rece),
                'b'=>$general->numberFormat($balance),
            ];
        }

    }

    $rData[]=[
        's'=>'',
        't'=>['t'=>'Total','b'=>1,'col'=>5],
        'd'=>['t'=>false],
        'i'=>['t'=>false],
        'st'=>['t'=>false],
        'di'=>['t'=>false],
        'p'=>['t'=>$general->numberFormat($tPayable),'b'=>1],
        'r'=>['t'=>$general->numberFormat($tRece),'b'=>1],
        'b'=>['t'=>$general->numberFormat($balance),'b'=>1],
    ];


    $fileName='purRep_'.TIME.rand(0,999).'.txt';
    $report_data=array(
        'name'      => 'purRep'.date('d_m_Y',$from).'_'.date('d_m_Y',$to),
        'title'     => 'Purchase Report',
        'info'      => $reportInfo,
        'fileName'  => $fileName,
        'head'=>array(
            array('title'=>"#"          ,'key'=>'s' ,'hw'=>5),
            array('title'=>"Type"       ,'key'=>'t'),
            array('title'=>"Date"       ,'key'=>'d'),
            array('title'=>"Sup Inv No" ,'key'=>'i','w'=>10),
            array('title'=>"Subtotal"   ,'key'=>'st','al'=>'r'),
            array('title'=>"Discount"   ,'key'=>'di','al'=>'r'),
            array('title'=>"Payable"    ,'key'=>'p','al'=>'r'),
            array('title'=>"Receivable" ,'key'=>'r','al'=>'r'),
            array('title'=>"Balance"    ,'key'=>'b','al'=>'r'),
            array('title'=>"Edit"       ,'key'=>'e'),
        ),
        'data'=>$rData
    );
    $jArray[__LINE__]=$report_data;
    $gAr['report_data']= $report_data;
    textFileWrite(json_encode($report_data),ROOT_DIR.'/print_file/'.$fileName);
    $jArray['html']     = $general->fileToVariable(ROOT_DIR.'/ajax/report.phtml');
    $jArray['status']=1;   
?>
