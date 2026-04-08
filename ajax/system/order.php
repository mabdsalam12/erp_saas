<?php
    $cOrder             = 1;
    $no                 = intval($_POST['trg']);
    $array              = $general->tableOrdArray($no);
    $t_id               = $array['id'];
    $t_ord              = $array['order'];
    $table              = $general->table(intval(($_POST['actn'])));
    $updateRecordsArray = explode(',',$_POST['recordsArray']);
    foreach ($updateRecordsArray as $rv) {
        $data=array(
            $t_ord=>$cOrder
        );
        $where=array(
            $t_id=>$rv
        );
        $update=$db->update($table,$data,$where);
        $cOrder++;
    }

?>
