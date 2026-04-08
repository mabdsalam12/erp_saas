<?php 
    $general->pageHeader($rModule['title'],[$pUrl=>$rModule['title'],1=>'New']);
    $users = $db->selectAll('users','where type='.USER_TYPE_MPO.' and isActive=1','id,name');
    $base = $db->selectAll('base','where status=1 order by code');
    $draftID=0;
    $supInvNo='';
    $purType='';
    $discount='';
    $purDate=date('d-m-Y');
    $product=[];
    $cID=0;
    $base_id = 0;
    $due_day='';
    $note='';
    $pay_type='';
    $use_product_category = $db->get_company_settings('use_product_category');
    $customer_data=$smt->get_base_wise_all_customer();
    $base_customers=$customer_data['base_customers'];
    $customers=$customer_data['customers'];

    $categoryData=$db->getCategoryData(); 
    $productData=$db->getProductData('and type in('.PRODUCT_TYPE_MANUFACTURING.','.PRODUCT_TYPE_PACKAGING.','.PRODUCT_TYPE_RAW.') and isActive=1');
    include_once __DIR__.'/common_sale.php';
    echo "<script>const PRODUCT_TYPE = ".PRODUCT_TYPE_MANUFACTURING.";</script>";