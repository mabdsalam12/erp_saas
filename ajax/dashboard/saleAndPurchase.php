<?php
if(0){
    $dRange = $_POST['dRange'];
    $general->getFromToFromString($dRange,$from,$to);
    $sales = $db->selectAll('sale','where sDate between '.$from.' and '.$to,'sID,total,sDate');
    $purchases = $db->selectAll('purchase','where purDate between '.$from.' and '.$to,'purID,netTotal,purDate');
    $rData=[];
    if(!empty($sales)){
        foreach($sales as $s){
            $date = date('Y-m-d',$s['sDate']) ;
            if(!isset($rData[$date])){
                $rData[$date]=[
                    'date'=>$date,
                    's'=>0,
                    'p'=>0,
                ];
            }
            $rData[$date]['s'] += $s['total'];
        }
    }
    if(!empty($purchases)){
        foreach($purchases as $s){
            $date = date('Y-m-d',$s['purDate']) ;
            if(!isset($rData[$date])){
                $rData[$date]=[
                    'date'=>$date,
                    's'=>0,
                    'p'=>0,
                ];
            }
            $rData[$date]['p'] += $s['netTotal'];
        }
    }

    $startA=$from;
    $endA=$to;
    while($startA<$endA){
        $date = date('Y-m-d',$startA) ;
        if(!isset($rData[$date])){
            $rData[$date]=[
                'date'=>$date,
                's'=>0,
                'p'=>0,
            ];
        }
        $startA=strtotime('+1 day',$startA);
    }
    ksort($rData);
    $saleAndPurchase=[];
    foreach($rData as $d){
        $saleAndPurchase[]=$d; 
    }
    $jArray['status'] = 1;
    $jArray['saleAndPurchase'] = $saleAndPurchase;
}