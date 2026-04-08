<?php
    $pdID=intval($_GET['pdID']);
    $data = array($pUrl=>$rModule['title']);
    $general->pageHeader($rModule['title'].' Details',$data);

    $option=[
        1=>['v'=>PROFIT_FOR_DPS,'t'=>'DPS'],
        2=>['v'=>PROFIT_FOR_FDR,'t'=>'FDR'],
        3=>['v'=>PROFIT_FOR_MAIN_ACCOUNT,'t'=>'Main account'],
        4=>['v'=>PROFIT_FOR_OWNER,'t'=>'owners'],
    ];
?>
<div class="row">
    <div class="col-sm-12">
        <div class="white-box">
            <div class="row">
                <div class="col-sm-12 col-lg-12 padding-left-0">
                    <div class="col-md-2">
                        <input type="hidden" id="pdID"  value="<?php echo $pdID ?>">
                        <h5 class="box-title">Type</h5>
                        <select  id="type" class="form-control select2">

                            <option value="">All</option>
                            <?php
                                foreach($option as $o){
                                ?><option value="<?php echo $o['v'];?>"><?php echo $o['t'];?></option><?php
                                }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <h5 class="box-title">Search</h5>
                        <a href="javascript:void()" class="btn btn-success" onclick="profitDistributeDetailsList()">Search</a>
                    </div>

                    <script>
                        $(document).ready(function(){profitDistributeDetailsList();});
                        function profitDistributeDetailsList(){
                            $('#reportArea').html(loadingImage);
                            $('#total').html('');
                            $('#reportArea').html('');
                            var pdID     = parse_int($('#pdID').val());
                            var type    = parse_int($('#type').val());
                            $.post(ajUrl,{profitDistributeDetailsList:1,pdID:pdID,type:type},function(data){
                                if(data.status==1){
                                    $('#reportArea').html(data.html);
                                    var details=data.details;
                                    $('#total').html(details.total);
                                  /*  $('#pa').html(details.pa);
                                    $('#ta').html(details.ta);
                                    $('#t').html(details.t);*/
                                    $('#sector').html(details.sector);
                                }
                                swMessageFromJs(data.m);
                            }); 
                        }
                    </script>
                </div>
                <div class="col-sm-12 col-lg-12  " id="total">
                    <!--<table class="table table-bordered">
                        <tr>
                            <td>Total Amount</td>
                            <td id='ma'></td>
                            <td>Total Profit</td>
                            <td id='pa'></td>
                        </tr>
                        <tr>
                            <td>Total Transfer</td>
                            <td id='ta'></td>
                            <td>Total TProfit</td>
                            <td id='t'></td>
                        </tr>
                    </table>-->
                
                </div>
                <div class="col-sm-12 col-lg-12" id='sector'>
                </div>
                <div class="col-sm-12 col-lg-12" id="reportArea" style="overflow: auto"></div>
            </div>
        </div>
    </div>
    </div>