<?php
    $general->pageHeader($rModule['title']);
    $base = $db->selectAll('base');
    $toll_sale_type=[
        ['id'=>0,'title'=>'Without Toll product'],
        ['id'=>1,'title'=>'With Toll product']
    ];
    $toll_base_type=[
        ['id'=>0,'title'=>'Without toll base'],
        ['id'=>1,'title'=>'With toll base']
    ];
?>
<div class="row">
    <div class="col-sm-12">
        <div class="white-box">
            <div class="row">
                <div class="col-sm-12 col-lg-12">   
                    <div class="col-md-2">
                        <h5 class="box-title">Date</h5>
                        <input type="text" id="dRange" class="daterangepickerMulti form-control" value="">
                    </div>    
                    <div class="col-md-2">
                        <h5 class="box-title">TP</h5>
                        <select id="with_tp" class="col-md-8 form-control">
                            <option value="1">With TP</option>
                            <option value="2">Without TP</option>
                        </select>
                    </div>
                    <?php
                    $general->inputBoxSelectForReport($toll_sale_type,'Toll product type','toll_sale_type','id','title','','','',false);
                    
                    $general->inputBoxSelectForReport($toll_base_type,'Toll base type','toll_base_type','id','title','','','',false);
                    ?>
                    <div class="col-md-2">
                        <h5 class="box-title">Search</h5>
                        <button class="btn btn-success" onclick="sales_report()">Search</button>
                        <script type="text/javascript">
                           
                            $(document).ready(function(){
                                const report_data = JSON.parse(localStorage.getItem('sales_report'));
                                if(report_data){
                                    if(report_data.hasOwnProperty('dRange')){
                                        $('#dRange').val(report_data.dRange);
                                    }
                                    if(report_data.hasOwnProperty('with_tp')){
                                        $('#with_tp').val(report_data.with_tp);
                                    }
                                    select2Call();
                                }
                                sales_report();
                            });
                        </script>
                    </div>
                </div>
                <div class="col-sm-12 col-lg-12">
                    <?php
                        show_msg();
                    ?>
                </div>
                <div class="col-sm-12 col-lg-12" id="reportArea" style="overflow: auto;">
                </div>
            </div>
        </div>
    </div>
</div>
<?php
include_once ROOT_DIR.'/common/details_modal.php';