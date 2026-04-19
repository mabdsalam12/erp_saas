<?php
$pageTitle      = $rModule['name'];
$titleFieldName = 'Title';
$base = $db->selectAll('base','','id,title');
$general->arrayIndexChange($base);
$doctors = $db->selectAll('doctor','','id,base_id,name');
$general->arrayIndexChange($doctors);
$base_doctors=[];
if(!empty($doctors)){
    foreach($doctors as $d){
        $base_doctors[$d['base_id']][]=$d;
    }
}
$customers = $db->selectAll('customer','where isActive=1','id,name,code,due_day,base_id');
$general->arrayIndexChange($customers);
$base_customers = [];
if(!empty($customers)){
    foreach($customers as $k=>$p){
        $p['name']=$p['code'].' '.$p['name'];
        $customers[$k]['name']=$p['name'];

        $base_customers[$p['base_id']][]=$p;
    }
}

$months = [
    1 => "January",
    2 => "February",
    3 => "March",
    4 => "April",
    5 => "May",
    6 => "June",
    7 => "July",
    8 => "August",
    9 => "September",
    10 => "October",
    11 => "November",
    12 => "December"
];

// Generate year options
$currentYear = date("Y");
$startYear = $currentYear; 
$endYear = $currentYear + 15;  // 15 years in the future
$currentMonth = date('n');

?>
<script type="">
    var cID = <?=intval(@$_POST['cID'])?>;
    var doctor_id = <?=intval(@$_POST['doctor_id'])?>;
    <?php echo 'var base_doctors='.json_encode($base_doctors).';';?>
    <?php  echo 'var base_customers='.json_encode($base_customers).';'; ?>
    $(document).on('change','#base_id',function(){base_wise_doctor(this.value);base_wise_customer(this.value,'','','Select Custoer')});
    $(document).ready(function(){
        base_wise_doctor(<?=intval(@$_POST['base_id'])?>);    
        base_wise_customer(<?=intval(@$_POST['base_id'])?>,'','','Select Customer');    
    });
</script>
<?php 
if(isset($_GET['add'])){
    $data = array($pUrl=>$pageTitle,'1'=>'Add');

    $general->pageHeader('Add '.$pageTitle,$data);

    if(isset($_POST['add'])){
        $base_id = intval($_POST['base_id']);
        $doctor_id = intval($_POST['doctor_id']);
        $customer_id = intval($_POST['customer_id']);
        $contribute = intval($_POST['contribute']);

        $start_month=intval(@$_POST['start_month']);
        $start_year=intval(@$_POST['start_year']);

        $start_time = mktime(0, 0, 0, $start_month, 1, $start_year);

        $end_month=intval($_POST['end_month']);
        $end_year=intval($_POST['end_year']);
        $end_time = mktime(23, 59, 59, $end_month + 1, 0, $end_year);

        if($base_id<1){$error=fl(); setMessage(36,'Base');}
        elseif(!isset($base[$base_id])){$error=fl(); setMessage(63,'Base');}
        elseif($doctor_id<1){$error=fl(); setMessage(36,'Doctor');}
        elseif(!isset($doctors[$doctor_id])){$error=fl(); setMessage(63,'Doctor');}
        elseif($customer_id<1){$error=fl(); setMessage(36,'Custorme');}
        elseif(!isset($customers[$customer_id])){$error=fl(); setMessage(63,'Custorme');}
        elseif($contribute<0 || $contribute>100){$error=fl(); setMessage(63,'Contribute');}
        elseif($end_time<$start_time){
            $error=fl(); setMessage(1,'Date objects is not valid.');
        }
        /*else{
            $honoraarium = $db->getRowData('honorarium_evaluation','where base_id='.$base_id.' and doctor_id='.$doctor_id.' and customer_id='.$customer_id);
            if(!empty($honoraarium)){$error=fl(); setMessage(1,'Already have one like this');}
        } */
        if(!isset($error)){
            $data = [
                'base_id'      => $base_id,
                'doctor_id'    => $doctor_id,
                'customer_id'  => $customer_id,
                'contribute'   => $contribute,
                'start_date'   => $start_time,
                'end_date'     => $end_time,
            ];
            $db->arrayUserInfoAdd($data);
            $insert=$db->insert('honorarium_evaluation',$data);
            if($insert){
                $general->redirect($pUrl,29,$pageTitle);

            }
            else{$error=fl(); setMessage(66);}

        }
    }
    ?>
    <div class="row">
        <div class="col-lg-12">
            <div class="white-box border-box">
                <div class="row">
                    <div class="col-lg-12"><?php show_msg();?></div>
                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <form method="post" action="">
                            <div class="col-xs-6 col-sm-4 col-md-4">
                                <?php $general->inputBoxSelect($base,'Base','base_id','id','title',@$_POST['base_id']);?>
                                <?php $general->inputBoxSelect([],'Doctor','doctor_id','id','name');?>
                                <?php $general->inputBoxSelect([],'Customer','customer_id','id','name');?>

                                <?php $general->inputBoxText('contribute','Contribute (%)',@$_POST['contribute']);?>
                                <div class="form-group row">
                                    <label for="newInputs" class="col-md-4 col-form-label">Start</label>
                                    <div class="col-md-8">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <select class="form-control" name="start_month" >
                                                    <?php foreach ($months as $key => $name): ?>
                                                        <option value="<?= $key; ?>" <?= $key == $currentMonth ? 'selected' : ''; ?>>
                                                            <?= $name; ?>
                                                        </option>
                                                        <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <select class="form-control" name="start_year" id="start_year">
                                                    <?php for ($year = $startYear; $year <= $endYear; $year++): ?>
                                                        <option value="<?= $year; ?>" <?= $year == $currentYear ? 'selected' : ''; ?>>
                                                            <?= $year; ?>
                                                        </option>
                                                        <?php endfor; ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="newInputs" class="col-md-4 col-form-label">End</label>
                                    <div class="col-md-8">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <select class="form-control" name="end_month" >
                                                    <?php foreach ($months as $key => $name): ?>
                                                        <option value="<?= $key; ?>" <?= $key == $currentMonth ? 'selected' : ''; ?>>
                                                            <?= $name; ?>
                                                        </option>
                                                        <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <select class="form-control" name="end_year" id="end_year">
                                                    <?php for ($year = $startYear; $year <= $endYear; $year++): ?>
                                                        <option value="<?= $year; ?>" <?= $year == $currentYear ? 'selected' : ''; ?>>
                                                            <?= $year; ?>
                                                        </option>
                                                        <?php endfor; ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group m-b-0">
                                    <div class="pull-right">
                                        <input type="submit" name="add" value="Add" class="btn btn-lg btn-info waves-effect waves-light">
                                    </div>
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
elseif(isset($_GET['edit'])){
    $edit = intval($_GET['edit']);
    $u = $db->get_rowData('honorarium_evaluation','id',$edit);
    if(empty($u)){$general->redirect($pUrl,array(37,$pageTitle));}
    if(isset($_POST['edit'])){
        $base_id = intval($_POST['base_id']);
        $doctor_id = intval($_POST['doctor_id']);
        $customer_id = intval($_POST['customer_id']);
        $contribute = intval($_POST['contribute']);

        $start_month=intval(@$_POST['start_month']);
        $start_year=intval(@$_POST['start_year']);

        $start_time = mktime(0, 0, 0, $start_month, 1, $start_year);

        $end_month=intval($_POST['end_month']);
        $end_year=intval($_POST['end_year']);
        $end_time = mktime(23, 59, 59, $end_month + 1, 0, $end_year);

        if($base_id<1){$error=fl(); setMessage(36,'Base');}
        elseif(!isset($base[$base_id])){$error=fl(); setMessage(63,'Base');}
        elseif($doctor_id<1){$error=fl(); setMessage(36,'Doctor');}
        elseif(!isset($doctors[$doctor_id])){$error=fl(); setMessage(63,'Doctor');}
        elseif($customer_id<1){$error=fl(); setMessage(36,'Custorme');}
        elseif(!isset($customers[$customer_id])){$error=fl(); setMessage(63,'Custorme');}
        elseif($contribute<0 || $contribute>100){$error=fl(); setMessage(63,'Contribute');}
        elseif($end_time<$start_time){
            $error=fl(); setMessage(1,'Date objects is not valid.');
        }
        /*else{
            $honoraarium = $db->getRowData('honorarium_evaluation','where id!='.$edit.' and base_id='.$base_id.' and doctor_id='.$doctor_id.' and customer_id='.$customer_id);
            if(!empty($honoraarium)){$error=fl(); setMessage(1,'Already have one like this');}
        }*/
        if(!isset($error)){
            $data = [
                'base_id'         => $base_id,
                'doctor_id'          => $doctor_id,
                'customer_id'       => $customer_id,
                'contribute'   => $contribute,
                'start_date'   => $start_time,
                'end_date'     => $end_time,
            ];
            $where = ['id'=>$edit];
            $db->arrayUserInfoEdit($data);
            $update=$db->update('honorarium_evaluation',$data,$where);
            if($update){
                $general->redirect($pUrl,30,$pageTitle);

            }
            else{$error=fl(); setMessage(66);}

        }
    }


    $data = array($pUrl=>$pageTitle,'1'=>'Edit');
    $general->pageHeader('Edit '.$rModule['name'],$data);
    ?>
    <script type="">
        var doctor_id = <?=intval(@$u['doctor_id'])?>;
        $(document).on('change','#base_id',function(){base_wise_doctor(this.value);base_wise_customer(this.value,'','','Select Customer')});
        $(document).ready(function(){
            base_wise_doctor(<?=intval(@$u['base_id'])?>);    
            base_wise_customer(<?=intval(@$u['base_id'])?>,'',<?=intval(@$u['customer_id'],)?>,'Select Customer');    
        });
    </script>
    <div class="row">
        <div class="col-lg-12">
            <div class="white-box border-box">
                <div class="row">
                    <div class="col-lg-12"><?php show_msg();?></div>
                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <form method="post" action="">
                            <div class="col-xs-6 col-sm-4 col-md-4">
                                <?php $general->inputBoxSelect($base,'Base','base_id','id','title',@$u['base_id']);?>
                                <?php $general->inputBoxSelect([],'Doctor','doctor_id','id','name');?>
                                <?php $general->inputBoxSelect([],'Customer','customer_id','id','name');?>

                                <?php $general->inputBoxText('contribute','Contribute (%)',@$u['contribute']);?>

                                <div class="form-group row">
                                    <label for="newInputs" class="col-md-4 col-form-label">Start</label>
                                    <div class="col-md-8">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <select class="form-control" name="start_month" >
                                                    <?php foreach ($months as $key => $name): ?>
                                                        <option value="<?= $key; ?>" <?= $key == date("n", @$u['start_date']) ? 'selected' : ''; ?>>
                                                            <?= $name; ?>
                                                        </option>
                                                        <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <select class="form-control" name="start_year" id="start_year">
                                                    <?php for ($year = $startYear; $year <= $endYear; $year++): ?>
                                                        <option value="<?= $year; ?>" <?= $year == date("Y", @$u['start_date']) ? 'selected' : ''; ?>>
                                                            <?= $year; ?>
                                                        </option>
                                                        <?php endfor; ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="newInputs" class="col-md-4 col-form-label">End</label>
                                    <div class="col-md-8">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <select class="form-control" name="end_month" >
                                                    <?php foreach ($months as $key => $name): ?>
                                                        <option value="<?= $key; ?>" <?= $key == date("n", @$u['end_date']) ? 'selected' : ''; ?>>
                                                            <?= $name; ?>
                                                        </option>
                                                        <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <select class="form-control" name="end_year" id="end_year">
                                                    <?php for ($year = $startYear; $year <= $endYear; $year++): ?>
                                                        <option value="<?= $year; ?>" <?= $year == date("Y", @$u['end_date']) ? 'selected' : ''; ?>>
                                                            <?= $year; ?>
                                                        </option>
                                                        <?php endfor; ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>


                            <div class="clearfix visible-xs"></div>
                            <div class="col-xs-6 col-sm-4 col-md-4">

                                <div class="form-group m-b-0">
                                    <div class="pull-right">
                                        <input type="submit" name="edit" value="Edit" class="btn btn-info waves-effect waves-light">
                                    </div>
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
else{
    $data = array($pUrl=>$pageTitle);
    //$sortLink='<a style="font-size: 20px; color: #228AE6;margin-left: 20px;" href="'.$pUrl.'&sort=1"><i class="fa fa-arrows-v"></i></a>';
    $general->pageHeader($rModule['name'],$data,$general->addBtnHtml($pUrl));

    ?>

    <div class="row">
        <div class="col-sm-12">
            <div class="white-box">
                <div class="row">
                    <div class="col-sm-12 col-lg-12">   


                        <?php
                        $general->inputBoxSelectForReport($base,'Base','base_id','id','title');
                        $general->inputBoxSelectForReport($doctors,'Doctors','doctors_id','id','name');
                        $general->inputBoxSelectForReport($customers,'Customer','customer_id','id','name');
                        ?>

                        <div class="col-md-4"> <!-- Adjust column width as needed -->
                            <h5 class="box-title">Month</h5>
                            <div class="row">
                                <!-- First select input -->
                                <div class="col-md-6">
                                    <select class="form-control select2" id="src_month" >
                                        <option value="">Select Month</option>
                                        <?php foreach ($months as $key => $name): ?>
                                            <option value="<?= $key; ?>" >
                                                <?= $name; ?>
                                            </option>
                                            <?php endforeach; ?>
                                    </select>
                                </div>
                                <!-- Second select input -->
                                <div class="col-md-6">
                                    <select class="form-control select2"  id="src_year">
                                        <option value="">Select Years</option>
                                        <?php for ($year = $startYear; $year <= $endYear; $year++): ?>
                                            <option value="<?= $year; ?>">
                                                <?= $year; ?>
                                            </option>
                                            <?php endfor; ?>
                                    </select>
                                </div>
                            </div>
                        </div>




                        <div class="col-md-2">
                            <h5 class="box-title">Search</h5>
                            <button class="btn btn-success" onclick="honorarium_evaluation_list()">Search</button>
                            <script type="text/javascript">
                                function honorarium_evaluation_list(){
                                    let base_id   = parse_int($('#base_id').val());
                                    let customer_id   = parse_int($('#customer_id').val());
                                    let doctors_id  = parse_int($('#doctors_id').val());
                                    let src_month  = parse_int($('#src_month').val());
                                    let src_year  = parse_int($('#src_year').val());
                                    $('#reportArea').html(loadingImage);
                                    $.ajax({
                                        type:'post',
                                        url:ajUrl,
                                        data:{honorarium_evaluation_list:1,base_id:base_id,customer_id:customer_id,doctors_id:doctors_id,src_month:src_month,src_year:src_year},
                                        success:function(data){
                                            if(typeof(data.status)!="undefined"){
                                                if(data.status==1){
                                                    $('#reportArea').html(data.html);
                                                }
                                                else{
                                                    $('#reportArea').html('');
                                                }
                                                swMessageFromJs(data.m);
                                            }
                                            else{
                                                $('#reportArea').html('');
                                                swMessage(AJAX_ERROR_MESSAGE); 
                                            }
                                        },
                                        error:function(){
                                            $('#reportArea').html('');
                                            swMessage(AJAX_ERROR_MESSAGE); 
                                        }
                                    });
                                }
                                $(document).ready(function(){
                                    honorarium_evaluation_list();
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
}



?>

