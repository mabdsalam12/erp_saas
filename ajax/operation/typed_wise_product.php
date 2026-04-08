<?php
  $type = intval($_POST['type']);
  $productData=$db->getProductData('and type='.$type);
  $jArray['product_data'] =  $productData;
  $jArray['status'] = 1;

