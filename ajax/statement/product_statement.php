<?php
	$product_id=intval($_POST['product_id']);
	$p=$smt->productInfoByID($product_id);

	if(!empty($p)){
		$current_balance=$smt->productClosingStock($product_id,strtotime('+5 year'));
		$jArray[fl()]=[
			$current_balance,$p['stock']
		];
		if($current_balance!=$p['stock']){
			$jArray[fl()]=[
				$current_balance,
				$p['stock']
			];
			$db->update('products',['stock'=>$current_balance],['id'=>$product_id],'array',$jArray);
		}



		$dRange=$_POST['dRange'];
		$reportInfo=['Date :'.$dRange];
		$general->getFromToFromString($dRange,$from,$to);

		$rData=[];
		$balance=$smt->productClosingStock($product_id,$from-1);

		$rData[]=[
			's'=>'',
			'd'=>['t'=>'Opening','b'=>1],
			't'=>'',
			'i'=>'',
			'o'=>'',
			'b'=>['t'=>$balance,'b'=>1],
		];

		$statements=$db->selectAll('product_stock_log','where product_id='.$product_id.' and action_time between '.$from.' and '.$to.' order by action_time asc','','array',$jArray);
		//$jArray[__LINE__]=$statments;
		$sIDs=[];
		$sdIDs=[];
		$eIDs=[];
		$cIDs=[];
		
		$tIn=0;
		$tOut=0;
		$production_ids=[];
		foreach($statements as $st){
			if($st['change_type']==ST_CH_PRODUCTION){
				$production_ids[]=$st['reference_id'];
				if(isset($_GET['rm_un_prd_lg'])){
					$production=$db->get_rowData('production_product','id',$st['reference_id']);
					if(empty($production)){
						$db->delete('product_stock_log',['id'=>$st['id']],'array',$jArray);
					}
				}
				
			}
		}
		$serial=1;
		$purchase_details_ids			= [];
		$sale_details_ids				= [];
		$production_product_source_ids	= [];
		$production_ids					= [];
		foreach($statements as $st){
			
			if($st['change_type']==ST_CH_SALE){
				$cType='Sale';
				$sale_details_ids[$st['reference_id']]=$st['reference_id'];
				
			}
			elseif($st['change_type']==ST_CH_PURCHASE){
				$purchase_details_ids[$st['reference_id']]=$st['reference_id'];
				
			}
			elseif($st['change_type']==ST_CH_OPENING){
				$cType='Opening';
			}
			elseif($st['change_type']==ST_CH_SALE_RETURN){
				$cType='Sale Return';
				if(isset($saleReturns[$st['reference_id']])){
					$sd=$saleReturns[$st['reference_id']];
					if(isset($sales[$sd['sID']])){
						$s=$sales[$sd['sID']];
						if(isset($customers[$s['cID']])){
							$c=$smt->customerInfoByID($s['cID']);
							$cName=$c['cName'];
						}
					}
				}
			}
            elseif($st['change_type']==ST_CH_PURCHASE_RETURN){
                $cType='Purchase Return';
            }
            elseif($st['change_type']==ST_CH_ADJUST_CLEAR){
                $cType='Damage Adjust clear';
            }
			elseif($st['change_type']==ST_CH_ADJUST){
				$cType='Damage Adjust';
			}
			elseif($st['change_type']==ST_CH_PRODUCTION){
				$cType='Production';
				$production_ids[$st['reference_id']]=$st['reference_id'];
			}
			elseif($st['change_type']==ST_CH_PRODUCTION_SOURCE){
				$cType='Production source';
				$production_product_source_ids[$st['reference_id']]=$st['reference_id'];
			}
			elseif($st['change_type']==ST_CH_PRODUCTION_SOURCE_MAN){
				$cType='Production source manufacture';
			}
			elseif($st['change_type']==ST_CH_STOCK_ENTRY){
				$cType='Stock entry';
			}
			else{
				$cType='N/A '.$st['change_type'];
			}
		}
		$jArray[fl()]=$production_product_source_ids;
		
		if(!empty($production_product_source_ids)){
			$production_product_source=$db->selectAllByID('production_product_source','id',$production_product_source_ids);
			if(!empty($production_product_source)){
				foreach($production_product_source as $pd){
					$production_ids[$pd['production_id']]=$pd['production_id'];
				}
			}
		}
		if(!empty($production_ids)){
			$production_list=$db->selectAllByID('production_product','id',$production_ids);
		}
		if(!empty($purchase_details_ids)){
			$purchase_details_list=$db->selectAllByID('purchase_details','id',$purchase_details_ids);
			if(!empty($purchase_details_list)){
				$purchase_ids=[];
				foreach($purchase_details_list as $pd){
					$purchase_ids[$pd['purchase_id']]=$pd['purchase_id'];
				}
				$purchase_list=$db->selectAllByID('purchase','id',$purchase_ids);
			}
		}
		if(!empty($sale_details_ids)){
			$sale_products=$db->selectAllByID('sale_products','id',$sale_details_ids);
			if(!empty($sale_products)){
				$sale_ids=[];
				foreach($sale_products as $pd){
					$sale_ids[$pd['sale_id']]=$pd['sale_id'];
				}
				$sales_list=$db->selectAllByID('sale','id',$sale_ids);
			}
		}
		foreach($statements as $st){
			$in=0;
			$out=0;
			$cName='';
			$cType='';
			$ref='';
			if($st['change_type']==ST_CH_SALE){
				$cType='Sale';
				$jArray[fl()][]=$st['reference_id'];
				if(isset($sale_products[$st['reference_id']])){
					$pd=$sale_products[$st['reference_id']];
					if(isset($sales_list[$pd['sale_id']])){
						$ref=$sales_list[$pd['sale_id']]['invoice_no'];
					}
					else{
						$ref=' N/A';
					}
				}
				else{
					$ref=' N/A '.$st['reference_id'];
				}
			}
			elseif($st['change_type']==ST_CH_PURCHASE){
				$cType='Purchase';
				if(isset($purchase_details_list[$st['reference_id']])){
					$pd=$purchase_details_list[$st['reference_id']];
					if(isset($purchase_list[$pd['purchase_id']])){
						$ref=$purchase_list[$pd['purchase_id']]['mrr_code'];
						if($ref==''){
							$ref='Not set';
						}
					}
					else{
						$ref=' N/A';
					}
				}
				else{
					$ref=' N/A '.$st['reference_id'];
				}
			}
			elseif($st['change_type']==ST_CH_OPENING){
				$cType='Opening';
			}
			elseif($st['change_type']==ST_CH_SALE_RETURN){
				$cType='Sale Return';
				if(isset($saleReturns[$st['reference_id']])){
					$sd=$saleReturns[$st['reference_id']];
					if(isset($sales[$sd['sID']])){
						$s=$sales[$sd['sID']];
						if(isset($customers[$s['cID']])){
							$c=$smt->customerInfoByID($s['cID']);
							$cName=$c['cName'];
						}
					}
				}
			}
            elseif($st['change_type']==ST_CH_PURCHASE_RETURN){
                $cType='Purchase Return';
            }
            elseif($st['change_type']==ST_CH_ADJUST_CLEAR){
                $cType='Damage Adjust clear';
            }
			elseif($st['change_type']==ST_CH_ADJUST){
				$cType='Damage Adjust';
			}
			elseif($st['change_type']==ST_CH_DISTRIBUTE){
				$cType='Gift distribute';
			}
			elseif($st['change_type']==ST_CH_PRODUCTION){
				$cType='Production '.$st['reference_id'];
				if(isset($production_list[$st['reference_id']])){
					$ref=$production_list[$st['reference_id']]['batch_no'];
				}
				else{
					$ref=' N/A '.$st['reference_id'];
				}
			}
			elseif($st['change_type']==ST_CH_PRODUCTION_SOURCE){
				$cType='Production source';
				if(isset($production_product_source[$st['reference_id']])){
					$pd=$production_product_source[$st['reference_id']];
					if(isset($production_list[$pd['production_id']])){
						$ref=' '.$production_list[$pd['production_id']]['batch_no'];
					}
					else{
						$ref=' N/A';
					}
				}
				else{
					$ref=' N/A '.$st['reference_id'];
				}
			}
			elseif($st['change_type']==ST_CH_PRODUCTION_SOURCE_MAN){
				$cType='Production source manufacture';
			}
			elseif($st['change_type']==ST_CH_REJECT){
				$cType='Reject entry';
			}
			elseif($st['change_type']==ST_CH_STOCK_ENTRY){
				$cType='Stock entry';
			}

			else{
				$cType='N/A '.$st['change_type'];
			}
			//$cType.=' -> '.$st['change_type'];
			if($st['quantity']>0){
				$in=$st['quantity'];
			}
			else{
				$out=-$st['quantity'];
			}
			$balance+=$in;
			$balance-=$out;
			$tIn+=$in;
			$tOut+=$out;


			$data=[
				's' => $serial++,
				'd' => $general->make_date($st['action_time'],'time').' ('.$st['id'].')',
				'e' => $general->make_date($st['entry_time'],'time'),
				'r' => $ref,
				't' => $cType,
				'i' => $in,
				'o' => $out,
				'b' => $balance,
			];
			$rData[]=$data;
		}
		$rData[]=[
			's' => '',
			'd' => ['t'=>'Total','b'=>1,'col'=>4],
			'e' => ['t'=>false],
			't' => ['t'=>false],
			'r' => ['t'=>false],
			'i' => ['t'=>$tIn,'b'=>1],
			'o' => ['t'=>$tOut,'b'=>1],
			'b' => ['t'=>$balance,'b'=>1],
		];
		$fileName='product_statement'.TIME.rand(0,999).'.txt';
		$report_data=[
			'name'      => 'product_statement'.date('d_m_Y',$from).'_'.date('d_m_Y',$to),
			'title'     => 'Product Statement',
			'info'      => $reportInfo,
			'fileName'  => $fileName,
			'head'=>[
				array('title'=>"#"      ,'key'=>'s'     ,'hw'=>5),
				array('title'=>"Date"   ,'key'=>'d'),
				array('title'=>"Entry"  ,'key'=>'e'),
				//array('title'=>"Customer",'key'=>'c'),
				array('title'=>"Type"   ,'key'=>'t' ),
				array('title'=>"Ref"   	,'key'=>'r' ),
				array('title'=>"In"     ,'key'=>'i'    ,'al'=>'r'),
				array('title'=>"Out"    ,'key'=>'o'    ,'al'=>'r'),
				array('title'=>"Balance",'key'=>'b'    ,'al'=>'r'),
			],
			'data'=>$rData
		];
		$jArray[__LINE__]=$report_data;
		$gAr['report_data']= $report_data;
		textFileWrite(json_encode($report_data),ROOT_DIR.'/print_file/'.$fileName);
		$jArray['html']     = $general->fileToVariable(ROOT_DIR.'/ajax/report.phtml');
		$jArray['status']=1;
	}
