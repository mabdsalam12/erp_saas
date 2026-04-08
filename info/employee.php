<?php
$aStatus      = $db->permission(35);
$eStatus      = $db->permission(36);


$tPTbl          = 74;
$tpID           = 'id';
$designations=$db->selectAll('employee_designation','where isActive =1 order by title asc');
$department=$db->selectAll('employee_department','where isActive =1 order by title asc');
$general->arrayIndexChange($designations);


if(isset($_GET['add'])){
    if(!$aStatus){$general->redirect($pUrl,146,'add employee');}
    $data = array($pUrl=>$rModule['title'],1=>'Add');
    $general->pageHeader('Add '.$rModule['title'],$data);
    if(isset($_POST['add'])){
        $name               = $_POST["name"];
        $father            = $_POST["father"];
        $mother            = $_POST["mother"];
        $date_of_birth     = intval(strtotime($_POST["date_of_birth"]));
        $date_of_join      = intval(strtotime($_POST["date_of_join"]));
        $NID               = $_POST["NID"];
        $present_address    = $_POST["present_address"];
        $parmanent_address  = $_POST["parmanent_address"];
        $mobile            = $_POST["mobile"];
        $email             = $_POST["email"];
        $designation_id    = intval($_POST["designation_id"]);
        $department_id    = intval($_POST["department_id"]);
        $salary            = floatval($_POST["salary"]);
        //$openint_due              = floatval($_POST["openint_due"]);
        $openint_balance          = floatval($_POST["openint_balance"]);
        $balance_type             = intval($_POST["balance_type"]);
        if(empty($name)){setMessage(36,'Name');$error=fl();}
        //if(!array_key_exists($scID,$sections)){$error=fl();setMessage(63,'Section');}
        if($date_of_join==0){$error=fl();setMessage(63,'Salary Start Date');}
        //if($openint_due<0){$error=fl();setMessage(63,'Due');}
        if($openint_balance<0){$error=fl();setMessage(63,'Opening Balance');}
        if($openint_balance>0){
            if($balance_type!=DEBIT&&$balance_type!=CREDIT){$error=fl();setMessage(63,'Opening Balance Type');}
        }
        if(!isset($error)){
            $data = array(
                'designation_id'   => $designation_id,
                'department_id'   => $department_id,
                'salary'           => $salary,
                'name'             => $name,
                'father'           => $father,
                'mother'           => $mother,
                'date_of_birth'    => $date_of_birth,
                'date_of_join'     => $date_of_join,
                'NID'              => $NID,
                'present_address'   => $present_address,
                'parmanent_address' => $parmanent_address,
                'mobile'           => $mobile,
                'email'            => $email,
            );
            $db->arrayUserInfoAdd($data);
            $db->transactionStart();
            $id=$db->insert('employees',$data,'getId');
            if($id!=false){
                $db->actionLogCreate('eID'.$id.'_newEmployeeAdd',$data);
                $e=$smt->employeeInfoByID($id);
                $empHead=$acc->getEmployeeHead($e);
                if($empHead==false){$error=fl();setMessage(66);}
                if($openint_balance>0){
                    $capitalHead=$acc->getSystemHead(AH_CAPITAL);
                    if($capitalHead==false){$error=fl();setMessage(66);}
                    if(!isset($error)){
                        $opening=$acc->opening_voucher_create(OPENING_VOUCHER_TYPE_EMPLOYEE,$id,$empHead,$openint_balance,$balance_type);
                        if($opening!==false){
                        }else{$error=fl();setMessage(66);}
                    }
                }
                //if($opDue>0){
                //                        $data=[
                //                            'sdType'    => DUE_TYPE_OPENING,
                //                            'sdDate'    => TODAY_TIME,
                //                            'sdRef'     => $id,
                //                            'sdAmount'  => $openint_due,
                //                            'sdRemarks' => 'Opening Due'
                //                        ];
                //                        $insert=$db->insert('sale_due',$data);
                //                        if($insert==false){$error=fl();setMessage(66);}
                //                    }

            }
            else{
                $error=fl();SetMessage(66);
            }
            if(!isset($error)){
                $ac=true;
            }
            else{
                $ac=false;
            }
            $db->transactionStop($ac);
            if(!isset($error)){
                $general->redirect($pUrl,29,'Employee');
            }
            else{
                setErrorMessage($error);
            }
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
                                    <?php $general->inputBoxText('name','Name',@$_POST['name'],'y');?>
                                    <?php $general->inputBoxText('father','Father Name',@$_POST['father']);?>
                                    <?php $general->inputBoxText('mother','Mother Name',@$_POST['mother']);?>
                                    <?php $general->inputBoxText('date_of_birth','Date of birth',@$_POST['date_of_birth'],'','daterangepicker_e');?>
                                    <?php $general->inputBoxText('NID','NID',@$_POST['NID']);?>
                                    <?php $general->inputBoxText('mobile', 'Mobile',@$_POST['mobile']);?>
                                </div>
                                <div class="col-xs-6 col-sm-4">

                                    <div class="form-group row">
                                        <label class="col-md-4 col-form-label" for="edID">Designation <?php echo BOOTSTRAP_REQUIRED;?></label>
                                        <div class="col-md-8">
                                            <?php
                                            $db->dropdownInputFromArray($designations,'designation_id','id','title',@$_POST['designation_id'],'y','','','Select Designation');
                                            ?>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-md-4 col-form-label" for="present_address">Present Address</label>
                                        <div class="col-md-8">
                                            <textarea name="present_address" class="form-control"><?php echo @$_POST['present_address'];?></textarea>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-md-4 col-form-label" for="parmanent_address">Permanent Address</label>
                                        <div class="col-md-8">
                                            <textarea name="parmanent_address" class="form-control"><?php echo @$_POST['parmanent_address'];?></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xs-6 col-sm-4">
                                    <div class="form-group row">
                                        <label class="col-md-4 col-form-label" for="edID">Department <?php echo BOOTSTRAP_REQUIRED;?></label>
                                        <div class="col-md-8">
                                            <?php
                                            $db->dropdownInputFromArray($department,'department_id','id','title',@$_POST['department_id'],'y','','','Select Department');
                                            ?>
                                        </div>
                                    </div>
                                    <?php $general->inputBoxText('email','Email',@$_POST['email']);?>
                                    <?php $general->inputBoxText('date_of_join','Salary Start Date',@$_POST['date_of_join'],'y','daterangepicker_e');?>
                                    <?php $general->inputBoxText('salary','Salary',@$_POST['salary']);?>
                                    <?php //$general->inputBoxText('openint_due','Opening Due',@$_POST['openint_due']);?>
                                    <div class="form-group row">
                                        <label for="qty" class="col-md-4 col-form-label">Opening Balance</label>
                                        <div class="col-md-8">
                                            <div class="input-group qty-input-group">
                                                <input class="form-control amount_td" value="<?php echo @$_POST['openint_balance'];?>" placeholder="Balance" type="text" name="openint_balance">
                                                <div class="input-group-append">
                                                    <select name="balance_type">
                                                        <option value="">Select Type</option>
                                                        <option value="<?php echo DEBIT;?>" <?php echo $general->selected(DEBIT,@$_POST['balance_type']);?>>Advance</option>
                                                        <option value="<?php echo CREDIT;?>" <?php echo $general->selected(CREDIT,@$_POST['balance_type']);?>>Due</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <?php echo $general->addBtn();?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php

}
elseif(isset($_GET['editOpening'])){
    if($eStatus==false){$general->redirect($pUrl,146,'Edit');}
    $eID= intval($_GET['editOpening']);
    $e = $db->get_rowData('employees',$tpID,$eID);
    if(empty($e)){$general->redirect($pUrl,37,$rModule['title']);}

    $general->arrayContentShow($e);
    $data = array($pUrl=>$rModule['title'],'javascript:void()'=>$e['name'],'1'=>'Edit');
    $general->pageHeader('Salary Info '.$e['name'],$data);

    $openingVoucher=$acc->voucherDetails(V_T_OPENING,OPENING_VOUCHER_TYPE_EMPLOYEE.'_'.$eID);
    $cOpeningB=0;
    $cOpeningD=0;
    $openingType=0;
    $ehID=$acc->getEmployeeHead($e);
    if(!empty($openingVoucher)){
        $o=current($openingVoucher);
        $cOpeningB=(float)$o['amount'];
        if($o['debit']==$ehID){
            $openingType=DEBIT;
        }
        else{
            $openingType=CREDIT;
        }
    }
    $capitalHead=$acc->getSystemHead(AH_CAPITAL);
    //$general->printArray($openingVoucher);

    if(isset($_POST['edit'])){
        $openingBalance=floatval($_POST['opening_balance']);
        $balance_type=intval($_POST['balance_type']);
        $db->transactionStart();

        if($openingBalance>0){
            if($balance_type!=DEBIT&&$balance_type!=CREDIT){$error=fl();setMessage(63,'Opening Balance Type');}
            if(!empty($openingVoucher)){
                $dHead=0;
                $cHead=0;
                if($balance_type==DEBIT){
                    $dHead=$ehID;
                    $cHead=$capitalHead;
                }
                else{
                    $dHead=$capitalHead;
                    $cHead=$ehID;
                }


                $update=$acc->voucherEdit($o['id'],$openingBalance,$o['note'],$dHead,$cHead);
                if($update==false){$error=fl();setMessage(66);}
            }
            else{
                $error=fl();setMessage(66);
            }
        }
        if(!isset($error)){
            $ac=true;
        }
        else{
            $ac=false;
        }
        $db->transactionStop($ac);
        if(!isset($error)){
            $general->redirect($pUrl,29,'Employee Opening');
        }
        else{
            setErrorMessage($error);
        }
    }
    ?>
    <div class="row"><div class="col-lg-12"><?php show_msg();?></div></div>
    <div class="row">
        <div class="col-sm-12">
            <div class="white-box border-box">
                <form method="post" action="">
                    <div class="row">
                        <div class="col-xs-6 col-sm-5">
                            <div class="form-group row">
                                <label for="qty" class="col-md-4 col-form-label">Opening Balance</label>
                                <div class="col-md-8">
                                    <div class="input-group qty-input-group">
                                        <input class="form-control amount_td" value="<?php echo $cOpeningB;?>" placeholder="Balance" type="text" name="opening_balance">
                                        <div class="input-group-append">
                                            <select name="balance_type">
                                                <option value="">Select Type</option>
                                                <option value="<?php echo DEBIT;?>" <?php echo $general->selected(DEBIT,$openingType);?>>Advance</option>
                                                <option value="<?php echo CREDIT;?>" <?php echo $general->selected(CREDIT,$openingType);?>>Due</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xs-6 col-sm-3">

                            <?php $general->editBtn();?>
                        </div>
                    </div>
                </form> 
            </div>
        </div>

    </div>
    <?php
}
elseif(isset($_GET['salaryInfo'])){
    if($eStatus==false){$general->redirect($pUrl,146,'Edit');}
    $eID= intval($_GET['salaryInfo']);
    $e = $db->get_rowData('employees',$tpID,$eID);
    if(empty($e)){$general->redirect($pUrl,37,$rModule['title']);}
    $general->arrayContentShow($e);
    $data = array($pUrl=>$rModule['title'],'javascript:void()'=>$e['name'],'1'=>'Edit');
    $general->pageHeader('Salary Info '.$e['name'],$data);
    ?>
    <div class="row">
        <div class="col-sm-12"><?php show_msg();?></div>
        <div class="col-sm-12">
            <div class="white-box border-box">
                <?php
                $y=0;
                $m=0;
                if(isset($_GET['y'])){
                    $ty=intval($_GET['y']);
                    if($ty>=date('Y',$e['date_of_join'])&&$ty<=(date('Y')+1)){
                        $y=$ty;
                    }
                }
                if(isset($_GET['m'])){
                    $tm=intval($_GET['m']);
                    if($tm>0&&$tm<=12){
                        $m=$tm;
                    }
                }
                if($y==0){
                    for($i=date('Y',$e['date_of_join']);$i<=date('Y')+1;$i++){
                        ?>
                        <a href="<?php echo $pUrl;?>&salaryInfo=<?php echo $eID;?>&y=<?php echo $i;?>" class="btn btn-success">Salary For <?php echo $i;?></a><br><br>
                        <?php
                    }
                }
                elseif($m==0){
                    for($i=1;$i<=12;$i++){
                        ?>
                        <div class="col-md-4">
                            <a href="<?php echo $pUrl;?>&salaryInfo=<?php echo $eID;?>&y=<?php echo $y;?>&&m=<?php echo $i;?>" class="btn btn-success">Salary For <?php echo $y;?> <?php echo $general->monthNameById($i);?></a><br><br>
                        </div>
                        <?php
                    }
                }
                else{
                    $salary=$acc->employeeSalaryInfo($eID,$y,$m,false);
                    //$general->printArray($salary);
                    if($salary!==false){
                        if(isset($_POST['edit'])){
                            $newSalary=intval($_POST['salary']);
                            //$general->printArray($eID.'_'.$y.'_'.$m);
                            $currentSalary=$acc->voucherDetails(V_T_EMPLOYEE_SALARY,$eID.'_'.$y.'_'.$m);
                            //$general->varDump($currentSalary);
                            if(!empty($currentSalary)){
                                $currentSalary=current($currentSalary);
                                //                                    $general->printArray($currentSalary);
                                //                                    $general->printArray($newSalary);
                                $particular=$e['name'].' salary for 31/'.$m.'/'.$y;
                                $update=$acc->voucherEdit($currentSalary['id'],$newSalary,$particular,0,$e['ledger_id']);
                                if($update){
                                    $salary=$acc->employeeSalaryInfo($eID,$y,$m,false);
                                    $general->redirect($pUrl,2,'Salary info update successfully');

                                }
                                else{$error=fl();setMessage(66);}
                            }
                            else{
                                $error=fl();setMessage(66);
                            }
                            if(isset($error)){setErrorMessage($error);}
                        }
                        ?>
                        <form method="post" action="">
                            <div class="row">
                                <div class="col-sm-12"><?php show_msg();?></div>
                                <div class="col-xs-6 col-sm-4">
                                    <?php $general->inputBoxText('name','Name',$e['name'],'','','disabled');?>
                                    <?php $general->inputBoxText('year','Year',$y,'','','disabled');?>
                                    <?php $general->inputBoxText('month','Month',$general->monthNameById($m),'','','disabled');?>
                                    <?php $general->inputBoxText('salary', 'Salary',$salary,'','amount_td');?>
                                    <div class="save-submit-btn-outter"> <?php $general->saveBtn();?> </div>  
                                </div>

                            </div>

                        </form>
                        <?php
                    }
                    else{
                        ?>
                        <h3>Salary not editable</h3>
                        <?php
                    }
                }
                ?>
            </div>
        </div>

    </div>
    <?php
}
elseif(isset($_GET['edit'])){
    if($eStatus==false){$general->redirect($pUrl,146,'Edit');}
    $edit = intval($_GET['edit']);
    $e = $db->get_rowData('employees',$tpID,$edit);
    if(empty($e)){$general->redirect($pUrl,37,$rModule['title']);}
    $general->arrayContentShow($e);
    $deletePremision=$db->permission(82);
    if($deletePremision){
        if(isset($_GET['delete'])){
            $data = array(
                'isActive'=> 2
            );
            $db->arrayUserInfoEdit($data);
            $where=array($tpID=>$edit);
            $update=$db->update('employees',$data,$where);
            if($update){
                $general->redirect($pUrl,14,'Employee');
            }
        }
    }
    if(isset($_POST['edit'])){
        $name               = $_POST["name"];
        $father            = $_POST["father"];
        $mother            = $_POST["mother"];
        $date_of_birth     = intval(strtotime($_POST["date_of_birth"]));
        $date_of_join      = intval(strtotime($_POST["date_of_join"]));
        $date_of_resign    = intval(strtotime($_POST["date_of_resign"]));
        $NID               = $_POST["NID"];
        $present_address    = $_POST["present_address"];
        $parmanent_address  = $_POST["parmanent_address"];
        $mobile            = $_POST["mobile"];
        $email             = $_POST["email"];
        $designation_id    = intval($_POST["designation_id"]);
        $department_id    = intval($_POST["department_id"]);
        $status    = intval($_POST["status"]);
        $salary            = floatval($_POST["salary"]);
        if(empty($name)){SetMessage(36,'Name');$error=fl();}
        if($date_of_join==0){$error=fl();setMessage(63,'Salary Start Date');}
        if(!isset($error)){

            $data = array(
                'designation_id'   => $designation_id,
                'department_id'   => $department_id,
                'salary'           => $salary,
                'name'             => $name,
                'father'           => $father,
                'mother'           => $mother,
                'date_of_birth'    => $date_of_birth,
                'date_of_join'     => $date_of_join,
                'date_of_resign'   => $date_of_resign,
                'NID'              => $NID,
                'present_address'   => $present_address,
                'parmanent_address' => $parmanent_address,
                'mobile'           => $mobile,
                'email'            => $email,
                'isActive'            => $status,
            );
            $db->arrayUserInfoEdit($data);
            $db->transactionStart();
            $where=array($tpID=>$edit);
            $update=$db->update('employees',$data,$where,' ');
            if($update){
                if($e['ledger_id']>0){
                    $data=array(
                        'title'=> $name
                    );
                    $where=array(
                        'id'=> $e['ledger_id']
                    );
                    $update=$db->update('a_ledgers',$data,$where);
                    if(!$update){$error=fl(); setMessage(66);}
                }

            }
            else{
                $error=fl();SetMessage(66);
            }
            $ac=false;
            if(!isset($error)){
                $ac=true;
            }

            $db->transactionStop($ac);
            if($ac){
                $db->actionLogCreate('eID'.$e['id'].'_employeeUpdate',$data);
                $general->redirect($pUrl,30,'Employee'); 
            }
        }
    }
    $data = array($pUrl=>$rModule['title'],'javascript:void()'=>$e['name'],'1'=>'Edit');
    $general->pageHeader('Edit '.$rModule['title'],$data);
    ?>
    <div class="row">
        <?php
        show_msg();
        ?>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="white-box border-box">
                <form method="post" action="">
                    <div class="row">
                        <div class="col-xs-6 col-sm-4">
                            <?php $general->inputBoxText('name','Name',$e['name'],'y');?>
                            <?php $general->inputBoxText('father','Father Name',$e['father']);?>
                            <?php $general->inputBoxText('mother','Mother Name',$e['mother']);?>
                            <?php $general->inputBoxText('date_of_birth','Date of birth',$general->make_date($e['date_of_birth']),'','daterangepicker_e');?>
                            <?php $general->inputBoxText('NID','NID',$e['NID']);?>
                            <?php $general->inputBoxText('mobile', 'Mobile',$e['mobile']);?>
                            <?php
                            if($deletePremision){
                                ?>
                                <a href="<?php echo $pUrl;?>&editOpening=<?php echo $edit;?>" style="color: green;">Edit Opening </a>
                                <?php
                            }
                            ?>
                        </div>
                        <div class="col-xs-6 col-sm-4">
                            <div class="form-group row">
                                <label class="col-md-4 col-form-label" for="edID">Designation <?php echo BOOTSTRAP_REQUIRED;?></label>
                                <div class="col-md-8">
                                    <?php
                                    $db->dropdownInputFromArray($designations,'designation_id','id','title',@$e['designation_id'],'y','','','Select Designation');
                                    ?>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-md-4 col-form-label" for="present_address"><?php echo $db->l('present_address') ?> </label>
                                <div class="col-md-8">
                                    <textarea name="present_address" class="form-control"><?php echo $e['present_address'];?></textarea>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-4 col-form-label" for="parmanent_address"><?php echo $db->l('permanent_address') ?> </label>
                                <div class="col-md-8">
                                    <textarea name="parmanent_address" class="form-control"><?php echo $e['parmanent_address'];?></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-6 col-sm-4">
                            <div class="form-group row">
                                <label class="col-md-4 col-form-label" for="edID">Department <?php echo BOOTSTRAP_REQUIRED;?></label>
                                <div class="col-md-8">
                                    <?php
                                    $db->dropdownInputFromArray($department,'department_id','id','title',$e['department_id'],'y','','','Select Department');
                                    ?>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-4 col-form-label" for="edID">Status</label>
                                <div class="col-md-8">
                                    <?php
                                    $db->dropdownInputFromArray(
                                        [['id'=>"1",'title'=>'Active'],['id'=>'0','title'=>'Inactive']],
                                        'status',
                                        'id',
                                        'title',
                                        $e['isActive'],
                                        haveSelect: 'n'
                                    );
                                    ?>

                                </div>
                            </div>
                            <?php $general->inputBoxText('email', $db->l('email'),$e['email']);?>
                            <?php $general->inputBoxText('date_of_join','Salary Start Date',$general->make_date($e['date_of_join']),'y','daterangepicker_e');?>
                            <?php $general->inputBoxText('date_of_resign','Resignation Date',$general->make_date($e['date_of_resign']),'','daterangepicker_e');?>
                            <?php $general->inputBoxText('salary', $db->l('salary'),$e['salary']);?>
                            <?php //$general->inputBoxTextArea('mAddress','Address',@$m['mAddress']);
                            if($deletePremision){
                                ?>
                                <a href="<?php echo $pUrl;?>&edit=<?php echo $edit;?>&delete=1" style="color: red;" onclick="return confirm('Are you sure to delete this employee?')">Archive</a>

                                <?php
                            }
                            ?>
                        </div>
                    </div>
                    <?php $general->editBtn();?>
                </form>
            </div>
        </div>

    </div>
    <?php
}
else{
    $data = array($pUrl=>$rModule['title']);
    $general->pageHeader($rModule['title'],$data,$general->addBtnHtml($pUrl));
    $departments=$db->selectAll('employee_department','where isActive=1');
    $general->arrayIndexChange($departments, 'id');
    ?>
    <div class="row">
        <div class="col-sm-12">
            <div class="white-box border-box">
                <?php
                show_msg();
                $q=[];
                $sLink=[];
                if(isset($_GET['name'])){
                    $name=$_GET['name'];
                    if(!empty($name)){   
                        $q[]="name like '%".$name."%'";
                        $sLink[]='name='.$name;
                    }
                }

                if(isset($_GET['designation_id'])){
                    $designation_id=intval($_GET['designation_id']);
                    if(!empty($designation_id)){   
                        $q[]='designation_id='.$designation_id;
                        $sLink[]='designation_id='.$designation_id;
                    }
                }
                if(isset($_GET['mobile'])){
                    $mobile=$_GET['mobile'];
                    if(!empty($mobile)){   
                        $q[]="mobile like '%".$mobile."%'";
                        $sLink[]='mobile='.$mobile;
                    }
                }
                if(isset($_GET['archive'])&&$_GET['archive']==1){
                    $q[]='isActive=2';
                }
                else{
                    $q[]='isActive in(1,0)';
                }
                $sq='where '.implode(' and ',$q);
                $general->arrayContentShow($categorys);
                $employees=$db->selectAll('employees',$sq.' order by name asc');
                ?>
                <div class="row">
                    <div class="col-sm-12 col-lg-12 padding-left-0">

                        <form action="" method="get">
                            <input type="hidden" name="<?php echo MODULE_URL;?>" value="<?php echo $rModule['slug'];?>">
                            <div class="col-md-2">
                                <h5 class="box-title">Name</h5>  
                                <input type="text" class="form-control" name="name" value="<?php echo @$_GET['name']?>" >  
                            </div>
                            <div class="col-md-2">
                                <h5 class="box-title">Designation</h5>
                                <?php
                                $db->dropdownInputFromArray($designations,'designation_id','id','title',@$_GET['designation_id'],'','form-control select2');                
                                ?>
                            </div>
                            <div class="col-md-2">
                                <h5 class="box-title">Mobile</h5>
                                <input type="text" class="form-control" name="mobile" value="<?php echo @$_GET['mobile']?>" >

                            </div>
                            <div class="col-md-2">
                                <h5 class="box-title">Search</h5>
                                <input type="submit" value="Search" class="btn btn-success" name="s">
                            </div>
                            <div class="col-md-2">
                                <h5 class="box-title">&nbsp;</h5>
                                <a class="btn btn-success" href="<?=$pUrl?>&archive=1">Archive</a>
                            </div>
                        </form>
                    </div>
                </div>
                <table class="table table-striped table-bordered table-hover only_show">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Department</th>
                            <th class="amount_td">Salary</th>
                            <th>Designation</th>
                            <th>Mobile</th>
                            <?php
                            if($eStatus){
                                ?>
                                <th>Edit</th>
                                <!-- <th>Salary Info</th> -->
                                <?php
                            }
                            ?>
                        </tr>
                    </thead> 
                    <tbody>
                        <?php
                        $total=1;
                        $tc=7;
                        $tSalary=0;
                        foreach($employees as $e){
                            $tSalary+=$e['salary'];
                            $department_title='';
                            if(isset($departments[$e['department_id']])){
                                $department_title=$departments[$e['department_id']]['title'];
                            }

                            ?>
                            <tr>
                                <td><?=$total++?></td>
                                <td><?=$e['name']?></td>
                                <td><?=$department_title?></td>
                                <td class="amount_td"><?=$general->numberFormat($e['salary'])?></td>
                                <td><?=$designations[$e['designation_id']]['title']?></td>
                                <td><?=$e['mobile']?></td>
                                <?php
                                if($eStatus){
                                    ?>
                                    <td><a href="<?=$pUrl?>&edit=<?=$e[$tpID]?>" class="btn btn-info">Edit</a></td>
                                    <!-- <td><a href="<?=$pUrl?>&salaryInfo=<?=$e[$tpID]?>" class="btn btn-info" title="Salary Info">S I</a></td> -->
                                    <?php
                                }
                                ?>
                            </tr>
                            <?php
                        }
                        ?>
                        <tr>
                            <td>&nbsp;</td>
                            <td colspan="2"><b>Total</b></td>
                            <td class="amount_td"><b><?php echo $general->numberFormat($tSalary);?></b></td>
                            <td colspan="2">&nbsp;</td>
                            <?php
                            if($eStatus){
                                ?>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <?php
                            }
                            ?>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php
    $general->onclickChangeJavaScript($tPTbl,$tpID);  
}
?>