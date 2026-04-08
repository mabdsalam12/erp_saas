<?php
    $q=array();

    $pdYear=intval($_POST['yeare']);
    if($pdYear>0){
        $q[]="pdYear = ".$pdYear;
    }
    $pdMonth=intval($_POST['month']);
    if($pdMonth>0){
        $q[]="pdMonth = ".$pdMonth;
    }

    if(!empty($q)){
        $sq='where '.implode(' and ',$q);   
    }
    else{
        $sq="";
    }

    $profitDistribute=$db->selectAll($general->table(28),$sq.' order by pdID asc');
    $general->arrayIndexChange($profitDistribute,'pdID');
    $profitSector=$db->selectAll($general->table(140));
    $general->arrayIndexChange($profitSector,'psID');
    $pdID=[];

    foreach($profitDistribute as $p){
        $pdID[]=$p['pdID'];
    }
    $serial=1;
    $rData=[];
    $tAmount=0;
    if(!empty($profitDistribute)){
        $q=[];
        $q[]='pdID in ('.implode(',',$pdID).')';
        $years=[];
        $months=[];
        for($i=date('Y');$i>2020;$i--){
            $years[$i]=['i'=>$i,'v'=>$i];
        }
        for($i=1;$i<=12;$i++){
            $months[$i]=['i'=>$i,'v'=>date("F", strtotime('00-'.$i.'-01'))];
        }
        $jArray['k']=$profitDistribute;

        if(!empty($profitDistribute)){
            foreach($profitDistribute as $pd){
                $tAmount+=$pd['pdProfitAmount'];
                $percent=($pd['pdProfitAmount']*100)/$pd['pdClosingBalance'];
                //$closing=$smt->getClosingBalanceForProfit($years[$pd['pdYear']]['v'],$months[$pd['pdMonth']]['v'],$jArray);
                $rData[]=[
                    's' => $serial++,
                    'y' => $years[$pd['pdYear']]['v'],
                    'm' => $months[$pd['pdMonth']]['v'],
                    't' => $general->numberFormat($pd['pdClosingBalance']),
                    'per' => $general->numberFormat($percent,3),
                    'a' => $general->numberFormat($pd['pdProfitAmount']),
                    'd'=>'<a href="'.URL.'?mdl=profitDistribute&details&pdID='.$pd['pdID'].'" target="_blank" class="btn btn-success">Details</a>'
                ];
            } 
        }
    }
    $rData[]=[
        's'=>'',
        'y'=>['t'=>'Total','b'=>1,'col'=>4],
        'm'=>false,
        't'=>false,
        'per'=>false,
        'a'=>['t'=> $general->numberFormat($tAmount) ,'b'=>1],
        'd'=>false,
    ];
    $fileName='profitDistributeList'.TIME.rand(0,999).'.txt';
    $report_data=array(
        'name'      => 'purRep',
        'title'     => 'Profit Distribute List',
        'fileName'  => $fileName,
        'head'=>array(
            array('title'=>"#"              ,'key'=>'s','hw'=>5),
            array('title'=>"Years"          ,'key'=>'y'),
            array('title'=>"Month"          ,'key'=>'m'),
            array('title'=>"Amount"         ,'key'=>'t' ,'al'=>'r'),
            array('title'=>"Percent"        ,'key'=>'per' ,'al'=>'r'),
            array('title'=>"Profit Amount"  ,'key'=>'a' ,'al'=>'r'),
            array('title'=>"Details"        ,'key'=>'d' ,'al'=>'r'),
        ),
        'data'=>$rData
    );
    //$jArray[__LINE__]=$report_data;
    $gAr['report_data']= $report_data;
    textFileWrite(json_encode($report_data),ROOT_DIR.'/print_file/'.$fileName);
    $jArray['html']     = $general->fileToVariable(ROOT_DIR.'/ajax/report.phtml');
    $jArray['status']=1;   



?>
