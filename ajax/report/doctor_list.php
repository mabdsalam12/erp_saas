<?php                              
$base_id = intval($_POST['base_id']);    
$reportInfo=[];             
$rData=[];
$sr=1;

if($base_id>0){
    $doctors = $db->selectAll('doctor','where base_id='.$base_id); 
}
else{
    $doctors = $db->selectAll('doctor'); 
}  

$types = $general->get_all_doctor_type();
$categorys = $general->get_all_doctor_category();  
$jArray[fl()]=$types;  

if(!empty($doctors)){
    $doctor_ids=[];
    foreach($doctors as $d){
        $doctor_ids[]=$d['id'];
    }

    $doctor_products = $db->selectAll('doctor_products','where id in ('.implode(',',$doctor_ids).')');

    $products_by_doctor=[];
    $products_ids =[];
    foreach ($doctor_products as $p){
         if(!isset($products_by_doctor[$p['doctor_id']])){
             $products_by_doctor[$p['doctor_id']]=[];
         }
         $products_by_doctor[$p['doctor_id']][]=$p['product_id'];
         if(!isset($products_ids[$p['product_id']])){
               $products_ids[$p['product_id']]= $p['product_id'];
         }
    }
    
    if(!empty($products_ids)){
        $products = $db->selectAll('products','where id in ('.implode(',',$products_ids).')');
        $general->arrayIndexChange($products);
    }

    foreach($doctors as $d){
        $product_list='';
        
        if(isset($products_by_doctor[$d['id']])){
            $a=0;
             foreach($products_by_doctor[$d['id']] as $p){
                 if($a==0){
                    $product_list.= $products[$p]['title'];
                    $a=1; 
                 }
                 else{
                    $product_list.= ', '.$products[$p]['title']; 
                 }
                  
             }
        }
        $rData[]=[
           // 's'=>$sr++,
            'd'=>$d['name'],
            'cd'=>$d['code'],
            'a'=>$d['address'],
            'm'=>$d['mobile'],
            'p'=>$product_list,
            'c'=>$categorys[$d['category']]['title'],
            't'=>$types[$d['type']]['title'],
        ];
    }
}
$general->arraySortByColumn($rData,'cd');
    foreach($rData as $k=>$v){
        $rData[$k]['s']=$k+1;
    }
$head=[
    ['title'=>'SL'          ,'key'=>'s','hw'=>5],
    ['title'=> 'Code','key'=> 'cd'],
    ['title'=>'Doctor name' ,'key'=>'d'],
    ['title'=>'Address'     ,'key'=>'a'],
    ['title'=>'Mobile'      ,'key'=>'m'],
    ['title'=>'Prescribed Product','key'=>'p','hw'=>50],
    ['title'=>'Category'    ,'key'=>'c' ],
    ['title'=>'Type'        ,'key'=>'t' ],
];
$fileName='customer_visit_list_'.TIME.rand(0,999).'.txt';
$report_data=[
    'name'      => 'doctor_visit_list',
    'title'     => 'doctor visit list',
    'info'      => $reportInfo,
    'head'=>$head,
    'data'=>$rData
];
$gAr['report_data']= $report_data;
textFileWrite(json_encode($report_data),ROOT_DIR.'/print_file/'.$fileName);
$jArray['html']     = $general->fileToVariable(ROOT_DIR.'/ajax/report.phtml');
$jArray['status']=1;
