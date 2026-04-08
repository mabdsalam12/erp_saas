<?php
    $pur=$db->getRowData($general->table(11),'where purID='.$purID);
    if(!empty($pur)){
        $date=$db->getRowData($general->table(11),'where purID='.$purID);   
        $sup=$smt->supplierInfoByID($pur['supID']); 
        $serial=0;   
?>
    <!DOCTYPE html>
    <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?php echo $sup['supName'];?> Purchase</title>
            <style type="text/css">
                .right{
                    text-align: right;
                }
            </style>
        </head>
        <body style="font-family: tahoma;color: #393939;">
            <div style="width:900px;margin:30px auto">
                <div style="display: flex;justify-content: space-between;">
                    <div style="display: flex;align-items:flex-start;">
                        <div>
                            <h1 style="font-size: 45px;margin:0"><?php echo $sup['supName'];?></h1>
                        </div>
                    </div>
                    <div>
                        <h3 style="margin: 7px auto;">Purchase</h3>
                        <p style="font-size: 15px;">Date : <span style="font-weight: 600;"><?php echo $general->make_date($date['purDate']);?></span></p>
                    </div>
                </div>
                <table border="1" cellspacing="0" style="width:100%;">
                    <thead>
                        <tr>
                            <td style="">Sl</td>
                            <td style="">Supplier</td>
                            <td style="">Sup Inv No</td>
                            <td style="">Date</td>
                            <td style="">Subtotal</td>
                            <td style="">Discount</td>
                            <td style="">Net Payable</td>                    
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?php echo $serial++;?></td>
                            <td><?php echo  $sup['supName']?></td>
                            <td class="right"><?php echo  $pur['supInvNo'];?></td>
                            <td class="right"><?php echo $general->make_date($pur['purDate']);?></td>
                            <td class="right"><?php echo $general->numberFormat($pur['subTotal']);?></td>
                            <td class="right"><?php echo $general->numberFormat($pur['discount']);?></td>
                            <td class="right"><?php echo $general->numberFormat($pur['netTotal']);?></td>

                        </tr>
                        <tr style="font-weight: bold;">
                            <td></td>
                            <td>Total</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td class="right"><?php echo $general->numberFormat($pur['subTotal']);?></td>
                            <td class="right"><?php echo $general->numberFormat($pur['discount']);?></td>
                            <td class="right"><?php echo $general->numberFormat($pur['netTotal']);?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </body>
    </html>
<?php
    }
    else{
        echo 'Invalid purchase print request';
    }
?>