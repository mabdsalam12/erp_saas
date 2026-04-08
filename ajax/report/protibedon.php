<?php
	$dRange = $_POST['dRange'];
	$scID   = intval($_POST['scID']);
	$reportInfo=['Date :'.$dRange];
	$general->getFromToFromStringt($dRange,$from,$to);
	//$q=['isActive in(0,1)','scID='.$scID];
	$q=['isActive in(1)','scID='.$scID];
	$suppliers=$db->selectAll($general->table(45),'where '.implode(' and ',$q));
//	$jArray[fl()]=$suppliers;
	$rData=[];
	$i=1;
	$rData[]=[
		$i++=>['t'=>'Name','b'=>1],
		$i++=>['t'=>'Opening Balance','b'=>1],
		$i++=>['t'=>'Opening Stock','b'=>1],
		$i++=>['t'=>'Buy','b'=>1],
		$i++=>['t'=>'Sale','b'=>1],
		$i++=>['t'=>'Paid','b'=>1],
		$i++=>['t'=>'Profit','b'=>1],
		$i++=>['t'=>'Stock','b'=>1],
		$i++=>['t'=>'Commission','b'=>1],
		$i++=>['t'=>'Balance','b'=>1],
	];

	$tpaid=0;
	$tsale=0;
	$tbuy=0;
	$tprofit=0;
	$tcommission=0;
	$topeningStockPrice=0;
	$tClosingBalance=0;
	$tclosingStockPrice=0;
	$topeningBalance=0;
	if(!empty($suppliers)){
		$saleReturns=$db->selectAll($general->table(16),'where srDate between '.$from.' and '.$to,'srID,pID,srTotal','array',$jArray);
		$saleReturnProducts=[];
		if(!empty($saleReturns)){
			foreach($saleReturns as $s){
				$pID=$s['pID'];
				if(!isset($saleReturnProducts[$pID])){
					$saleReturnProducts[$pID]=0;
				}
				$saleReturnProducts[$pID]+=$s['srTotal'];
			}
		}
		foreach($suppliers as $sup){
			$paid=0;
			$sale=0;
			$profit=0;
			$commission=0;
			$openingStockPrice=0;
			$closingStockPrice=0;
			//$products=$db->selectAll($general->table(104),'where supID='.$sup['supID'].' and pID=1','pID,pBuyPrice');
			$products=$db->selectAll($general->table(104),'where supID='.$sup['supID'],'pID,pBuyPrice');
			if(!empty($products)){
				foreach($products as $p){
					$pID=$p['pID'];
					if(isset($saleReturnProducts[$pID])){
						$sale-=$saleReturnProducts[$pID];
					}
					$stock=$db->getRowData('product_closing_stock','where pID='.$pID.' and actionDate<'.$from.' and stockType='.PRODUCT_TYPE_GOOD.' order by actionDate desc','array',$jArray);
					if(!empty($stock)){
                        $openingStockPrice+=$stock['pStockAmount'];
					}
					$stock=$db->getRowData('product_closing_stock','where pID='.$pID.' and actionDate<'.$from.' and stockType='.PRODUCT_TYPE_DAMAGE.' order by actionDate desc','array',$jArray);
					if(!empty($stock)){
                        //$openingStockPrice+=$stock['pStockAmount'];
                        $openingStockPrice+=$stock['stock']*$p['pBuyPrice'];
					}
					$stock=$db->getRowData('product_closing_stock','where pID='.$pID.' and actionDate<'.$to.' and stockType='.PRODUCT_TYPE_GOOD.' order by actionDate desc','array',$jArray);
					if(!empty($stock)){
						$closingStockPrice+=$stock['pStockAmount'];

					}
					$stock=$db->getRowData('product_closing_stock','where pID='.$pID.' and actionDate<'.$to.' and stockType='.PRODUCT_TYPE_DAMAGE.' order by actionDate desc','array',$jArray);
					if(!empty($stock)){
						//$closingStockPrice+=$stock['pStockAmount'];
                        $closingStockPrice+=$stock['stock']*$p['pBuyPrice'];
					}
				}
			}
			$paids=$acc->voucherDetails(V_T_SUPPLIER_PAYMENT,$sup['supID'],$from,$to);
			if(!empty($paids)){
				foreach($paids as $p){
					$paid+=$p['amount'];
				}
			}
			$openingBalance=$acc->headBalance($sup['hID'],$from-1);
			$closingBalance=$acc->headBalance($sup['hID'],$to);
			$tClosingBalance+=$closingBalance;
			$purchases=$db->selectAll($general->table(11),'where supID='.$sup['supID'].' and purDate between '.$from.' and '.$to,'sum(netTotal) as t','array',$jArray);
			$buy=$purchases[0]['t'];
			$sales=$db->selectAll($general->table(14),'where supID='.$sup['supID'].' and sDate between '.$from.' and '.$to,'sID,sTotal,sProfit','array',$jArray);
			if(!empty($sales)){
				$general->arrayIndexChange($sales,'sID');
				foreach($sales as $s){
					$sale+=$s['sTotal'];
					$profit+=$s['sProfit'];
				}
				$commissions=$db->selectAll($general->table(19),'where sID in('.implode(',',array_keys($sales)).') and socType='.OTHER_COST_COMMISSION,'sum(scAmount) as t');
				$commission=$commissions[0]['t'];

			}


			if($profit!=0||$buy!=0||$sales!=0){
				$tpaid              += $paid;
				$tsale              += $sale;
				$tprofit            += $profit;
				$tcommission        += $commission;
				$topeningStockPrice += $openingStockPrice;
				$tclosingStockPrice += $closingStockPrice;
				$topeningBalance    += $openingBalance;
				$tbuy               += $buy;
				$i=1;
				$data=[
					$i++ =>$sup['supName'],
					$i++=>$general->numberFormat($openingBalance),
					$i++=>$general->numberFormat($openingStockPrice),
					$i++=>$general->numberFormat($buy),
					$i++=>$general->numberFormat($sale),
					$i++=>$general->numberFormat($paid),
					$i++=>$general->numberFormat($profit),
					$i++=>$general->numberFormat($closingStockPrice),
					$i++=>$general->numberFormat($commission),
					$i++=>$general->numberFormat($closingBalance),
				];
				$rData[]=$data;
			}
		}
	}
	$i=1;
	$rData[]=[
		$i++=>['t'=>'Total','b'=>1],
		$i++=>['t'=>$general->numberFormat($topeningBalance),'b'=>1],
		$i++=>['t'=>$general->numberFormat($topeningStockPrice),'b'=>1],
		$i++=>['t'=>$general->numberFormat($tbuy),'b'=>1],
		$i++=>['t'=>$general->numberFormat($tsale),'b'=>1],
		$i++=>['t'=>$general->numberFormat($tpaid),'b'=>1],
		$i++=>['t'=>$general->numberFormat($tprofit),'b'=>1],
		$i++=>['t'=>$general->numberFormat($tclosingStockPrice),'b'=>1],
		$i++=>['t'=>$general->numberFormat($tcommission),'b'=>1],
		$i++=>['t'=>$general->numberFormat($tClosingBalance),'b'=>1],
	];


	$leftSide=[];
	$rightSide=[];
	$rightSide2=[];

	$sdDue=$db->selectAll($general->table(20),'where sdDate<'.$to.' group by sdRef' ,'sum(sdAmount)-sum(sdCollect) as due, sdRef as eID');
	$totalDue=0;
	$i=1;
	$leftSide[]=[
		$i++=>['t'=>'Employee due','b'=>1,'col'=>2,'al'=>'c'],
		$i++=>['t'=>false],
		$i++=>['t'=>false],
	];
	$i=1;
	$leftSide[]=[
		$i++=>['t'=>'Employee','b'=>1],
		$i++=>['t'=>'Due','b'=>1],
	];
	if(!empty($sdDue)){
		$general->arrayIndexChange($sdDue,'eID');
		$eIDs=array_keys($sdDue);
		$employees=$db->selectAllByID($general->table(74),'eID',$eIDs);

		foreach($sdDue as $sd){
			if($sd['due']!=0){
				$totalDue+=$sd['due'];
				/* $i=1;
				$leftSide[]=[
				$i++=>$employees[$sd['eID']]['eName'],
				$i++=>$general->numberFormat($sd['due'])
				];*/
			}
		}
	}
	$i=1;
	$leftSide[]=[
		$i++=>['t'=>'Total Due','b'=>1],
		$i++=>['t'=>$general->numberFormat($totalDue),'b'=>1]
	];
	$expTypes=[
		V_T_EXPENSE,
		V_T_SALE_COST,
		V_T_SALE_VAN_CHART,
		V_T_SALE_TAX,
		V_T_EMPLOYEE_PAY,
		V_T_EMPLOYEE_SALE_SALARY_PAY
	];
	$expences=$acc->voucherDetails($expTypes,'',$from,$to);
	//$jArray[__LINE__]=$expences;
	$tVan=0;
	$tTax=0;
	$tCost=0;
	$tSalary=0;
	$tOtherExpense=0;
	$totalExpence=0;
	foreach($expences as $ex){
		$totalExpence+=$ex['amount'];
		if($ex['vType']==V_T_SALE_COST){
			$tCost+=$ex['amount'];
		}
		elseif($ex['vType']==V_T_SALE_VAN_CHART){
			$tVan+=$ex['amount'];
		}
		elseif($ex['vType']==V_T_EXPENSE){
			$tOtherExpense+=$ex['amount'];
		}
		elseif($ex['vType']==V_T_SALE_TAX){
			$tTax+=$ex['amount'];
		}
		elseif($ex['vType']==V_T_EMPLOYEE_PAY||$ex['vType']==V_T_EMPLOYEE_SALE_SALARY_PAY){
			$tSalary+=$ex['amount'];
		}
	}

	$i=1;
	$rightSide[]=[
		$i++=>['t'=>'Other Expense','b'=>1,'col'=>3,'al'=>'c'],
		$i++=>['t'=>false],
		$i++=>['t'=>false],
	];
	$i=1;
	$rightSide[]=[
		$i++=>'Van',
		$i++=>$general->numberFormat($tVan)
	];
	$i=1;
	$rightSide[]=[
		$i++=>'Tax',
		$i++=>$general->numberFormat($tTax)
	];
	$i=1;
	$rightSide[]=[
		$i++=>'Other Expense',
		$i++=>$general->numberFormat($tOtherExpense)
	];
	$i=1;
	$rightSide[]=[
		$i++=>'Salary',
		$i++=>$general->numberFormat($tSalary)
	];
	$i=1;
	$rightSide[]=[
		$i++=>'Sale cost',
		$i++=>$general->numberFormat($tCost)
	];
	$i=1;
	$rightSide[]=[
		$i++=>['t'=>'Total Expense','b'=>1],
		$i++=>['t'=>$general->numberFormat($totalExpence),'b'=>1]
	];
	$totalProfit=$tprofit-$totalExpence;
	$i=1;
	$rightSide[]=[
		$i++=>['t'=>'Total Profit','b'=>1],
		$i++=>['t'=>$general->numberFormat($totalProfit),'b'=>1]
	];

	$i=1;
	$rightSide2[]=[
		$i++=>['t'=>'Other Income','b'=>1,'col'=>3,'al'=>'c'],
		$i++=>['t'=>false],
		$i++=>['t'=>false],
	];
	$extraIncome=$acc->voucherDetails(V_T_INCOME,'',$from,$to);
	$jArray[__LINE__]=$extraIncome;
	$tExtraIncome=0;
	if(!empty($extraIncome)){
		foreach($extraIncome as $ei){
			$tExtraIncome+=$ei['amount'];
			$i=1;
			$rightSide2[]=[
				$i++=>['t'=>$ei['particular']],
				$i++=>['t'=>$general->numberFormat($ei['amount'])]
			];
		}
	}
	$i=1;
	$rightSide2[]=[
		$i++=>['t'=>'Total Extra Income','b'=>1],
		$i++=>['t'=>$general->numberFormat($tExtraIncome),'b'=>1]
	];
	$i=1;
	$rightSide2[]=[
		$i++=>['t'=>'Net Profit','b'=>1],
		$i++=>['t'=>$general->numberFormat($totalProfit+$tExtraIncome),'b'=>1]
	];
	$max=count($leftSide);
	if($max<count($rightSide)){
		$max=count($rightSide);
	}
	if($max<count($rightSide2)){
		$max=count($rightSide2);
	}
	for($j=0;$j<$max;$j++){
		$data=[];
		$i=1;
		if(isset($leftSide[$j])){
			foreach($leftSide[$j] as $l){
				$data[$i++]=$l;
			}
		}
		$i=4;
		if(isset($rightSide[$j])){
			foreach($rightSide[$j] as $l){
				$data[$i++]=$l;
			}
		}
		$i=7;
		if(isset($rightSide2[$j])){
			foreach($rightSide2[$j] as $l){
				$data[$i++]=$l;
			}
		}
		$rData[]=$data;
	}

	$fileName='protibedon_'.TIME.rand(0,999).'.txt';
	$i=1;
	$report_data=array(
		'name'      => 'protibedon'.date('d_m_Y',$from).'_'.date('d_m_Y',$to),
		'title'     => 'Protibedon',
		'info'      => $reportInfo,
		'fileName'  => $fileName,
		'head'=>array(
			array('title'=>$i   ,'key'=>$i++),
			array('title'=>$i   ,'key'=>$i++,'al'=>'r'),
			array('title'=>$i   ,'key'=>$i++,'al'=>'r'),
			array('title'=>$i   ,'key'=>$i++,'al'=>'r'),
			array('title'=>$i   ,'key'=>$i++,'al'=>'r'),
			array('title'=>$i   ,'key'=>$i++,'al'=>'r'),
			array('title'=>$i   ,'key'=>$i++,'al'=>'r'),
			array('title'=>$i   ,'key'=>$i++,'al'=>'r'),
			array('title'=>$i   ,'key'=>$i++,'al'=>'r'),
			array('title'=>$i   ,'key'=>$i++,'al'=>'r'),
		),
		'data'=>$rData
	);
	$jArray[__LINE__]=$report_data;
	$gAr['report_data']= $report_data;
	textFileWrite(json_encode($report_data),ROOT_DIR.'/print_file/'.$fileName);
	$jArray['html']     = $general->fileToVariable(ROOT_DIR.'/ajax/report.phtml');
	$jArray['status']=1;
?>
