<?php
	$dRange=$_POST['dRange'];
	$reportInfo=['Date :'.$dRange];
	$general->getFromToFromStringt($dRange,$from,$to);
	$supID  = intval($_POST['supID']);
	$scID   = intval($_POST['scID']);
	$dsr    = intval($_POST['dsr']);
	$sr     = intval($_POST['sr']);
	$type   = intval($_POST['type']);
	$wp     = intval($_POST['wp'])==1?1:0;
	$q[]='scID='.$scID;
	$q[]='sDate between '.$from.' and '.$to;
	if($supID>0){
		$q[]='supID='.$supID;
	}

	if($dsr>0){
		$q[]='sDsr='.$dsr;
	}
	if($sr>0){
		$q[]='sSr='.$sr;
	}
	if($type==1){
		$q[]='cID=0';
	}
	else if($type==2){
		$q[]='cID>0';
	}

	$sales=$db->selectAll($general->table(14),'where '.implode(' and ',$q).' order by sDate asc');

	$rData=[];
	$tOrder=0;
	$tSubTotal=0;
	$tDue=0;
	$tDueCollect=0;
	$tCost=0;
	$tCommission=0;
	$tBalance=0;
	$tVan=0;
	$tReturn=0;
	$tBounce=0;
	$tTax=0;
	$tSalary=0;
	$tProfit=0;
	$tNetCash=0;
	$serial=1;
	if(!empty($sales)){
		$supIDs=[];
		$eIDs=[];
		$rtIDs=[];
		$sIDs=[];
		$cIDs=[];
		foreach($sales as $s){
			$supIDs[$s['supID']]=$s['supID'];
			$eIDs[$s['sDsr']]=$s['sDsr'];
			$eIDs[$s['sSr']]=$s['sSr'];
			$rtIDs[$s['rtID']]=$s['rtID'];
			$sIDs[$s['sID']]=$s['sID'];
			if($s['cID']>0){
				$cIDs[$s['cID']]=$s['cID'];
			}
		}
		$salaryVouchers=$acc->voucherDetails(V_T_EMPLOYEE_SALE_SALARY_PAY,$sIDs);
		$general->arrayIndexChange($salaryVouchers,'vTypeRef');
		$suppliers=$db->selectAll($general->table(45),'where supID in('.implode(',',$supIDs).')');
		$general->arrayIndexChange($suppliers,'supID');
		$routes=$db->selectAll($general->table(5),'where rtID in('.implode(',',$rtIDs).')');
		$general->arrayIndexChange($routes,'rtID');
		//$jArray[__LINE__]=$routes;
		$employees=$db->selectAll($general->table(74),'where eID in('.implode(',',$eIDs).')');
		$general->arrayIndexChange($employees,'eID');
		$returns=$db->selectAllByID($general->table(16),'sID',$sIDs,'',false);
		$otherCosts=$db->selectAllByID($general->table(19),'sID',$sIDs,'',false);
		$dueData=$db->selectAllByID($general->table(20),'sID',$sIDs,'',false);
        if(!empty($cIDs)){
            $customers=$db->selectAllByID($general->table(145),'cID',$cIDs);
        }
        else{
            $customers=[];
        }
		
		$dueInfo=[];
		foreach($dueData as $d){
			if(!isset($dueInfo[$d['sID']])){
				$dueInfo[$d['sID']]=['d'=>0,'c'=>0];
			}
			$dueInfo[$d['sID']]['d']+=$d['sdAmount'];
			$dueInfo[$d['sID']]['c']+=$d['sdCollect'];
		}

		$sdDue=$db->selectAll($general->table(20),'where sdRef in ('.implode(',',$eIDs).') group by sdRef' ,'sum(sdAmount)-sum(sdCollect) as due, sdRef as eID');
		$general->arrayIndexChange($sdDue,'eID');

		foreach($sales as $s){
			$sID=$s['sID'];
			$due=0;
			$dueCollect=0;
			if(isset($dueInfo[$sID])){
				$due=$dueInfo[$sID]['d'];
				$dueCollect=$dueInfo[$sID]['c'];
			}
			$srTDue     =0;
			$dsrTDue    =0;
			if(isset($sdDue[$s['sSr']]['due'])){
				$srTDue=$sdDue[$s['sSr']]['due'];
			}
			if(isset($sdDue[$s['sDsr']]['due'])){
				$dsrTDue=$sdDue[$s['sDsr']]['due'];
			}

			$tDue       += $due;
			$tDueCollect+= $dueCollect;
			$tProfit    += $s['sProfit'];
			$balance    =  $s['sTotal']-$s['sDue'];
			$tBalance   += $balance;
			$return     =  0;
			$cost       =  0;
			$commission =  0;
			$van        =  0;
			$tax        =  0;
			$salary     =  0;
			$sale       =  0;
			if(!empty($returns)){
				foreach($returns as $r){
					if($r['sID']==$sID){
						$return+=$r['srTotal'];
					}
				}
			}

			$sale=$s['sTotal']-$return;
			$tSubTotal  +=$sale;

			if(!empty($otherCosts)){
				foreach($otherCosts as $r){
					if($r['sID']==$sID){
						if($r['socType']==OTHER_COST_COST){
							$cost+=$r['scAmount'];
						}
						elseif($r['socType']==OTHER_COST_COMMISSION){
							$commission+=$r['scAmount'];
						}
						elseif($r['socType']==OTHER_COST_VAN_CHARGE){
							$van+=$r['scAmount'];
						}
						elseif($r['socType']==OTHER_COST_TAX){
							$tax+=$r['scAmount'];
						}
					}
				}
			}
			if(array_key_exists($sID,$salaryVouchers)){
				$salary=$salaryVouchers[$sID]['amount'];
			}
			$tReturn+=$return;
			$tCost+=$cost;
			$tCommission+=$commission;
			$tVan+=$van;
			$tTax+=$tax;
			$tSalary+=$salary;
			$oCost=$cost+$commission+$van+$tax+$salary;
			$balance=$s['sTotal']-($oCost+$return);
			$tOrder+=$s['sOrder'];
			$bounce=($s['sOrder']-$s['sTotal']);//+$return;
			$tBounce+=$bounce;
			$netProfit=$s['sProfit']-($salary+$cost+$tax+$van);
			$netCash=$balance-$due+$dueCollect;
			$tNetCash+=$netCash;
			$sp=0;
			$bp=0;
			if($s['sOrder']>0&&$s['sTotal']>0){
				$sp=($bounce*100)/$s['sOrder'];
				$bp=$sp;
				$sp=100-$sp;
			}
			$cName='';
			if($s['cID']>0){
				$cName=@$customers[$s['cID']]['cName'];
			}
			$rData[]=[
				's'     => $serial++,
				'd'     => $general->make_date($s['sDate']),
				'su'    => $suppliers[$s['supID']]['supName'],
				'ds'    => $employees[$s['sDsr']]['eName'],
				'sr'    => $employees[$s['sSr']]['eName'],
				'rt'    => $routes[$s['rtID']]['rtTitle'],
				'cs'    => $cName,
				'sp'    => $general->numberFormat($sp).'%',
				'bp'    => $general->numberFormat($bp).'%',
				'o'     => $general->numberFormat($s['sOrder'],0),
				'ts'    => $general->numberFormat($sale,0),
				'srD'   => $general->numberFormat($srTDue,0),
				'dsrD'  => $general->numberFormat($dsrTDue,0),
				'bn'    => $general->numberFormat($bounce,0),
				'r'     => $general->numberFormat($return,0),
				'cm'    => $general->numberFormat($commission,0),
				'c'     => $general->numberFormat($cost,0),
				'tx'    => $general->numberFormat($tax,0),
				'v'     => $general->numberFormat($van,0),
				'sl'    => $general->numberFormat($salary,0),
				'b'     => $general->numberFormat($balance,0),
				'du'    => $general->numberFormat($due,0),
				'dc'    => $general->numberFormat($dueCollect,0),
				'nc'    => $general->numberFormat($netCash,0),
				'pr'    => $general->numberFormat($s['sProfit'],0),
				'np'    => $general->numberFormat($netProfit,0),
				'p'     =>'<a href="'.URL.'/?print=sale&sID='.$s['sID'].'" target="_blank" class="btn btn-success">Print</a>',
				'e'=>'<a href="?mdl=dealerSale&edit='.$s['sID'].'" class="btn btn-info">Edit</a>'
			];
		}


	}
	$sp=0;
	$bp=0;
	if($tOrder>0&&$tSubTotal>0){
		$sp=($tBounce*100)/$tOrder;
		$bp=$sp;
		$sp=100-$sp;
	}
	$rData[]=[
		's'=>'',
		'd'=>['t'=>'Total','b'=>1,'col'=>5],
		'su'=>['t'=>false],
		'ds'=>['t'=>false],
		'sr'=>['t'=>false],
		'rt'=>['t'=>false],
		'srD'=>'',
		'dsrD'=>'',
		'cs'=>'',
		'o'=>['t'=>$general->numberFormat($tOrder,0),'b'=>1],
		'ts'=>['t'=>$general->numberFormat($tSubTotal,0),'b'=>1],
		'sp'=>['t'=>$general->numberFormat($sp,0).'%','b'=>1],
		'bp'=>['t'=>$general->numberFormat($bp,0).'%','b'=>1],
		'r'=>['t'=>$general->numberFormat($tReturn,0),'b'=>1],
		'bn'=>['t'=>$general->numberFormat($tBounce,0),'b'=>1],
		'cm'=>['t'=>$general->numberFormat($tCommission,0),'b'=>1],
		'c'=>['t'=>$general->numberFormat($tCost,0),'b'=>1],
		'tx'=>['t'=>$general->numberFormat($tTax,0),'b'=>1],
		'v'=>['t'=>$general->numberFormat($tVan,0),'b'=>1],
		'sl'=>['t'=>$general->numberFormat($tSalary,0),'b'=>1],
		'b'=>['t'=>$general->numberFormat($tBalance,0),'b'=>1],
		'du'=>['t'=>$general->numberFormat($tDue,0),'b'=>1],
		'dc'=>['t'=>$general->numberFormat($tDueCollect,0),'b'=>1],
		'pr'=>['t'=>$general->numberFormat($tProfit,0),'b'=>1],
		'nc'=>['t'=>$general->numberFormat($tNetCash,0),'b'=>1],
		'p'=>'',
		'e'=>''
	];

	$headData=[
		array('title'=>"#"          ,'key'=>'s'),
		array('title'=>"Date"       ,'key'=>'d'     ,'hw'=>9),
		array('title'=>"Supplier"   ,'key'=>'su'     ,'hw'=>7),
	];
	if($type==0||$type==2){
		$headData[]=array('title'=>"Customer" ,'key'=>'cs'     ,'hw'=>10);
	}
	$headData[]=array('title'=>"DSR"        ,'key'=>'ds'     ,'hw'=>10);
	$headData[]=array('title'=>"SR"         ,'key'=>'sr'     ,'hw'=>10);
	$headData[]=array('title'=>"Route"      ,'key'=>'rt'     ,'hw'=>9);
	$headData[]=array('title'=>"Order"      ,'key'=>'o'     ,'al'=>'r');
	$headData[]=array('title'=>"Sr Due"     ,'key'=>'srD'   ,'al'=>'r');
	$headData[]=array('title'=>"Dsr Due"    ,'key'=>'dsrD'  ,'al'=>'r');
	$headData[]=array('title'=>"Bounce"     ,'key'=>'bn'    ,'al'=>'r');
	$headData[]=array('title'=>"Return"     ,'key'=>'r'     ,'al'=>'r');
	$headData[]=array('title'=>"Sale"       ,'key'=>'ts'    ,'al'=>'r');
	$headData[]=array('title'=>"Sale %"     ,'key'=>'sp'    ,'al'=>'r');
	$headData[]=array('title'=>"Bounce%"    ,'key'=>'bp'    ,'al'=>'r');
	$headData[]=array('title'=>"Comm"       ,'key'=>'cm'    ,'al'=>'r');
	$headData[]=array('title'=>"Van"        ,'key'=>'v'     ,'al'=>'r');
	$headData[]=array('title'=>"Tax"        ,'key'=>'tx'    ,'al'=>'r');
	$headData[]=array('title'=>"Cost"       ,'key'=>'c'     ,'al'=>'r');
	$headData[]=array('title'=>"Salary"     ,'key'=>'sl'    ,'al'=>'r');
	$headData[]=array('title'=>"Balance"    ,'key'=>'b'     ,'al'=>'r');
	$headData[]=array('title'=>"Due"        ,'key'=>'du'    ,'al'=>'r');
	$headData[]=array('title'=>"Due C",'key'=>'dc'    ,'al'=>'r');
	$headData[]=array('title'=>"Net Cash"   ,'key'=>'nc'    ,'al'=>'r');
	if($wp==1){
		$headData[]=['title'=>"Profit"   ,'key'=>'pr'    ,'al'=>'r'];
	}
	$headData[]=['title'=>"Print"           ,'key'=>'p','noForExcel'=>1];
	$headData[]=['title'=>"Edit"            ,'key'=>'e','noForExcel'=>1];
	$fileName='purRep_'.TIME.rand(0,999).'.txt';
	$report_data=array(
		'name'      => 'purRep'.date('d_m_Y',$from).'_'.date('d_m_Y',$to),
		'title'     => 'Sale Report',
		'info'      => $reportInfo,
		'fileName'  => $fileName,
		'head'=>$headData,
		'data'=>$rData
	);
	$jArray[__LINE__]=$report_data;
	$gAr['report_data']= $report_data;
	textFileWrite(json_encode($report_data),ROOT_DIR.'/print_file/'.$fileName);
	$jArray['html']     = $general->fileToVariable(ROOT_DIR.'/ajax/report.phtml');
	$jArray['status']=1;   
?>
