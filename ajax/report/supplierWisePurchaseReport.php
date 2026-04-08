<?php
    $fileName='supplierWisePurchaseReport'.TIME.rand(0,999).'.txt';
    $dRange = $_POST['dRange'];
    $reportInfo=['Date: '.$dRange];
    $general->getFromToFromStringt($dRange,$from,$to);
    $rType = intval($_POST['rType']);
    $supID = intval($_POST['supID']);
    $category = intval($_POST['category']);
    $subCategory = intval($_POST['subCategory']);
    $pID = intval($_POST['pID']);
    $uID = intval($_POST['uID']);
    $q=['purDate between '.$from.' and '.$to];
    if($uID>0){
        $q[]='createdBy='.$uID;
    }
    if($supID>0){
        $q[]='supID='.$supID;
    }
    $purchase = $db->selectAll('purchase','where '.implode(' and ',$q),'purID,supID,purDate,subTotal,netTotal,createdBy');
    if($pID==0){
        $sPIDs=[];
        if($category>0){
            $cats = $db->selectAll('product_category','where parent='.$category,'id');
            $ids=[];
            foreach($cats as $id){
                $ids[$id['id']]= $id['id'];
            }
            $product = $db->selectAll('products','where categoryID in('.implode(',',$ids).')','pID');
        }
        elseif($subCategory>0){
            $product = $db->selectAll('products','where categoryID='.$subCategory ,'pID');
        }
        if($subCategory!=0 || $category!=0){
            $sPIDs=[0=>0];
            if(!empty($product)){
                foreach($product as $id){
                    $sPIDs[$id['pID']]= $id['pID'];
                }
            }
        }
    }
    $rData=[];
    $sr=1;
    $total=0;
    $tQty=0;

    $purIDs=[];
    $supIDs=[];
    $purchases=[];
    $uIDs=[];
    if(!empty($purchase)){
        foreach($purchase as $p){
            $purchases[$p['purID']]=$p;
            $purIDs[$p['purID']] =$p['purID'];
            $supIDs[$p['purID']] =$p['supID'];
            $uIDs[$p['createdBy']] = $p['createdBy'];
        }
        $suppliers=$db->selectAll('suppliers','where supID in('.implode(',',$supIDs).')','supID,supName');
        $general->arrayIndexChange($suppliers,'supID');
        $q=['purID in('.implode(',',$purIDs).')'];
        if($pID>0){
            $q[]='pID='.$pID;
        }
        elseif(!empty($sPIDs)){
            $q[]='pID in('.implode(',',$sPIDs).')';
        }
        $purchaseDetails=$db->selectAll('purchase_details','where '.implode(' and ',$q));
    }
    if($rType==0){
        $reportInfo[]='Report Type: Summary';
        $supplierData=[];
        $quantitys=[];
        if(!empty($purchase) && !empty($purchaseDetails)){  
            foreach($purchaseDetails as $pd){
                if(!isset($quantitys[$pd['purID']])){
                    $quantitys[$pd['purID']]=0;
                }
                $quantitys[$pd['purID']]+=$pd['quantity'];
            }
            foreach($purchase as $p){
                if(!isset($supplierData[$p['supID']])){
                    $supplierData[$p['supID']]=[
                        'amount'=>0,
                        'qty'=>0
                    ];
                }
                $supplierData[$p['supID']]['amount']+=$p['netTotal'];
                $supplierData[$p['supID']]['qty']+=$quantitys[$pd['purID']];
            }
            foreach($supplierData as $supID=>$sd){
                $tQty+=$sd['qty'];
                $total+=$sd['amount'];
                $rData[]=[
                    's'=>$sr++,
                    'su'=>$suppliers[$supID]['supName'],
                    'q'=>$general->numberFormat($sd['qty'],0),
                    'a'=>$general->numberFormat($sd['amount'],0)
                ];
            }
        }
        $rData[]=[
            's'=>['t'=>''],
            'su'=>['t'=>'Total','b'=>1],
            'q'=>['t'=>$general->numberFormat($tQty,0),'b'=>1],
            'a'=>['t'=>$general->numberFormat($total,0),'b'=>1],
        ];

        $head=array(
            array('title'=>"#"         ,'key'=>'s','hw'=>5),
            array('title'=>"Supplier"   ,'key'=>'su'),
            array('title'=>"Quantity"   ,'key'=>'q'),
            array('title'=>"Amount"     ,'key'=>'a','al'=>'r')
        );


        // print_r($rData);

    }
    elseif($rType==1){
        $reportInfo[]='Report Type: Product wise';
        $prodcutData=[];
        $pIDs=[];
        if(!empty($purchase) && !empty($purchaseDetails)){  
            foreach($purchaseDetails as $pd){
                $pIDs[$pd['pID']] = $pd['pID'];
                $supID = $purchases[$pd['purID']]['supID'];
                if(!isset($prodcutData[$supID][$pd['pID']])){
                    $prodcutData[$supID][$pd['pID']]=[
                        'amount'=>0,
                        'qty'=>0,
                    ];
                }
                $prodcutData[$supID][$pd['pID']]['amount']+=$pd['quantity']*$pd['unitPrice'];
                $prodcutData[$supID][$pd['pID']]['qty']+=$pd['quantity'];
            }
            $products = $db->selectAll('products','where pID in('.implode(',',$pIDs).')','pID,pTitle,categoryID');
            $general->arrayIndexChange($products,'pID');
            $categorys = $db->selectAll('product_category');
            if(!empty($categorys)){
                $general->arrayIndexChange($categorys,'id');
            }
            foreach($prodcutData as $supID=>$data){
                foreach($data as $pID=>$d){
                    $tQty+=$d['qty'];
                    $total+=$d['amount'];
                    $p=$products[$pID];
                    $c=$categorys[$p['categoryID']];
                    $rData[]=[
                        's'=>$sr++,
                        'su'=>$suppliers[$supID]['supName'],
                        'c'=>$categorys[$c['parent']]['title'],
                        'sc'=>$c['title'],
                        'p'=>$p['title'],
                        'q'=>$general->numberFormat($d['qty'],0),
                        'a'=>$general->numberFormat($d['amount'],0)
                    ];
                }
            }
        }
        $rData[]=[
            's'=>['t'=>''],
            'su'=>['t'=>'Total','b'=>1],
            'c'=>['t'=>'','b'=>1],
            'sc'=>['t'=>'','b'=>1],
            'p'=>['t'=>'','b'=>1],
            'q'=>['t'=>$general->numberFormat($tQty,0),'b'=>1],
            'a'=>['t'=>$general->numberFormat($total,0),'b'=>1],
        ];
        $head=array(
            array('title'=>"#"         ,'key'=>'s','hw'=>5),
            array('title'=>"Supplier"   ,'key'=>'su'),
            array('title'=>"Category"   ,'key'=>'c'),
            array('title'=>"Sub Category"   ,'key'=>'sc'),
            array('title'=>"Product"    ,'key'=>'p'),
            array('title'=>"Quantity"   ,'key'=>'q'),
            array('title'=>"Amount"     ,'key'=>'a','al'=>'r')
        );

    }
    else{
        $reportInfo[]='Report Type: Details';
        $prodcutData=[];
        $pIDs=[];
        if(!empty($purchase) && !empty($purchaseDetails)){  
            foreach($purchaseDetails as $pd){
                $date=$general->make_date($purchases[$pd['purID']]['purDate']);
                $pIDs[$pd['pID']] = $pd['pID'];
                $uID= $purchases[$pd['purID']]['createdBy'];
                $supID = $purchases[$pd['purID']]['supID'];
                if(!isset($prodcutData[$date][$supID][$pd['pID']])){
                    $prodcutData[$date][$supID][$pd['pID']]=[
                        'amount'=>0,
                        'qty'=>0,
                        'uID'=>$uID,
                    ];
                }
                $prodcutData[$date][$supID][$pd['pID']]['amount']+=$pd['quantity']*$pd['unitPrice'];
                $prodcutData[$date][$supID][$pd['pID']]['qty']+=$pd['quantity'];
            }
            $products = $db->selectAll('products','where pID in('.implode(',',$pIDs).')','pID,pTitle,categoryID');
            $general->arrayIndexChange($products,'pID');
            $users = $db->selectAll('users','where uID in('.implode(',',$uIDs).')','uID,username');
            $general->arrayIndexChange($users,'uID');
            $categorys = $db->selectAll('product_category');
            if(!empty($categorys)){
                $general->arrayIndexChange($categorys,'id');
            }
            foreach($prodcutData as $date=>$datas){
                foreach($datas as $supID=>$data){
                    foreach($data as $pID=>$d){
                        $tQty+=$d['qty'];
                        $total+=$d['amount'];
                        $p=$products[$pID];
                        $c=$categorys[$p['categoryID']];
                        $rData[]=[
                            's'=>$sr++,
                            'su'=>$suppliers[$supID]['supName'],
                            'u'=>$users[$d['uID']]['username'],
                            'd'=>$date,
                            'c'=>$categorys[$c['parent']]['title'],
                            'sc'=>$c['title'],
                            'p'=>$p['title'],
                            'q'=>$general->numberFormat($d['qty'],0),
                            'a'=>$general->numberFormat($d['amount'],0)
                        ];
                    }
                }

            }
        }
        $rData[]=[
            's'=>['t'=>''],
            'su'=>['t'=>'Total','b'=>1],
            'u'=>['t'=>'','b'=>1],
            'd'=>['t'=>'','b'=>1],
            'c'=>['t'=>'','b'=>1],
            'sc'=>['t'=>'','b'=>1],
            'p'=>['t'=>'','b'=>1],
            'q'=>['t'=>$general->numberFormat($tQty,0),'b'=>1],
            'a'=>['t'=>$general->numberFormat($total,0),'b'=>1],
        ];
        $head=array(
            array('title'=>"#"         ,'key'=>'s','hw'=>5),
            array('title'=>"Supplier"   ,'key'=>'su'),
            array('title'=>"User"       ,'key'=>'u'),
            array('title'=>"Date"       ,'key'=>'d'),
            array('title'=>"Category"   ,'key'=>'c'),
            array('title'=>"Sub Category"   ,'key'=>'sc'),
            array('title'=>"Product"    ,'key'=>'p'),
            array('title'=>"Quantity"   ,'key'=>'q'),
            array('title'=>"Amount"     ,'key'=>'a','al'=>'r')
        );


    }

    $report_data=array(
        'name'      => 'supplierWisePurchaseReport',
        'title'     => 'Aupplier wise Purchase Report',
        'info'      => $reportInfo,
        'fileName'  => $fileName,
        'head'=>$head,
        'data'=>$rData
    );
    $jArray[__LINE__]=$report_data;
    $gAr['report_data']= $report_data;
    textFileWrite(json_encode($report_data),ROOT_DIR.'/print_file/'.$fileName);
    $jArray['html']     = $general->fileToVariable(ROOT_DIR.'/ajax/report.phtml');
    $jArray['status']=1;
