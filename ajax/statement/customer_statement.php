<?php
    
	$customer_id=intval($_POST['customer_id']);
	$customer_category_id=intval($_POST['customer_category_id']);
	$bazar_id=intval($_POST['bazar_id']);
	$base_id=intval($_POST['base_id']);
	$type_zero=intval($_POST['type_zero']);
	$column_zero=intval($_POST['column_zero'])==0?0:1;
	$dRange=$_POST['dRange'];
	$type=$_POST['type']=='s'?'s':'t';
	include_once ROOT_DIR.'/include/customer_statement.php';