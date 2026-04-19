<?php
    $general->pageHeader($rModule['name']);
    $date=date('d-m-Y');
    $company_data = $db->get_company_data();
    $base = $db->selectAll('base');
    $base=[0=>['id'=>0,'title'=>'General'],...$base];
    $for_voucher_entry_ledgers = $company_data['for_voucher_entry_ledgers']??[0=>0];
    $ledgers  = $db->selectAll('a_ledgers','where id in('.implode(',',$for_voucher_entry_ledgers).') or type='.H_TYPE_CUSTOM,'id,title,code,charts_accounts_id');
    $types = [['id'=>DEBIT,'title'=>'Debit'],['id'=>CREDIT,'title'=>'Credit']];
    $chart_of_accounts=$db->selectAll('a_charts_accounts','where isActive=1','id,title,code');
    $chart_of_account_wise_ledger=[];
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


?>
<script>
<?= 'var chart_of_account_wise_ledger='.json_encode($chart_of_account_wise_ledger).';'?>
$(document).on('change','#chart_of_account_id',function(){
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
<div class="col-sm-12">
    <div class="white-box border-box">
        <div><?php show_msg();?></div> 
        <div class="row">
            <div class="col col-md-6">
                <?php
                    $general->inputBoxText('date','Date',$date,'','daterangepicker');
                    $general->inputBoxSelect($base,'Base','base_id','id','title',haveSelect:'n');
                    echo '<hr>' ;
                    $general->inputBoxSelect($types,'Type','type','id','title',inputClassName:'form-control');
                    $general->inputBoxSelect($chart_of_accounts,'Chart of account','chart_of_account_id','id','title');
                    $general->inputBoxSelect([],'Ledger','ledger_id','id','title');
                    $general->inputBoxTextArea('note','Note');
                    $general->inputBoxText('amount','Amount');
                    
                ?>
            </div>     
        </div>     
        <div class="row">
            <div class="clearfix visible-xs"></div>
            <div class="col-sm-12">
                <button onclick="ledgerAddToCart()" class="p-2 m-2 btn btn-info waves-effect waves-light pull-right m-t-10">Add to cart</button>
            </div>
        </div>
        <div class="row">
            <div style="display: none;">
                <table>
                    <tbody>
                        <tr id="ledger_entry">
                            <td class="autoSerial"></td>
                            <td>
                                <select onchange="change_type(this)"  class="form-conto type">
                                    <option value="<?=DEBIT?>">Debit</option>  
                                    <option value="<?=CREDIT?>">Credit</option>  
                                </select>
                            </td>
                            <td>
                                <input type="hidden" class="chart_of_account_id" value="">
                                <span class="chart_of_account_title"></span>
                            </td>
                            <td>
                                <input type="hidden" class="ledger_id" value="">
                                <span class="ledger_title"></span>
                            </td>
                            <td spellcheck="false" contenteditable="true" class="note"  style="background-color: #bdbdb9;"></td>
                            <td class="amount_td debit"></td>
                            <td class="amount_td credit"></td>
                            
                            <td class="amount_td"><button class="btn btn-danger remove">X</button></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="col-md-12" style="overflow:auto">
                <table class="table table-border">
                    <thead>
                    <tr>
                        <th  style="width: 2%;">#</th>
                        <th>Type</th>
                        <th>Chart of account</th>
                        <th>Ledger</th>
                        <th>Note</th>
                        <th  class="amount_td ">Debit</th>
                        <th  class="amount_td">Credit</th>
                        <th style="width: 1%;" class="amount_td">X</th>
                    </tr>
                    <thead>
                    <tbody id="table_body">

                    </tbody>
                    <tfoot>
                        <tr>
                            <td></td>
                            <td>Total</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td id="total_debit" class="amount_td"></td>
                            <td id="total_credit" class="amount_td"></td>
                            
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

        </div>
        <div class="row">
            <div class="clearfix visible-xs"></div>
            <div class="col-sm-12">
                <button onclick="voucher_entry()" class="p-2 m-2 btn btn-info waves-effect waves-light pull-right m-t-10 voucher_entry">Submit</button>
            </div>
        </div>
    </div>
</div>
<script>
const DEBIT = <?=DEBIT?>;
const CREDIT = <?=CREDIT?>;
function ledgerAddToCart(){
    let type = parse_int($('#type').val());
    let chart_of_account_id = parse_int($('#chart_of_account_id').val());
    let chart_of_account_title = $('#chart_of_account_id option:selected').text();
    let ledger_id = parse_int($('#ledger_id').val());
    let ledger_title = $('#ledger_id option:selected').text();
    let amount = parse_float($('#amount').val());
    let note = $('#note').val();

    
    
    if(type<1){swMessage('Select a type');return 0;}
    if(chart_of_account_id<1){swMessage('Select a chart of account');return 0;}
    if(ledger_id<1){swMessage('Select a ledger');return 0;}
    if(amount<=0){swMessage('Invalid amount');return 0;}

    let id='VE_'+ledger_id+'_'+autoInc;autoInc++;

    $('#ledger_entry .chart_of_account_id').val(chart_of_account_id);
    $('#ledger_entry .chart_of_account_title').html(chart_of_account_title);

    $('#ledger_entry .ledger_id').val(ledger_id);
    $('#ledger_entry .ledger_title').html(ledger_title);
    
    $('#ledger_entry .note').html(note);
    (type==DEBIT)?$('#ledger_entry .debit').html(amount) : $('#ledger_entry .credit').html(amount);
    (type!=DEBIT)?$('#ledger_entry .debit').html('') : $('#ledger_entry .credit').html('');
    $('#ledger_entry .remove').attr('onclick','remove_row_by_id(\''+id+'\');voucher_entry_total();')
    let ledger_entry = $('#ledger_entry').html();
    $('#table_body').append('<tr id="'+id+'">'+ledger_entry+'</tr>');
    let tr_sl_start=1;$('#table_body .autoSerial').each(function(){$(this).html(tr_sl_start);tr_sl_start++;});
    $('#'+id+'  .type').val(type);

    $('#type').val('');
    $('#chart_of_account_id').val('');
    $('#ledger_id').val('');
    //$('#amount').val('');
    //$('#note').val('');
    select2Call();
    voucher_entry_total();
}
function voucher_entry_total(){
    let total_debit=0;$('#table_body .debit').each(function(){total_debit+=parse_float($(this).html());});
    let total_credit=0;$('#table_body .credit').each(function(){total_credit+=parse_float($(this).html());});
    $('#total_debit').html(total_debit.toFixed(2))
    $('#total_credit').html(total_credit.toFixed(2))
}
function change_type(type_object){
    let type = parse_int($(type_object).val());
    let id=$(type_object).closest('tr').attr('id');
    console.log('id',id);
    
    let amount = parse_float($('#'+id+' .debit').html())>0 ? parse_float($('#'+id+' .debit').html()) : parse_float($('#'+id+' .credit').html());
    (type==1)?$('#'+id+' .debit').html(amount) : $('#'+id+' .credit').html(amount);
    (type!=1)?$('#'+id+' .debit').html('') : $('#'+id+' .credit').html('');
    voucher_entry_total();
}
function voucher_entry(){
    buttonLoading('voucher_entry');
    let error=0;
    let total_debit=0;
    let total_credit=0;
    let entrees={};
    let serial = 0;
    $('#table_body .ledger_id').each(function(a,b){
        let id = $(this).closest('tr').attr('id');
        let type = parse_int($('#'+id+' .type').val());
        let chart_of_account_id =parse_int($('#'+id+' .chart_of_account_id').val());
        let ledger_id =parse_int($('#'+id+' .ledger_id').val());
        let amount = (type==DEBIT)?parse_float($('#'+id+' .debit').html()) : parse_float($('#'+id+' .credit').html());
        type==DEBIT?total_debit+=amount : total_credit+=amount;
        let note = $('#'+id+' .note').html();

        if(type<1){swMessage('Select a type');error=1; return ;}
        if(chart_of_account_id<1){swMessage('Select a ledger');error=1;return ;}
        if(ledger_id<1){swMessage('Select a ledger');error=1;return ;}
        if(amount<=0){swMessage('Invalid amount');error=1;return ;}

        entrees[serial++]={
            type:type,
            chart_of_account_id:chart_of_account_id,
            ledger_id:ledger_id,
            amount:amount,
            note:note,
        };
    });
    if(error==0&&serial==0){swMessage('select a ledger');error=1}
    if(error==0&&total_debit!=total_credit){swMessage('Debit and Credit do not match');error=1;}
    if(error==1){button_loading_destroy('voucher_entry','Submit'); return;}
    let date = $('#date').val();
    let base_id = parse_int($('#base_id').val());
    $.ajax({
        type:'post',
        url:ajUrl,
        data:{voucher_entry:1,entrees:entrees,date:date,base_id:base_id},
        success:function(data){
            if(typeof(data.status)  !== "undefined"){ 
                if(data.status==1){
                    $('#table_body').html('');
                    voucher_entry_total();

                }
                swMessageFromJs(data.m);
            }
            else{
                swMessage(AJAX_ERROR_MESSAGE); 
            }
            button_loading_destroy('voucher_entry','Submit');
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) { 
            button_loading_destroy('voucher_entry','Submit');
            swMessage(AJAX_ERROR_MESSAGE); 
        }
    });

}


</script>