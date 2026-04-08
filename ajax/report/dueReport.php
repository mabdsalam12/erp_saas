<?php
    $dRange=$_POST['dRange'];
    $reportInfo=['Date :'.$dRange];
    $general->getFromToFromStringt($dRange,$from,$to);
    $supID=intval($_POST['supID']);
    $scID=intval($_POST['scID']);
    $eID=intval($_POST['eID']);
    $q[]='scID='.$scID;
    $q[]='sDate between '.$from.' and '.$to;
    if($supID>0){
        $q[]='supID='.$supID;
    }
    $jArray[__LINE__]=$eID;
    if($eID>0){
        $q[]='(sDsr='.$eID.' or sSr='.$eID.')';
    }
    $tVan       = 0;
    $tTax       = 0;
    $tCost      = 0;
    $tSalary    = 0;
    $tDue       = 0;
    $tDueCollect= 0;
    $costTotal  = 0;
    $vanData    = [];
    $taxData    = [];
    $costData   = [];
    $salaryData = [];
    $dueData    = [];
    $dueColData = [];

    $sales=$db->selectAll($general->table(14),'where '.implode(' and ',$q),'sID,sDate');
    $general->getIDFromVariable($sales,'sID',$sIDs);
    $general->arrayIndexChange($sales,'sID');
    if(!empty($sales)){
        $vouchers=$db->selectAll($general->table(96),'where vType='.V_T_EMPLOYEE_SALE_SALARY_PAY.' and vTypeRef in('.implode(',',$sIDs).')');
        if(!empty($vouchers)){
            $general->getIDFromVariable($vouchers,'veID',$veIDs);
            $entrys=$db->selectAll($general->table(97),'where veID in('.implode(',',$veIDs).')');
            $jArray[__LINE__]=$entrys;
            foreach($entrys as $en){
                $date=$general->make_date($en['time']);
                if(!isset($salaryData[$date])){$salaryData[$date]=0;}
                $salaryData[$date]+=$en['debit'];
            }
        }
        $q=['socDate between '.$from.' and '.$to];
        if($eID>0){
            $q[]='saleDSR='.$eID;
        }
        $otherCosts=$db->selectAll($general->table(19),'where '.implode(' and ',$q),'','array',$jArray);
        $jArray[__LINE__]=$otherCosts;
        if(!empty($otherCosts)){
            foreach($otherCosts as $oc){
                $date=$general->make_date($oc['socDate']);
                if($oc['socType']==OTHER_COST_VAN_CHARGE){
                    if(!isset($vanData[$date])){$vanData[$date]=0;}
                    $vanData[$date]+=$oc['scAmount'];
                }
                elseif($oc['socType']==OTHER_COST_TAX){
                    if(!isset($taxData[$date])){$taxData[$date]=0;}
                    $taxData[$date]+=$oc['scAmount'];
                }
                elseif($oc['socType']==OTHER_COST_COMMISSION){
                    if(!isset($costData[$date])){$costData[$date]=0;}
                    $costData[$date]+=$oc['scAmount'];
                }
            }
        }
        $q=['sID in('.implode(',',$sIDs).')'];
        if($eID>0){
            $q[]='sdRef='.$eID;
        }
        $dues=$db->selectAll($general->table(20),'where '.implode(' and ',$q));
        if(!empty($dues)){
            foreach($dues as $d){
                $s=$sales[$d['sID']];
                $date=$general->make_date($s['sDate']);
                if(!isset($dueData[$date])){$dueData[$date]=0;}
                $dueData[$date]+=$d['sdAmount'];
                if(!isset($dueColData[$date])){$dueColData[$date]=0;}
                $dueColData[$date]+=$d['sdCollect'];
            }
        }
    }
    $rData=[];
    $start=$from;
    $dueBalance=0;
    while($start<$to){
        $d=$general->make_date($start);
        $van=0;     if(isset($vanData[$d])){$van=$vanData[$d];}         $tVan+=$van;
        $tax=0;     if(isset($taxData[$d])){$tax=$taxData[$d];}         $tTax+=$tax;
        $cost=0;    if(isset($costData[$d])){$cost=$costData[$d];}      $tCost+=$cost;
        $salary=0;  if(isset($salaryData[$d])){$salary=$salaryData[$d];}$tSalary+=$salary;
        $due=0;     if(isset($dueData[$d])){$due=$dueData[$d];}         $tDue+=$due;
        $dueCol=0;  if(isset($dueColData[$d])){$dueCol=$dueColData[$d];}$tDueCollect+=$dueCol;
        $dueBalance+=$due;
        $dueBalance-=$dueCol;
        $t=$van+$tax+$cost+$salary;
        $costTotal+=$t;
        $rData[]=[
            'd'=>$d,
            'v'=>$general->numberFormat($van),
            'tx'=>$general->numberFormat($tax),
            'c'=>$general->numberFormat($cost),
            's'=>$general->numberFormat($salary),
            't'=>$general->numberFormat($t),
            'du'=>$general->numberFormat($due),
            'dc'=>$general->numberFormat($dueCol),
            'db'=>$general->numberFormat($dueBalance),
        ];


        $start=strtotime('+1 day',$start);
    }
    $rData[]=[
        'd'=>['t'=>'Total','b'=>1],
        'v'=>['t'=>$general->numberFormat($tVan),'b'=>1],
        'tx'=>['t'=>$general->numberFormat($tTax),'b'=>1],
        'c'=>['t'=>$general->numberFormat($tCost),'b'=>1],
        's'=>['t'=>$general->numberFormat($tSalary),'b'=>1],
        't'=>['t'=>$general->numberFormat($costTotal),'b'=>1],
        'du'=>['t'=>$general->numberFormat($tDue),'b'=>1],
        'dc'=>['t'=>$general->numberFormat($tDueCollect),'b'=>1],
        'db'=>['t'=>$general->numberFormat($dueBalance),'b'=>1],
    ];
    $fileName='dueRep_'.TIME.rand(0,999).'.txt';
    $report_data=array(
        'name'      => 'dueRep'.date('d_m_Y',$from).'_'.date('d_m_Y',$to),
        'title'     => 'Sale Report',
        'info'      => $reportInfo,
        'fileName'  => $fileName,
        'head'=>array(
            array('title'=>"Date"           ,'key'=>'d'     ,'hw'=>15),
            array('title'=>"van"            ,'key'=>'v'     ,'al'=>'r'),
            array('title'=>"Tax"            ,'key'=>'tx'    ,'al'=>'r'),
            array('title'=>"Cost"           ,'key'=>'c'     ,'al'=>'r'),
            array('title'=>"Salary"         ,'key'=>'s'     ,'al'=>'r'),
            array('title'=>"Total"          ,'key'=>'t'     ,'al'=>'r'),
            array('title'=>"Due"            ,'key'=>'du'     ,'al'=>'r'),
            array('title'=>"Due Collection" ,'key'=>'dc'    ,'al'=>'r'),
            array('title'=>"Due Balance"    ,'key'=>'db'    ,'al'=>'r'),
        ),
        'data'=>$rData
    );
    $gAr['report_data']= $report_data;
    textFileWrite(json_encode($report_data),ROOT_DIR.'/print_file/'.$fileName);
    $jArray['html']     = $general->fileToVariable(ROOT_DIR.'/ajax/report.phtml');
    $jArray['status']=1;

?>
