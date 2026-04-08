<?php
    $purID = intval($_POST['purID']);
    include(__DIR__.'/purchaseReturnInit.php');
    $returnData = $_POST['returnData'];
    $cashPayment = floatval($_POST['cashPayment']);
    $returnRemarks = $_POST['returnRemarks'];
    $jArray['status'] = 0;
    if(!isset($error)){
        unset($jArray['html']);
        if(empty($returnData)){$error=fl(); setMessage(63,'return data');}
        elseif($cashPayment<0){$error=fl(); setMessage(63,'cash payment amount.');}
        else{

            $returnDetails=[];
            $totalReturn=0;
            foreach($returnData as $pID=>$rd){
                if(!isset($purchaseReturnData[$pID])){$error=fl(); setMessage(63,'product');break;}
                $sr = $purchaseReturnData[$pID];
                $p = $products[$sp['pID']];
                if($rd['returnQty']>$sr['avQty']){$error=fl(); setMessage(63,'quantity for '.$p['title']);}
                if($rd['returnUp']>$sr['sale_up']){$error=fl(); setMessage(63,'unit price for '.$p['title']);}
                $returnDetails[$pID]=[
                    'purchasse_id' => $purID,
                    'productID' => $pID,
                    'quantity' => $rd['returnQty'],
                    'unitPrice' => $rd['returnUp'],
                ];
                $totalReturn+=$rd['returnQty']*$rd['returnUp'];
            }
            if($totalReturn<$cashPayment){$error=fl(); setMessage(63,'cash payment amount.');}
            elseif($subTotal<$totalReturn){$error=fl(); setMessage(63,'amount.');}
            if(!isset($error)){
                $db->transactionStart();
                $data=[
                    'purchase_id'=>$purID,
                    'note'=>$returnRemarks,
                    'returnAmount'=>$totalReturn,
                ];
                $db->arrayUserInfoAdd($data);

                $id = $db->insert('purchase_return',$data,true);
                if($id==false){$error=fl(); setMessage(66);}
                else{
                    $invoiceNo = $db->setAutoCode('purchaseReturn',$id);
                    foreach($returnDetails as $pID=>$data){
                        $data['purchaseReturnID']=$id;
                        $insert = $db->insert('purchase_return_details',$data);
                        if(!$insert){$error=fl(); setMessage(66);}
                        else{
                            $p = $products[$pID];
                            $nextQty=$p['stock'];
                            $log=$smt->productStockChangeLog($pID,$data['quantity'],ST_CH_PURCHASE_RETURN,$id,TIME);
                            if($log==false){$error=fl();setMessage(66);}
                            $update=$smt->update_product_closing_stock($product_id,$jArray);
                            if($update==false){$error=fl();setMessage(66);}
                            // $nextQty-=$data['quantity'];
                            // $where=['pID'=>$pID];
                            // $update=$db->update('products',['stock'=>$nextQty],$where);
                            // if($update==false){$error=fl();setMessage(66);}

                        }
                    }
                }
                if(!isset($error)){
 
                    
                    $purchaseHead=$acc->getSystemHead(AH_PURCHASE);
                    if($purchaseHead==false){$error=fl();setMessage(66);}

                    
                    $supHead=$acc->getSupplierHead($sup);
                    if($supHead==false){$error=fl();setMessage(66);}
                    
                    
                    
                    $cashHead=$acc->getSystemHead(AH_CASH);
                    if($cashHead==false){$error=fl();setMessage(66);}
                }
                if(!isset($error)){
                    $customerAmount=$totalReturn;
                    $particular='Purchase return '.$sup['supName'].' '.$id.' '.$invoiceNo;
                    $voucher=$acc->newVoucher(0,V_T_PURCHASE_RETURN,$customerAmount,$purchaseHead,$supHead,TIME,$particular,$id);
                    if($voucher==false){$error=fl();setMessage(66);}
                    if($cashPayment>0){
                        $supplyerPaid = $cashPayment;
                        $voucher=$acc->newVoucher(0,V_T_PURCHASE_RETURN_CASH_RECEIVE_FROM_SUPPLYER,$supplyerPaid,$supHead,$cashHead,TIME,$particular,$id);
                        if($voucher==false){$error=fl();setMessage(66);}
                    }
                }
                $ac=false;
                if(!isset($error)){
                    $jArray['status'] = 1;
                    setMessage(2,'Purchase return successfully');
                    $ac=true;
                }
                $db->transactionStop($ac);
            }

        }
    }
?>
