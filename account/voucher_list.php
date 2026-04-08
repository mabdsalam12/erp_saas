<?php
$chart_of_accounts=$db->selectAll('a_charts_accounts','where isActive=1 order by code','id,title,code');
$chart_of_account_wise_ledger=[];
$ledgers  = $db->selectAll('a_ledgers','where isActive=1 order by code','id,title,code,charts_accounts_id');
if($ledgers){
    foreach($ledgers as $l){
        $l['title']=$l['code'].' '.$l['title'];
        $chart_of_account_wise_ledger[$l['charts_accounts_id']][]=$l;
    }
}
if($chart_of_accounts){
    foreach($chart_of_accounts as $k=>$cot){
        $chart_of_accounts[$k]['title']=$cot['code'].' '.$cot['title'];
    }
}
$dateTypes=[
    [
        'id'=>0,
        'title'=>'Transaction Date'
    ],
    [
        'id'=>1,
        'title'=>'Entry Date'
    ]
];

$general->pageHeader($rModule['title']);
?>
<div class="row">
    <div class="col-sm-12" id="message_show_box"></div>
    <div class="col-sm-12">
        <div class="white-box">
            <div class="row">
                <?php
                    $general->inputBoxSelectForReport($dateTypes,'Date Type','date_type','id','title','','','',false);
                    $general->inputBoxTextForReport('dRange','Date',className:'daterangepickerMulti form-control');
                    $general->inputBoxSelectForReport($chart_of_accounts,'Chart of Account','charts_accounts_id','id','title','select2 form-control');
                    $general->inputBoxSelectForReport([],'Ledger','ledger_id','id','title');
                    $heads=$db->allUsers('order by username asc');
                    $general->inputBoxSelectForReport($heads,'User','user_id','id','name');
                    $types = $acc->all_voucher_type();
                    $general->inputBoxSelectForReport($types,'Type','type','id','title');
                    $general->inputBoxSelectForReport(
                        [
                            ['id'=>'0','title'=>'Details'],
                            ['id'=>'1','title'=>'Summary']
                        ],
                        'Report Type',
                        'report_type',
                        'id',
                        'title',
                        needFirstOption:false
                    );
                ?>
              
                <div class="col-md-2">
                    <h5 class="box-title">Search</h5>
                    <input type="submit" value="Search"class="btn btn-success" name="s" onclick="voucher_list(0);">
                </div>
                <div class="col-sm-12 col-lg-12"><?php show_msg();?></div>
                <div class="col-sm-12 col-lg-12" id="trDetailsArea"></div>
            </div>
        </div>
    </div>
</div>
<script>
<?= 'var chart_of_account_wise_ledger='.json_encode($chart_of_account_wise_ledger).';'?>
$(document).on('change','#charts_accounts_id',function(){
    let id = this.value;
    $('#ledger_id').html('<option value="">Select</option>');
    if(typeof(chart_of_account_wise_ledger[id])!=='undefined'){
        $.each(chart_of_account_wise_ledger[id],function(a,b){
            $('#ledger_id').append('<option value="'+b.id+'">'+b.title+'</option>');
        });
    }
    select2Call();
});
    
</script>
<script>
    function voucher_list(){
        let user_id = parse_int($('#user_id').val());
        const date_type = parse_int($('#date_type').val());
        let type = parse_int($('#type').val());
        let ledger_id = parse_int($('#ledger_id').val());
        let report_type = parse_int($('#report_type').val());
        ajax_report_request({voucher_list:1,ledger_id:ledger_id,date_type:date_type,user_id:user_id,type:type,report_type:report_type},'trDetailsArea');
    }</script>
