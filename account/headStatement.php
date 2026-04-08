 <?php
        $general->pageHeader($rModule['title']);
        $ledgers=$acc->getAllHead();

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
    <div class="row">
        <div class="col-sm-12">
            <div class="white-box">
                <div class="row">
                    <div class="col-sm-12 col-lg-12">
                        <div class="col-md-2">
                            <h5 class="box-title">Date</h5>
                            <input type="text" name="dRange" id="dRange" class="daterangepickerMulti form-control" value="">
                        </div>
                        <?php
                        $general->inputBoxSelectForReport($chart_of_accounts,'Chart of account','chart_of_account_id','id','title','');
                        $general->inputBoxSelectForReport($ledgers,'Ledger','ledger_id','id','title',@$_GET['ledger_id']);
                        $general->inputBoxSelectForReport([['i'=>3,'t'=>'Type 3'],['i'=>0,'t'=>'Type 1'],['i'=>1,'t'=>'Type 2'],['i'=>2,'t'=>'Summarize']],'Type','type','i','t',needFirstOption:false);
                        ?>
                        
                        <div class="col-md-2">
                            <h5 class="box-title">Search </h5>
                            <input type="submit" value="Search" class="btn btn-success" name="s" onclick="headStatement();">
                        </div>
                       
                            <script>
                            $(document).ready(function(){
                                const report_data = JSON.parse(localStorage.getItem('headStatement'));
                                if(report_data){
                                    if(report_data.hasOwnProperty('dRange')){
                                        $('#dRange').val(report_data.dRange);
                                        if(report_data.ledger_id>0){
                                            $('#ledger_id').val(report_data.ledger_id);
                                        }
                                        $('#type').val(report_data.type);
                                        select2Call();
                                    }
                                }
                                headStatement();
                            });
                            </script>
                          
                    </div>
                    <div class="col-sm-12 col-lg-12"><?php show_msg();?></div>
                    <div class="col-sm-12 col-lg-12" id="reportArea"></div>
                </div>
            </div>
        </div>
    </div>
    <?php
    $acc->voucher_details_html();