<?php
    $general->pageHeader($rModule['title']);

    //$customers = $db->selectAll('customer','where isActive=1','id,name,code,due_day,base_id');

    $customer_data=$smt->get_base_wise_all_customer();
    $base_customers=$customer_data['base_customers'];
    $customers=$customer_data['customers'];

    $base = $db->selectAll('base','where status=1 order by code','id,title');
    $customer_category = $db->selectAll('customer_category','where isActive=1','id,title');
    $general->arrayIndexChange($base,'id');
    $bazar=$db->selectAll('bazar','','id,title,base_id');
    $bazar_wise_bazars=[];
    if(!empty($bazar)){
        foreach($bazar as $d){
            $bazar_wise_bazars[$d['base_id']][]=$d;
        }
    }
    

    $dRange=date('d-m-Y').' to '.date('d-m-Y',strtotime('+30 day'));
    if(isset($_GET['dRange'])){$dRange=$_GET['dRange'];}
?>
<script type="text/javascript">
    <?php  echo 'var base_customers='.json_encode($base_customers).';'; ?>
    let bazar_wise_bazars=<?=json_encode($bazar_wise_bazars);?>;
    $(document).on('change','#base_id',function(){base_wise_customer(this.value,'','','All')});
    $(document).on('change','#base_id',function(){base_wise_bazar(this.value,'All');});
</script>
<div class="white-box border-box">
    <div class="row">
        <div class="col-md-12">
            <div class="col-sm-12 col-md-12 col-lg-12">
                <div class="col-md-2">
                    <h5 class="box-title">Date</h5>
                    <input type="text" name="dRange" id="dRange" class="daterangepickerMulti form-control" value="<?= $dRange; ?>">
                </div>
                
                <?php

                $list_type=[
                    ['id'=>'2','title'=>'Without Zero balance'],
                    ['id'=>'0','title'=>'Without Zero transaction'],
                    ['id'=>'1','title'=>'With Zero']
                ];
                $column_hide=[
                    ['id'=>'0','title'=>'Without Zero'],
                    ['id'=>'1','title'=>'With Zero']
                ];

                $general->inputBoxSelectForReport([['id'=>'s','title'=>'Summarized'],['id'=>'t','title'=>'Original']],'Type Report','type','id','title',needFirstOption:false);
                $general->inputBoxSelectForReport($base,'Base','base_id','id','title');
                $general->inputBoxSelectForReport($bazar, 'bazar','bazar_id','id','title');
                $general->inputBoxSelectForReport($customers, 'Customer','customer_id','id','name',@$_GET['customer_id']);
                $general->inputBoxSelectForReport($customer_category,'Category','customer_category_id','id','title');
                $general->inputBoxSelectForReport($list_type,'Type','type_zero','id','title',needFirstOption:false);
                $general->inputBoxSelectForReport($column_hide,'Column','column_zero','id','title',needFirstOption:false);
                ?>
              
                <div class="col-md-2">
                    <h5 class="box-title">Search</h5>
                    <button class="btn btn-success" onclick="customer_statement()">Search</button>
                </div>
            </div> 
            <?php

                if(isset($_GET['cID'])){
                ?>
                <script>
                    $(document).ready(function(){
                        $(document).ready(function(){
                                const report_data = JSON.parse(localStorage.getItem('customer_statement'));
                                if(report_data&&report_data.hasOwnProperty('dRange')){
                                    $('#dRange').val(report_data.dRange);
                                    $('#customer_id').val(report_data.customer_id);
                                    $('#base_id').val(report_data.base_id);
                                    $('#type').val(report_data.type);
                                    $('#type_zero').val(report_data.type_zero);
                                    $('#customer_category_id').val(report_data.customer_category_id);
                                    $('#bazar_id').val(report_data.bazar_id);
                                    select2Call();
                                }
                                customer_statement();
                            });
                    })

                </script>
                <?php
                }
            ?>
        </div>

        <div class="col-sm-12 col-lg-12"><?php show_msg();?></div>
        <div class="col-sm-12 col-lg-12" id="reportArea" style="overflow: auto;"></div>
    </div>
</div>