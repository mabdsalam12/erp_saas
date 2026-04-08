<?php
$id=intval($_POST['id']);
$purchase=$db->get_rowData('purchase','id',$id);
$products=[];
if(!empty($purchase)){
	$details=$db->selectAll('purchase_details','where purchase_id='.$id);
	$supplier=$smt->supplierInfoByID($purchase['supplier_id']);
	$units=$smt->getAllUnit();
	foreach($details as $d){
		$p=$smt->productInfoByID($d['product_id']);
		$products[]=[
			'id'		=> $d['product_id'],
			'title'		=> $p['title'],
			'unit'		=> $units[$p['unit_id']]['title'],
			'quantity'	=> (float)$d['quantity'],
			'unit_price'=> (float)$d['unit_price'],
			'total'		=> $general->numberFormat($d['quantity']*$d['unit_price'])
		];
	}
	$jArray['status']=1;
	$jArray['info']=[
		'supplier'		=> $supplier['name'],
		'invoice_no'	=> $purchase['invoice_no'],
		'date'			=> date('d/m/Y',$purchase['date']),
		'challan_date'	=> date('d/m/Y',$purchase['challan_date']),
		'mrr_date'		=> date('d/m/Y',$purchase['mrr_date']),
		'challan_no'	=> $purchase['challan_no'],
		'mrr_no'	=> $purchase['mrr_code'],
		'sub_total'	=> $general->numberFormat($purchase['sub_total']),
		'discount'	=> $general->numberFormat($purchase['discount']),
		'VAT'		=> $general->numberFormat($purchase['VAT']),
		'AIT'		=> $general->numberFormat($purchase['AIT']),
		'total'		=> $general->numberFormat($purchase['total']),
		'remarks'	=> $purchase['remarks'],
		'createdOn'	=> $general->make_date($purchase['createdOn'],'time'),
		'createdBy'	=> $db->userInfoByID($purchase['createdBy'])['name']
	];
	$jArray['products']=$products;

}