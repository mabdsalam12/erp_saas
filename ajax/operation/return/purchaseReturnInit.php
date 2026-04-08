<?php
    $purID = intval($_POST['purID']);
    $purchase = $db->getRowData('purchase','where purID='.$purID);
    if(empty($purchase)){$error=fl(); setMessage(63,'sale');}
    else{
        $sup = $db->get_rowData('suppliers','supID',$purchase['supID']);
        $subTotal = $purchase['netTotal'];
        $purchaseProducts = $db->selectAll('purchase_details','where purID='.$purID);
        $purchaseReturns  = $db->selectAll('purchase_return_details','where purchasse_id='.$purID,'id,productID as pID,quantity,unitPrice');
        $purchaseReturnData=[];
        if(!empty($purchaseProducts)){
            $purchaseRetun = [];
            if(!empty($purchaseReturns)){

                foreach($purchaseReturns as $sr){
                    if(!isset($purchaseRetun[$sr['pID']])){
                        $purchaseRetun[$sr['pID']]=[
                            'qty'=>0,
                        ];
                    }  
                    $subTotal-=$sr['quantity']*$sr['unitPrice'];
                    $purchaseRetun[$sr['pID']]['qty']+=$sr['quantity'];
                }
            }


            $pIDs=[];
            foreach($purchaseProducts as $sp){
                $pIDs[$sp['pID']] = $sp['pID'];
            }
            $products = $db->selectAll('products','where pID in('.implode(',',$pIDs).')','pID,pTitle,categoryID,pStock');
            $general->arrayIndexChange($products,'pID');
            $categorys = $db->selectAll('product_category','','id,parent,title');
            $general->arrayIndexChange($categorys,'id');
            foreach($purchaseProducts as $sp){
                $p = $products[$sp['pID']];
                $c = $categorys[$p['categoryID']];
                $purchaseReturnData[$sp['pID']]=[
                    'pID'           => $p['pID'],
                    'product'       => $p['title'],
                    'category'      => $categorys[$c['parent']]['title'],
                    'subCategory'   => $c['title'],
                    'qty'           => $sp['quantity'],
                    'sale_up'       => $sp['unitPrice'],
                    'returnQty'     => intval(@$purchaseRetun[$sp['pID']]['qty']), 
                    'avQty'         => $sp['quantity']- intval(@$purchaseRetun[$sp['pID']]['qty'])
                ];
            }
        }
        $jArray['supplyer']=$sup['supName'];
        $gAr['purchaseReturnData']=$purchaseReturnData;
        $jArray['html'] =$general->fileToVariable(__DIR__.'/purchaseReturnInit.phtml');
        $jArray['status']= 1;

    }
?>
