<?php
// require_once(ROOT_DIR.'/tcpdf/tcpdf.php');
include_once(__DIR__.'/pdf_functions_'.PROJECT.'.php');

$c=$smt->customerInfoByID($s['customer_id']);
$base=$smt->base_info_by_id($c['base_id']);
$mpo=$smt->userInfoByID($base['mpo_id']);
$user=$smt->userInfoByID($s['createdBy']);
$units=$smt->getAllUnit();
if(empty($mpo)){
    $mpo=[
        'name'=>'N/A',
        'mobile'=>'N/A'
    ];
}
$details=$db->selectAll('sale_products','where sale_id='.$id);
$product_ids=[];
foreach($details as $d){
    $product_ids[$d['product_id']]=$d['product_id'];
}
$products=$db->selectAllByID('products','id',$product_ids);
foreach($details as $k=>$d){
    $details[$k]['product']=$products[$d['product_id']];
}
//echo $general->make_date($s['createdOn'],'time');exit;
$due_data=$acc->customer_due_details($s['customer_id']);
//$general->printArray($mpo);exit;
$pay_type=$db->get_rowData('pay_types','id',$s['pay_type']);
$s['pay_type_name']=$pay_type['name'];
$challan=0;
if(isset($_GET['challan'])){
    $challan=$_GET['challan'];
}
$data=[
    'sale'      => $s,
    'customer'  => $c,
    'details'   => $details,
    'base'      => $base,
    'mpo'       => $mpo,
    'user'      => $user,
    'units'     => $units,
    'challan'   => $challan,
];
$format=PDF_PAGE_FORMAT;
$orientation='P';
// if(PROJECT=='project_4'||PROJECT=='local'){
//     $format=[210, 148];
//     $data['custom_size']=$format;
//     $orientation='L';
// }
$pdf = new MY_PDF($orientation, PDF_UNIT, $format, true, 'UTF-8', false);

$pdf->pdf_init($data,$general);
//$pdf->MultiCell(104,10,'abar jigai abar jigai ',1,'R',false,0,10,10);

$with_tp=1;
if(isset($_GET['without_tp'])){
    $with_tp=0;
}
$base_data=$general->getJsonFromString($base['data']);
if(isset($base_data['invoice_print_type'])&&$base_data['invoice_print_type']=='without_tp'){
    $with_tp=0;
}
$y=$pdf->sale_header();
$y=$pdf->sale_product($y,$with_tp);
$y=$pdf->sale_footer($y);


$pdf_name='sale_'.$id.TIME.'.pdf';
//$pdf->Output(ROOT_DIR.'/print_file/'.$pdf_name, 'F');
$pdf->Output($pdf_name, 'I');