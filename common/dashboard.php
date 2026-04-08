<?php
$url    = "https://sms-service.xylub.com/?api=getBalance";
$data   = [
    "api_key"=>SMS_API_KEY,
];
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//set operation timeout
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
$result = curl_exec($ch);
curl_close($ch);
$sms_balance=0;
$bg_color='green';
$response=$general->getJsonFromString($result);
if(isset($response['status'])&&$response['status']==1){
    if($response['balance']<200){
        $bg_color='red';
    }
    $sms_balance=$general->numberFormat($response['balance']);
}
?>

<link href="<?php echo URL; ?>/plugins/bower_components/morrisjs/morris.css" rel="stylesheet">
<script src="<?php echo URL; ?>/plugins/bower_components/raphael/raphael-min.js"></script>
<script src="<?php echo URL; ?>/plugins/bower_components/morrisjs/morris.js?"></script>
<?php
    $general->pageHeader(l('dashboard'));
    $where = "WHERE for_home = 1 order by title asc";   
    $m = $db->selectAll('module',$where);        
?>
<div class='row'>
    <div class='col-md-12 col-sm-12 col-xs-12 mt-3'>
        <div class="col-md-2">
            <div class="white-box text-center" <?='style="background-color:'.$bg_color.';color:white;"'?>>
            <ul class="list-inline two-part">
                <li>SMS balance</li>
            </ul>
            <h3 class="box-title"><?=$sms_balance?></h3>
            </div>
        </div>
    </div>
</div>
<?php 
    $row = 1;     
    foreach($m as $a){
        $path = $a['slug']; 
        $color='';
        if(!empty($a['color'])){
            $color .= "style='background-color:#".$a['color'].";";

        }else{
            $color .= "style='background-color:red;";
        }
        if(!empty($a['text_color'])){
            $color .="color:#".$a['text_color'].";'";
        }else{
            $color .= "color:#ffffff;'";
        }
        if(!empty($a['icon'])){
            $icon = $a['icon'];
        }else{
            $icon = "icon-folder";
        }  



        if($row == 1){
        ?> <div class='row'>
            <div class='col-md-12 col-sm-12 col-xs-12 mt-3'><?php
                } 
                $row++;                    
            ?>
            <div class="col-md-2">
                <a href="?<?php echo MODULE_URL."=".$path;?>">
                    <div class="white-box text-center" <?php echo $color; ?>>
                        <ul class="list-inline two-part">
                            <li><i class="<?php echo $icon; ?>"></i></li>
                        </ul>
                        <h3 class="box-title"><?php echo $a["title"]; ?></h3>
                    </div>
                </a>
            </div>  
            <?php       
                if($row == 7){           
                ?>
            </div>
        </div>
        <?php
            $row=1;
        }      
    }             
    if($row >1){
    ?>
    </div>
    </div>
    <?php
        $row=1;
    }    
    $dRange=date('d-m-Y',strtotime('-7 day')).' to '.date('d-m-Y');
    
?> 
<div class="col-md-12 mt-4" style="display:none">
    <div class="white-box">
        <div class="row">
            <div class="col-12 col-sm-12 col-md-12 col-lg-3 col-xl-6 pt-3">
                <h3 class="box-heading">Sales and Purchase</h3>
            </div>                                                                                                                                    
            <div class="col-4 col-sm-4 col-md-4 col-lg-3 col-xl-2 pt-3">
                <input type="text" name="dRange" id="dRange" class="daterangepickerMulti form-control" value="<?php echo $dRange; ?>">
            </div>
            <div class="col-4 col-sm-4 col-md-4 col-lg-3 col-xl-2 pt-3">
                <input class="btn btn-info" value="Submit" onclick="saleAndPurchase()" name="bar" type="submit">
            </div>
        </div>
        <div class="col-md-12">
            <hr class="my-3">
            <ul class="list-inline text-right">
                <li>
                    <h5><i class="fa fa-circle m-r-5" style="color:#2CA02C"></i>Sale</h5>
                </li>
                <li>
                    <h5><i class="fa fa-circle m-r-5" style="color:#FFCD00"></i>Purchase</h5>
                </li>

            </ul>
            <div id="saleAndPurchase" style="height: 250px;" class="pb-5"></div>
        </div>
    </div>
</div>




<script>
     $(document).ready(function(){saleAndPurchase();});
    function saleAndPurchase(){
        $('#saleAndPurchase').html('');
        var dRange=$('#dRange').val();
        $.post(ajUrl,{saleAndPurchase:1,dRange:dRange},function(jData){
            if(jData.status==1){
                console.log(jData);
                Morris.Area({
                    element: 'saleAndPurchase',
                    data:jData.saleAndPurchase,
                    xkey: 'date',
                    ykeys:["s","p"],                                            
                    labels:["Sale",'Purchase'],                                           
                    pointSize: 3,
                    fillOpacity: 0,
                    xLabelAngle:0,
                    preUnits:'',
                    pointStrokeColors:["#2CA02C","#FFCD00"],                                            
                    behaveLikeLine: true,
                    //gridLineColor: 'rgba(120, 130, 140, 0.28)',
                    lineWidth: 2,
                    gridTextColor: '#96a2b4',
                    hideHover: 'auto',
                    lineColors:["#2CA02C","#FFCD00"],                                            
                    xLabelFormat: function (x) {
                        var IndexToMonth = [ "Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec" ];
                        var month = IndexToMonth[ x.getMonth() ];
                        var year = x.getFullYear();
                        var day = x.getDate();
                        return day+'-'+month+ '-' +year;
                    },
                    //resize: true
                });
            }


        });
    }
</script>