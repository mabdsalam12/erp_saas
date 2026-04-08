<?php
	$general->createLog('saleDelete',$_GET);
	$jArray=[];
	$saleProduct=$db->selectAll($general->table(15),'where sID='.$sID);
	$saleReturns=$db->selectAll($general->table(16),'where sID='.$sID);
	$saleOtherCosts=$db->selectAll($general->table(19),'where sID='.$sID);
	$dueColDisribute=$db->selectAll($general->table(20),'where sID='.$sID);
	$jArray['saleProduct']      = $saleProduct;
	$jArray['saleReturns']      = $saleReturns;
	$jArray['saleOtherCosts']   = $saleOtherCosts;
	$jArray['dueColDisribute']  = $dueColDisribute;
	$canRun=false;$echo='n';
	$canRun=true;$echo='No';
	if($s['cID']>0){$error=fl();setMessage(1,'This sale not ready for delete');}
	if(!isset($error)){
		$db->transactionStart();
		foreach($saleProduct as $sp){
			//            $general->printArray('$sp');
			//            $general->printArray($sp);
			$productScource=$db->selectAll($general->table(21),'where sID='.$sID.' and pID='.$sp['pID']);
			//            $general->printArray('$productScource');
			//            $general->printArray($productScource);
			if(!empty($productScource)){
				foreach($productScource as $ps){
					if($ps['spsType']==SALE_PRD_SRC_TYPE_OPENING){
						//                        $general->printArray('Scource Opening');
						$oStock=$db->get_rowData($general->table(3),'posID',$ps['spsRefID']);
						//                        $general->printArray('$oStock');
						//                        $general->printArray($oStock);
						$data=[
							'inStock'=>$oStock['inStock']+$ps['qty']
						];
						//                        $general->printArray('$data');
						//                        $general->printArray($data);
						$where=['posID'=>$oStock['posID']];
						if($canRun==true){
							$update=$db->update($general->table(3),$data,$where,$echo,$jArray);if($update==false){$error=fl();setMessage(66);}
						}
						$p=$smt->productInfoByID($sp['pID'],false);
						$data=['stock'=>$p['stock']+$ps['qty']];
						$where=['pID'=>$sp['pID']];
						if($canRun==true){
							$update=$db->update($general->table(104),$data,$where,$echo,$jArray);if($update==false){$error=fl();setMessage(66);}
						}
						$stockLog=$db->getRowData($general->table(2),'where pID='.$sp['pID'].' and psType='.PRODUCT_TYPE_GOOD.' and changeType='.ST_CH_SALE.' and refID='.$sp['sdID']);
						//                        $general->printArray('$stockLog');
						//                        $general->printArray($stockLog);
						$where=['pslID'=>$stockLog['pslID']];
						if($canRun==true){
							$delete=$db->delete($general->table(2),$where,$echo,$jArray);if($delete==false){$error=fl();setMessage(66);}
						}

					}
					elseif($ps['spsType']==SALE_PRD_SRC_TYPE_RETURN){
						$oStock=$db->get_rowData($general->table(16),'srID',$ps['spsRefID']);
						//                        $general->printArray('$oStock');
						//                        $general->printArray($oStock);
						$data=[
							'inStock'=>$oStock['inStock']+$ps['qty']
						];
						//                        $general->printArray('$data');
						//                        $general->printArray($data);
						$where=['srID'=>$oStock['srID']];
						if($canRun==true){
							$update=$db->update($general->table(16),$data,$where,$echo,$jArray);if($update==false){$error=fl();setMessage(66);}
						}
						$p=$smt->productInfoByID($sp['pID'],false);
						$data=['stock'=>$p['stock']+$ps['qty']];
						$where=['pID'=>$sp['pID']];
						if($canRun==true){
							$update=$db->update($general->table(104),$data,$where,$echo,$jArray);if($update==false){$error=fl();setMessage(66);}
						}
						$stockLog=$db->getRowData($general->table(2),'where pID='.$sp['pID'].' and psType='.PRODUCT_TYPE_GOOD.' and changeType='.ST_CH_SALE.' and refID='.$sp['sdID']);
						//                        $general->printArray('$stockLog');
						//                        $general->printArray($stockLog);
						$where=['pslID'=>$stockLog['pslID']];
						if($canRun==true){
							$delete=$db->delete($general->table(2),$where,$echo,$jArray);if($delete==false){$error=fl();setMessage(66);}
						}

						//$error=fl();
					}
					elseif($ps['spsType']==SALE_PRD_SRC_TYPE_PURCHASE){
						$oStock=$db->get_rowData($general->table(12),'pdID',$ps['spsRefID']);
						//                        $general->printArray('$oStock');
						//                        $general->printArray($oStock);
						$data=[
							'inStock'=>$oStock['inStock']+$ps['qty']
						];
						//                        $general->printArray('$data');
						//                        $general->printArray($data);
						$where=['pdID'=>$oStock['pdID']];
						if($canRun==true){
							$update=$db->update($general->table(12),$data,$where,$echo,$jArray);if($update==false){$error=fl();setMessage(66);}
						}
						$p=$smt->productInfoByID($sp['pID'],false);
						$data=['stock'=>$p['stock']+$ps['qty']];
						$where=['pID'=>$sp['pID']];
						if($canRun==true){
							$update=$db->update($general->table(104),$data,$where,$echo,$jArray);if($update==false){$error=fl();setMessage(66);}
						}
						$stockLog=$db->getRowData($general->table(2),'where pID='.$sp['pID'].' and psType='.PRODUCT_TYPE_GOOD.' and changeType='.ST_CH_SALE.' and refID='.$sp['sdID']);
						//$general->printArray('$stockLog');
						//$general->printArray($stockLog);
						$where=['pslID'=>$stockLog['pslID']];
						if($canRun==true){
							$delete=$db->delete($general->table(2),$where,$echo,$jArray);if($delete==false){$error=fl();setMessage(66);}
						}

					}
				}
			}

		}
		//$general->printArray('$saleReturns');
		//$general->printArray($saleReturns);
		if(!empty($saleReturns)){
			foreach($saleReturns as $sr){

				//$general->printArray('$sr');
				//$general->printArray($sr);

				if($sr['srType']==PRODUCT_TYPE_DAMAGE){

					$p=$smt->productInfoByID($sr['pID'],false);
					$data=['stock'=>$p['stock']+$sr['srQty']];
					$where=['pID'=>$sr['pID']];
					if($canRun==true){
						$update=$db->update($general->table(104),$data,$where,$echo,$jArray);if($update==false){$error=fl();setMessage(66);}
					}
					$stockLog=$db->getRowData($general->table(2),'where pID='.$sr['pID'].' and psType='.PRODUCT_TYPE_DAMAGE.' and changeType='.ST_CH_SALE_RETURN.' and refID='.$sr['srID'],'');
					//                    $general->printArray('$stockLog d');
					//                    $general->printArray($stockLog);
					$where=['pslID'=>$stockLog['pslID']];
					if($canRun==true){
						$delete=$db->delete($general->table(2),$where,$echo,$jArray);if($delete==false){$error=fl();setMessage(66);}
					}
				}
				elseif($sr['srType']==PRODUCT_TYPE_GOOD){

					$p=$smt->productInfoByID($sr['pID'],false);
					$data=['stock'=>$p['stock']+$sr['srQty']];
					$where=['pID'=>$sr['pID']];
					if($canRun==true){
						$update=$db->update($general->table(104),$data,$where,$echo,$jArray);if($update==false){$error=fl();setMessage(66);}
					}
					$stockLog=$db->getRowData($general->table(2),'where pID='.$sr['pID'].' and psType='.PRODUCT_TYPE_GOOD.' and changeType='.ST_CH_SALE_RETURN.' and refID='.$sr['srID'],'');
					//                    $general->printArray('$stockLog g');
					//                    $general->printArray($stockLog);
					$where=['pslID'=>$stockLog['pslID']];
					if($canRun==true){
						$delete=$db->delete($general->table(2),$where,$echo,$jArray);if($delete==false){$error=fl();setMessage(66);}
					}

				}
				else{$error=fl();setMessage(66);}
			}

		}

		//        $general->printArray('$saleOtherCosts');
		//        $general->printArray($saleOtherCosts);
		if(!empty($saleOtherCosts)){
			if($canRun==true){
				$delete=$db->delete($general->table(19),['sID'=>$sID],$echo,$jArray);if($delete==false){$error=fl();setMessage(66);}
				$delete=$db->delete($general->table(24),['sID'=>$sID],$echo,$jArray);if($delete==false){$error=fl();setMessage(66);}
			}
		}
		//        $general->printArray('$dueColDisribute');
		//        $general->printArray($dueColDisribute);
		if(!empty($dueColDisribute)){
			$delete=$db->delete($general->table(20),['sID'=>$sID],$echo,$jArray);if($delete==false){$error=fl();setMessage(66);}
		}

		if(!isset($error)){
			$saleVouchers=[V_T_SALE_CASH,V_T_SALE_DUE_COLLECT,V_T_SALE_DUE,V_T_SALE_RETURN,V_T_EMPLOYEE_SALE_SALARY_PAY,V_T_SALE_COMMISSION,V_T_SALE_COST,V_T_SALE_TAX,V_T_SALE_VAN_CHART];

			foreach($saleVouchers as $vt){
				$voucher=$acc->voucherDetails($vt,$sID);
				//                $general->printArray('$voucher');
				//                $general->printArray($voucher);
				if(!empty($voucher)){
					foreach($voucher as $v){
						if($canRun==true){
							$delete=$db->delete($general->table(96),['veID'=>$v['veID']],$echo,$jArray);if($delete==false){$error=fl();setMessage(66);}
							$delete=$db->delete($general->table(97),['veID'=>$v['veID']],$echo,$jArray);if($delete==false){$error=fl();setMessage(66);}
						}
					}
				}
			}

			if($canRun==true){
				$delete=$db->delete($general->table(14),['sID'=>$s['sID']],$echo,$jArray);if($delete==false){$error=fl();setMessage(66);}
			}
		}


		//        $error=fl();
		if(!isset($error)){
			$ac=true;
		}
		else{
			$ac=false;
		}
		if(isset($error)){setErrorMessage($error);}
		$db->transactionStop($ac);
	}

	$general->createLog('saleDelete',$jArray);
?>
