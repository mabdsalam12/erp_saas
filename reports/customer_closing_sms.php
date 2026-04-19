<?php
    include_once ROOT_DIR.'/class/sms/Customer_closing_sms.php';
    $cls=new Customer_closing_sms($acc);
    if(isset($_GET['add'])){
        $data = array($pUrl=>$rModule['name'],1=>'Add');
        $general->pageHeader('Add '.$rModule['name'],$data);

        if(isset($_POST['add'])){
            try{
                $cls->create($_POST['name'],$_POST['from'],$_POST['to']);
                setMessage(1,'Customer closing SMS record created successfully');
                $general->redirect($pUrl);
            }
            catch(Exception $e){
                setMessage(1,$e->getMessage());
            }
        }
    ?>
    <div class="row"><div class="col-lg-12"><?php show_msg();?></div></div>
    <div class="row">
        <div class="col-sm-12">
            <div class="white-box border-box">
                <div class="row">
                    <div class="col-md-12">
                        <form method="post" action="">
                            <div class="row">
                                <div class="col-xs-6 col-sm-4">
                                    <?php $general->inputBoxText('name','Name',@$_POST['name']);?>
                                    <?php $general->inputBoxText('from','Start date',@$_POST['start'],'','daterangepicker_e');?>
                                    <?php $general->inputBoxText('to','End date',@$_POST['end'],'','daterangepicker_e');?>
                                    <?php echo $general->addBtn();?>
                                </div>

                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
    }
    elseif(isset($_GET['details'])){
        $id=intval($_GET['details']);
        $data = array($pUrl=>$rModule['name'],1=>'Details');
        $general->pageHeader('Customer closing SMS Details',$data);  
        try{
            $details=$cls->details($id);
        }
        catch(Exception $e){
            $general->redirect($pUrl,1,$e->getMessage());
        }
        $bases=$db->selectAll('base','where status=1');
        $general->arrayIndexChange($bases,'id');
    ?>
    <div class="white-box border-box">
    <div class="row"><div class="col-lg-12"><?php show_msg();?></div></div>
    <div class="row">
        <div class="col-sm-12">
            <div class="white-box border-box">
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="m-t-20">Base</h5>
                            <select class="select2 m-b-10 select2-multiple" id="base" multiple="multiple" data-placeholder="Choose base">
                                <?php
                                    foreach($bases as $b){
                                        ?>
                                        <option value="<?php echo $b['id'];?>"><?php echo $b['title'];?></option>
                                        <?php
                                    }
                                ?>
                            </select>
                    </div>
                </div>
            </div>
        </div>
        <script type="text/javascript">

            $(document).on('change','#base',function(){
                filterCustomerDetails();
            });
            $(document).on('click','#sendButton',function(){
                sendCustomerClosingSMS();
            });
            function filterCustomerDetails(){
                const bases=$('#base').val();
                console.log(bases);
                if(bases.length>0){
                    $('.singleRow').hide();
                    bases.forEach(function(base_id){
                        $('.base'+base_id).show();
                    });
                }
                else{
                    $('.singleRow').show();
                }
            }
            function sendCustomerClosingSMS(){
                const bases=$('#base').val();
                let selectedCustomers={};
                $('input[name="select_customer[]"]:checked').each(function(){
                    selectedCustomers[$(this).val()]=1;
                });
                if(Object.keys(selectedCustomers).length==0){
                    alert('Please select at least one customer to send SMS');
                    return;
                }
                if(confirm('Are you sure to send SMS?')){
                    $.ajax({
                        url:ajUrl,
                        type:'post',
                        data:{
                            send_customer_closing_sms:1,
                            id:<?=$id?>,
                            bases:bases,
                            customers:JSON.stringify(selectedCustomers)
                        },
                        success:function(data){
                            if(data.status==1){
                                alert('SMS sent successfully');
                                $('.sms_checkbox').hide();
                            }
                            else{
                                alert('Error sending SMS:');
                            }
                        }
                    });
                }
            }
        </script>
        <div class="col-sm-12 col-lg-12" style="overflow: auto;">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Select</th>
                        <th>Base</th>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Opening balance</th>
                        <th>Sale</th>
                        <th>Collection</th>
                        <th>Collection discount</th>
                        <th>Return</th>
                        <th>Closing balance</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if(!empty($details)){
                        $serial=1;
                        foreach($details['details'] as $d){
                            $base=$bases[$d['base_id']] ?? ['title'=>'N/A'];
                            $checked=$d['getClosingSMS'];
                            ?>
                            <tr id="c_<?=$d['id']?>" class="singleRow base<?=$d['base_id']?>">
                                <td class="serial"><?php echo $serial++;?></td>
                                <td>
                            <?php
                                if($details['record']['sms_send']==0){
                            ?>
                                    <input class="sms_checkbox" type="checkbox" name="select_customer[]" <?=$general->checked($checked)?> value="<?php echo $d['id'];?>">
                            <?php
                                }
                            ?>
                                </td>
                                <td><?php echo $base['title'];?></td>
                                <td><?php echo $d['code'];?></td>
                                <td><?php echo $d['name'];?></td>
                                <td style="text-align:right;"><?php echo $general->numberFormat($d['opening_balance']);?></td>
                                <td style="text-align:right;"><?php echo $general->numberFormat($d['sale']);?></td>
                                <td style="text-align:right;"><?php echo $general->numberFormat($d['collection']);?></td>
                                <td style="text-align:right;"><?php echo $general->numberFormat($d['collection_discount']);?></td>
                                <td style="text-align:right;"><?php echo $general->numberFormat($d['return']);?></td>
                                <td style="text-align:right;"><?php echo $general->numberFormat($d['closing_balance']);?></td>
                            </tr>
                            <?php
                        }
                    }
                    ?>
                </tbody>
            </table>
                    <button class="btn btn-success sms_details" id="sendButton">Send SMS</button>
        </div>
    </div>
    </div>
    <?php
    }
    else{
        $data = array($pUrl=>$rModule['name']);
        $general->pageHeader($rModule['name'],$data,$general->addBtnHtml($pUrl));  
    ?>
    <div class="col-sm-12">
        <div class="white-box border-box">
            <?php show_msg();?>
            <div class="row">
                <div class="col-sm-12 col-lg-12 padding-left-0">
                    

                    <div class="col-md-2">
                        <h5 class="box-title">Search</h5>
                        <input type="submit" value="Search" class="btn btn-success" onclick="customer_closing_sms_list()" name="s">
                    </div>
                </div>
                <div class="col-sm-12 col-lg-12" id="reportArea" style="overflow: auto;"></div>

            </div>
        </div>
    </div>
    <script type="">
        $(document).on('click','.sms_details',function(){
            let id=$(this).data('id');
            let request={
                customer_closing_sms_details:1,
                id:id
            };
            $.ajax({
                url:ajUrl,
                type:'post',
                data:request,
                success:function(data){
                    if(data.status==1){
                        $('#sms_details_modal_body').html(data.html);
                        $('#sms_details_modal').modal('show');
                    }
                }
            });
        });
        $(document).ready(function(){
            customer_closing_sms_list();
        });
    function customer_closing_sms_list(){
        let request={
            customer_closing_sms_list:1
        };
        ajax_report_request(request,'reportArea');
    }
    </script>
    <?php
    }