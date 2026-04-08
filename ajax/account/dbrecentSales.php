<?php
    $dRange=$_POST['dRange'];   
    $dRange = explode(' to ',$dRange);

    $from   = strtotime($dRange[0]);
    $to     = strtotime(trim($dRange[1]));
    $to=strtotime('+1 day',$to);
    $to=strtotime('-1 second',$to);

    $sales=$db->selectAll($general->table(29),'where createdOn BETWEEN '.$from.' and '.$to.' limit 10');   

    $jArray['html']='No Recent Sales.';
    $html='';
    $total=0;
    $status=' ';
    if(!empty($sales)){
        foreach($sales as $s){
            $total=$total+$s['diTotal'];
            $customer=$tkt->customerInfoByID($s['cID']);
            if($s['diStatus']==INVOICE_STATUS_ACTIVE){$status='SALE';}
            
            $html.= '<tr>
            <td class="txt-oflo">'.$customer['cName'].'</td>
            <td><span class="label label-megna label-rounded">'.$status.'</span> </td>
            <td class="txt-oflo">'.$general->make_date($s['createdOn']).'</td>
            <td><span class="text-success">'.number_format($s['diTotal'],2).'</span></td>
            </tr>';      
        }

        $jArray['html'] = $html;
        $jArray['status']=1;

    }  

    $jArray['status']=1;
    $jArray['total']=number_format($total,2);
    $jArray['m']=show_msg('y');
    $general->jsonHeader($jArray);


?>
