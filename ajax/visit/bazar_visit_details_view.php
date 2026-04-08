<?php
  $id = intval($_POST['id']);
    $vl = $db->get_rowData('area_visit','id',$id);
    if(empty($vl)){$error=fl();setMessage(63,'Area visit');}
    else{
        $area  = $db->get_rowData('area','id',$vl['area_id']);
       
        $vl['entry_time'] = $general->make_date($vl['entry_time'],'time');
        $vl['note'] = $general->content_show($vl['note']);
        $gAr['vl']=$vl;
        $gAr['area']=$area;
        $jArray['html']     = $general->fileToVariable(__DIR__.'/area_visit_details_view.phtml');
        $jArray['status']=1;
    }
?>
