<?php
    $reportInfo=['Date Time '.date('d/m/Y h:i:s A')];
    $scID=SECTION_DEALER;
    $q=['isActive=1','scID='.$scID];
    $suppliers=$db->selectAll($general->table(45),'where '.implode(' and ',$q));
    $rData=[];
    $serial=1;
    $tStockPrice=0;
    $tStockPriceD=0;
    $tBalance=0;
    $tTotal=0;
    
    $dRange=$_POST['dRange'];
     $general->getFromToFromStringt($dRange,$from,$to);
    
    $cashHead=$acc->getSystemHead(AH_CASH);
    $cash=$acc->headBalance($cashHead,TOMORROW_TIME,$scID);
    $date=strtotime("-1 day",$from);
    //echo date('m-d-y',$date);
    $BD=$acc->headBalance(14,$date);
   // print_r($BD);
    if(!empty($suppliers)){
        foreach($suppliers as $sup){
            $stockPrice=0;
            $stockPriceD=0;
            $products=$db->selectAll($general->table(104),'where supID='.$sup['supID'],'pID,pBuyPrice,pStockDamage');
            if(!empty($products)){
                $general->arrayIndexChange($products,'pID');
                $closingStock=$db->selectAll($general->table(2),'where pID in('.implode(',',array_keys($products)).') and psType='.PRODUCT_TYPE_GOOD.' group by pID','pID,sum(changeStock) t');
                foreach($closingStock as $os){
                    $stockPrice+=$products[$os['pID']]['pBuyPrice']*$os['t'];
                }

                foreach($products as $p){
                    if($p['pStockDamage']>0){
                        $returProducts=$db->selectAll($general->table(16),'where inStock>0 and pID='.$p['pID'].' and srType='.PRODUCT_TYPE_DAMAGE.' group by srID','srID,(unitPrice*inStock) as amount');
                        if(!empty($returProducts)){
                            foreach($returProducts as $rp){
                                $stockPriceD+=$rp['amount'];
                            }
                        }
                        $openingProducts=$db->selectAll($general->table(3),'where inStock>0 and pID='.$p['pID'].' and posType='.PRODUCT_TYPE_DAMAGE.' group by posID','posID,unitPrice,inStock,(unitPrice*inStock) as amount');
                        /*if($p['pID']==85){
                        $general->printArray($openingProducts);
                        }*/
                        if(!empty($openingProducts)){
                            foreach($openingProducts as $rp){
                                $stockPriceD+=$rp['amount'];
                            }
                        }

                    }
                }
            }

            $balance=$acc->headBalance($sup['hID']);;
           
            $total=$balance+$stockPrice+$stockPriceD;
            $tBalance   += $balance;
            $tStockPrice+= $stockPrice;
            $tStockPriceD+= $stockPriceD;
            $tTotal     += $total;
            $data=[
                's' => $serial++,
                'n' => $sup['supName'],
                'b' => $general->numberFormat($balance),
                'g' => $general->numberFormat($stockPrice),
                'd' => $general->numberFormat($stockPriceD),
                't' => $general->numberFormat($total),
            ];
            $rData[]=$data;
        }
    }
    $rData[]=[
        's' =>'',
        'n' =>['t'=>'Total','b'=>1],
        'b'=>['t'=>$general->numberFormat($tBalance),'b'=>1],
        'g'=>['t'=>$general->numberFormat($tStockPrice),'b'=>1],
        'd'=>['t'=>$general->numberFormat($tStockPriceD),'b'=>1],
        't' =>['t'=>$general->numberFormat($tTotal),'b'=>1],
    ];

    $fileName='supSummery_'.TIME.rand(0,999).'.txt';
    $report_data=array(
        'name'      => 'Closing_Report_Sup_'.date('d/m/Y'),
        'title'     => 'Closing Report Supplier',
        'info'      => $reportInfo,
        'fileName'  => $fileName,
        'head'=>array(
            array('title'=>"#"              ,'key'=>'s'     ,'hw'=>5),
            array('title'=>"Supplier Name"  ,'key'=>'n'),
            array('title'=>"Balance"        ,'key'=>'b'    ,'al'=>'r'),
            array('title'=>"Good Stock"     ,'key'=>'g'    ,'al'=>'r'),
            array('title'=>"Damage Stock"   ,'key'=>'d'    ,'al'=>'r'),
            array('title'=>"Total"          ,'key'=>'t'    ,'al'=>'r'),
        ),
        'data'=>$rData
    );
    $jArray[__LINE__]=$report_data;
    $gAr['report_data']= $report_data;
    textFileWrite(json_encode($report_data),ROOT_DIR.'/print_file/'.$fileName);
    $jArray['html']     = $general->fileToVariable(ROOT_DIR.'/ajax/report.phtml');
    $jArray['status']=1;   
    $general->jsonHeader($jArray);
?>