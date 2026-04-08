<?php
$parent=$_POST['moduleOrder'];
$recordsArray=explode(',',$_POST['ord']);
$cOrder             = 1;
foreach ($recordsArray as $rv) {
    $data=array(
        'sequence'=>$cOrder
    );
    $where=array(
        'id'=>$rv
    );
    $update=$db->update('module',$data,$where);
    $cOrder++;
}