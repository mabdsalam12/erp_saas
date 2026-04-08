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
    $order_no='';
    $order_date=date('d-m-Y');
    


    $customer_data=$smt->get_base_wise_all_customer();
    $base_customers=$customer_data['base_customers'];
    $customers=$customer_data['customers'];

    
    $use_product_category = $db->get_company_settings('use_product_category');
    if(isset($_GET['draftID'])){
        $draftID = intval($_GET['draftID']);
        $draft = $db->get_rowData('sale_draft','id',$draftID);

        if(!empty($draft)){
            $cID = intval($draft['customer_id']);
            $base_id = intval($draft['base_id']);
            $due_day = $draft['due_day'];
            $order_no = $draft['order_no']??'';
            $purDate = $general->make_date($draft['date']);
            if($draft['order_date']>0){
                $order_date = $general->make_date($draft['order_date']);
            }
            $sData =  $general->getJsonFromString($draft['data']);
            $product = $sData['products']??[] ;
            $discount = $sData['discount'] ?? '';
            $pay_type = $sData['pay_type'] ?? '';
            $note = $sData['note'] ?? '';
            $categories=$db->selectAll('product_category','where isActive=1');
            $general->arrayIndexChange($categories,'id');
        }
    }

    $categoryData=$db->getCategoryData(); 
    $productData=$db->getProductData('and type in('.PRODUCT_TYPE_FINISHED.') and isActive=1');
    include_once __DIR__.'/common_sale.php';
    echo "<script>const PRODUCT_TYPE = ".PRODUCT_TYPE_FINISHED.";</script>";


