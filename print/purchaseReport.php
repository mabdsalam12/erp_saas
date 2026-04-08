<?php
    $pur=$db->get_rowData($general->table(11),'purID',$purID);
    $supID=$pur['supID'];
    $suppliersname=$smt->supplierInfoByID($supID);
    $purProducts=$db->selectAll($general->table(12),'where purID='.$purID);
    $units=$db->selectAll($general->table(56),'where isActive=1 order by unTitle asc');
    $general->arrayIndexChange($units,'unID');
    $pID = [];
    foreach($purProducts as $p){
        $pID[]=$p['pID'];
    }
    $products=$db->selectAll($general->table(104),'where pID in('.implode(',',$pID).') order by pTitle asc');
    $general->arrayIndexChange($products,'pID');
    $unID=[];
    foreach($products as $u){
        $unID[]=$u['unID'];
    }
    $units=$db->selectAll($general->table(56),'where unID in('.implode(',',$unID).') and isActive=1 order by unTitle asc');
    $general->arrayIndexChange($units,'unID');
?>

<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo $suppliersname['supName'];?> Purchase</title>
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
                        <h1 style="font-size: 45px;margin:0"><?php echo $suppliersname['supName'];?></h1>
                    </div>
                </div>
                <div>
                    <h3 style="margin: 7px auto;">Purchase</h3>
                    <p style="font-size: 15px;">Date : <span style="font-weight: 600;"><?php echo $general->make_date($pur['purDate']);?></span></p>
                </div>
            </div>
            <table border="1" cellspacing="0" style="width:100%;">
                <thead>
                    <tr>
                        <td style="">Sl</td>
                        <td style="">Product</td>
                        <td style="">Unit</td>
                        <td style="">Unit Price</td>
                        <td style="">Quantity</td>                   
                        <td style="">Total</td>                   
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <?php
                            $serial=0;
                            $total=0;
                            foreach($purProducts as $p){
                                $total+=$p['unitPrice']*$p['quantity'];
                            ?>
                            <td><?php echo $serial++;?></td>
                            <td><?php echo  $products[$p['pID']]['title']?></td>
                            <td class="right"><?php echo $units[$products[$p['pID']]['unID']]['unTitle'];?></td>
                            <td class="right"><?php echo $general->numberFormat($p['unitPrice']);?></td>
                            <td class="right"><?php echo $p['quantity'];?></td>
                            <td class="right"><?php echo $general->numberFormat($p['unitPrice']*$p['quantity']);?></td>
                        </tr>
                        <?php
                        }
                    ?>
                    <tr style="font-weight: bold;">
                        <td></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>Total</td>
                        <td class="right"><?php echo $general->numberFormat($total);?></td>
                    </tr>
                </tbody>
            </table>
        </div>


    </body>

</html>

