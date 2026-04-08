<?php
    $sID = intval($_POST['sID']);
    $sale = $db->getRowData('sale','where sID='.$sID);
    if(empty($sale)){$error=fl(); setMessage(63,'sale');}
    else{
        $subTotal = $sale['total'];
        $saleProducts = $db->selectAll('sale_products','where sID='.$sID);
        $saleReturns  = $db->selectAll('sale_return_details','where sale_id='.$sID,'productID as pID,quantity,quantity');

        $saleReturnData=[];
        if(!empty($saleProducts)){
            $saleRetun = [];
            if(!empty($saleReturns)){

                foreach($saleReturns as $sr){
                    if(!isset($saleRetun[$sr['pID']])){
                        $saleRetun[$sr['pID']]=[
                            'qty'=>0,
                        ];
                    }
                    $subTotal-=$sr['quantity']*$sr['unitPrice'];
                    $saleRetun[$sr['pID']]['qty']+=$sr['quantity'];
                }
            }


            $pIDs=[];
            foreach($saleProducts as $sp){
                $pIDs[$sp['pID']] = $sp['pID'];
            }
            $products = $db->selectAll('products','where pID in('.implode(',',$pIDs).')','pID,pTitle,categoryID');
            $general->arrayIndexChange($products,'pID');
            $categorys = $db->selectAll('product_category','','id,parent,title');
            $general->arrayIndexChange($categorys,'id');
            foreach($saleProducts as $sp){
                $p = $products[$sp['pID']];
                $c = $categorys[$p['categoryID']];
                $saleReturnData[$sp['pID']]=[
                    'pID'           => $p['pID'],
                    'product'       => $p['title'],
                    'category'      => $categorys[$c['parent']]['title'],
                    'subCategory'   => $c['title'],
                    'qty'           => $sp['saleQty'],
                    'sale_up'       => $sp['unitPrice'],
                    'returnQty'     => intval(@$saleRetun[$sp['pID']]['qty']), 
                    'avQty'         => $sp['saleQty']- intval(@$saleRetun[$sp['pID']]['qty'])
                ];
            }
        }
        $gAr['saleReturnData']=$saleReturnData;
        $jArray['html'] =$general->fileToVariable(__DIR__.'/saleReturnInit.phtml');
        $jArray['status']= 1;

    }
?>
