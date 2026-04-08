<?php
// require_once(ROOT_DIR.'/tcpdf/tcpdf.php');
include_once(__DIR__.'/pdf_functions_'.PROJECT.'.php');

$base=$smt->base_info_by_id($g['base_id']);
$user=$smt->userInfoByID($g['user_id']);
$details=$db->selectAll('gift_distribute_product','where gift_distribute_id='.$id);
$product_ids=[];
foreach($details as $d){
    $product_ids[$d['product_id']]=$d['product_id'];
}
$products=$db->selectAllByID('products','id',$product_ids);
foreach($details as $k=>$d){
    $details[$k]['product']=$products[$d['product_id']];
}
$data=[
    'g'         => $g,
    'details'   => $details,
    'base'      => $base,
    'user'       => $user,
];
$pdf = new MY_PDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->pdf_init($data,$general);
$with_tp=1;
$y=$pdf->gift_dist_header();
$y=$pdf->gift_dist_product($y,$with_tp);
$y=$pdf->gift_dist_footer($y);
$pdf_name='sale_'.$id.TIME.'.pdf';
//$pdf->Output(ROOT_DIR.'/print_file/'.$pdf_name, 'F');
$pdf->Output($pdf_name, 'I');