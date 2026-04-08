<?php
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
	show_msg();
$gift_distribute=$db->selectAll('gift_distribute','where product_type=-1 limit 50','*','ay',$jArray);
if(!empty($gift_distribute)){
	foreach($gift_distribute as $gd){
		$d=$db->get_rowData('gift_distribute_product','gift_distribute_id',$gd['id'],'ay',$jArray);
		$p=$smt->productInfoByID($d['product_id']);
		$general->printArray($p['type']);
		$data=['product_type'=>$p['type']];
		$where=['id'=>$gd['id']];
		$db->update('gift_distribute',$data,$where,'d');
	}
}
else{
	echo 'No gift distribute records found.';
}