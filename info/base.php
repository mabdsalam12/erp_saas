<?php
    $aStatus      = true;
    $eStatus      = true;
    $pageTitle      = $rModule['title'];
    $titleFieldName = 'Title';

    $mpo_data=$db->selectAll('users','where isActive=1 and type='.USER_TYPE_MPO.' order by username asc');
    if(!empty($mpo_data)){
        $general->arrayIndexChange($mpo_data,'id');
    }
    $invoice_print_types=[
        'with_tp'=>[
            'id'=>'with_tp',
            'title'=>'With TP'
        ],
        'without_tp'=>[
            'id'=>'without_tp',
            'title'=>'Without TP'
        ]
    ];
    
    $base_types=[
        'finish'=>[
            'id'=>'finish',
            'title'=>'Finish'
        ],
        'toll'=>[
            'id'=>'toll',
            'title'=>'Toll'
        ]
    ];
    
    if(isset($_GET['add'])){
        $data = array($pUrl=>$pageTitle,'1'=>'Add');

        $general->pageHeader('Add '.$pageTitle,$data);

        if(isset($_POST['add'])){
            $title     = $_POST['title'];
            $code     = $_POST['code'];
            $area     = $_POST['area'];
            $district     = $_POST['district'];
            $mpo_id = intval($_POST['mpo_id']);
            if(empty($title)){setMessage(36,'Title');$error=fl();}
            else if(empty($code)){setMessage(36,'Code');$error=fl();}
            
            if($mpo_id>0){
                if(!isset($mpo_data[$mpo_id])){
                    setMessage(63,'MPO');
                }
            }

            if(!isset($error)){
                $data = [
                    'title' => $title,
                    'code'  => $code,
                    'area'  => $area,
                    'district'  => $district,
                    'mpo_id'=> $mpo_id,
                ];
                $db->arrayUserInfoAdd($data);
                $db->transactionStart();
                $pID=$db->insert('base',$data);
                if($pID==false){
                    $error=fl();setMessage(66);
                }
                if(!isset($error)){
                    $ac=true;
                }
                else{
                    $ac=false;
                }
                $db->transactionStop($ac);
                if(!isset($error)){
                    $general->redirect($pUrl,29,$pageTitle);
                }
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
                                <?php $general->inputBoxText('code','Code',@$_POST['code'],'y');?>
                                <?php $general->inputBoxText('title',$titleFieldName,@$_POST['title'],'y');?>
                                <?php $general->inputBoxText('area','Area',@$_POST['area'],'y');?>
                                <?php $general->inputBoxText('district','District',@$_POST['district'],'y');?>
                                
                                <?php $general->inputBoxSelect($mpo_data,'MPO','mpo_id','id','username',@$_POST['mpo_id']);?>
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
        $id = intval($_GET['edit']);
        $b = $smt->base_info_by_id($id);
        if(empty($b)){$general->redirect($pUrl,37,$pageTitle);}
        $general->arrayContentShow($b);
        $base_data=$general->getJsonFromString($b['data']);
        if(isset($_POST['edit'])){
            $title  = $_POST['title'];
            $code   = $_POST['code'];
            $area     = $_POST['area'];
            $district     = $_POST['district'];
            $invoice_print_type     = $_POST['invoice_print_type'];
            $base_type     = $_POST['base_type'];
            $mpo_id = intval($_POST['mpo_id']);
            if(!isset($base_types[$base_type])){
                $base_type='finish';
            }
            if(empty($title)){setMessage(36,'Title');$error=fl();}
            else if(empty($code)){setMessage(36,'Code');$error=fl();}
            

            if(!isset($invoice_print_types[$invoice_print_type])){
                $invoice_print_type='with_tp';
            }

            if($mpo_id>0){
                if(!isset($mpo_data[$mpo_id])){
                    setMessage(63,'MPO');
                }
            }

            if(!isset($error)){
                $base_data['invoice_print_type']=$invoice_print_type;
                $base_data['base_type']=$base_type;
                $data = [
                    'title' => $title,
                    'code'  => $code,
                    'area'  => $area,
                    'district'  => $district,
                    'mpo_id'=> $mpo_id,
                    'data'=>json_encode($base_data)
                ];
                $where=['id'=>$id];
                $db->arrayUserInfoEdit($data);
                
                $pID=$db->update('base',$data,$where);
                if($pID==false){
                    $error=fl();setMessage(66);
                }
                if(!isset($error)){
                    $general->redirect($pUrl,29,$pageTitle);
                }
            }
        }


        $data = array($pUrl=>$pageTitle,'javascript:void()'=>$b['title'],'1'=>'Edit');
        $general->pageHeader('Edit '.$rModule['title'],$data);
    ?>
    <div class="row">
        <div class="col-lg-12">
            <div class="white-box border-box">
                <div class="row">
                    <div class="col-lg-12"><?php show_msg();?></div>
                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <form method="post" action="">
                            <div class="col-xs-6 col-sm-4 col-md-4">
<?php $general->inputBoxText('code','Code',$b['code'],'y');?>
<?php $general->inputBoxText('title',$titleFieldName,$b['title'],'y');?>
<?php $general->inputBoxSelect($mpo_data,'MPO','mpo_id','id','username',$b['mpo_id']);?>
<?php
$invoice_print_type=$base_data['invoice_print_type']??'';
$general->inputBoxSelect($invoice_print_types,'Invoice print type','invoice_print_type','id','title',$invoice_print_type,'','','','n');
$base_type=$base_data['base_type']??'';
$general->inputBoxSelect($base_types,'base type','base_type','id','title',$base_type,'','','','n');
?>
<?php $general->inputBoxText('area','Area',$b['area']);?>
<?php $general->inputBoxText('district','District',$b['district']);?>
<?php
$general->editBtn();
?>
                            </div>


                            <div class="clearfix visible-xs"></div>
                            <div class="col-xs-6 col-sm-4 col-md-4">

                                
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
        $data =[$pUrl=>$pageTitle];
        $general->pageHeader($rModule['title'],$data,$general->addBtnHtml($pUrl));
        $base_data=$db->selectAll('base','order by title asc');
    ?>
    <div class="row">
        <div class="col-sm-12">
            <div class="white-box">
            <div class="col-lg-12"><?php show_msg();?></div>
                <div class="col-md-12" id="reportArea">
                    <table class="table table-striped table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>SN</th>
                                <th>Code</th>
                                <th>Title</th>
                                <th>Area</th>
                                <th>District</th>
                                <th>MPO</th>
                                <th>Edit</th>
                            </tr>
                        </thead> 
                        <tbody>
                            <?php
                                $total=1;
                                foreach($base_data as $b){
                                ?>
                                <tr>
                                    <td><?=$total++?></td>
                                    <td><?=$b['code']?></td>
                                    <td><?=$b['title']?></td>
                                    <td><?=$b['area']?></td>
                                    <td><?=$b['district']?></td>
                                    <td><?=@$mpo_data[$b['mpo_id']]['username']?></td>
                                    <td><a href="<?=$pUrl?>&edit=<?=$b['id']?>" class="btn btn-info">Edit</a>
                                    </td>
                                </tr>
                                <?php
                                }
                            ?>
                        </tbody>

                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php
    }