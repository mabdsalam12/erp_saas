<?php
$tpID           = 'id';
$tpTitle        = 'title';
$titleFieldName = 'Title'; 
$pageTitle='Sms template';

$triggers=[
    '{{customer_name}}' => 'Customer Name',
    '{{amount}}'        => 'Sale amount/Received amount',
    '{{due}}'=> 'Due amount',
    '{{company_name}}'  => 'Company name',
    '{{company_mobile}}'=> 'Company mobile',
    '{{user}}' => 'User name',
];

if(isset($_GET['add'])){
    $data = [$pUrl => $db->l($rModule['title']), 'Add' => 1];
    $general->pageHeader('Add ' . $db->l($rModule['title']), $data);

    if(isset($_POST['add'])){
        $title      = $_POST["title"];
        $sms_body   = $_POST["sms_body"];

        if(empty($title)){SetMessage(36,$titleFieldName);$error=1;}
        if(empty($sms_body)){SetMessage(36,'Template Body');$error=1;}

        if(!isset($error)){
            $data = array(
                $tpTitle    => $title,
                'body'    => $sms_body
            );
            $db->arrayUserInfoAdd($data);
            $insert = $db->insert('sms_template',$data);
            if($insert){$general->redirect($pUrl,29,$pageTitle);}
            else{
                $error=__LINE__;SetMessage(66);
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

                        <form action="" method="POST">

                            <div class="col-md-4">
                                <h4>Trigger</h4>
                                <?php
                                foreach($triggers as $k=>$t){
                                    ?>
                                    <p class="cpointer-bg" onclick="smsTemplateTriggreDataSet('<?php echo $k;?>')"><?php echo $k;?> -:--:--:--:--:-  <span  style="font-weight: bold;"><?php echo $t;?></span></p>
                                    <?php
                                }
                                ?>
                            </div>
                            <div class="col-md-8">
                                <?php
                                $general->inputBoxText('title',$titleFieldName,@$_POST['title'],'y');
                                ?>
                                <textarea class="form-control" spellcheck="false" id="sms_body" name="sms_body" style="height: 200px;line-height: 27px;font-size: 20px;font-weight: bold;"><?php echo @$_POST['sms_body'];?></textarea>
                            </div>  
                            <div class="form-group m-b-0"><br>
                                <input type="submit" class="btn btn-info waves-effect waves-light pull-right" value="Save" name="add">
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
    $data = [$pUrl => $db->l($rModule['title']), 'Edit' => 1];
    $general->pageHeader('Edit ' . $db->l($rModule['title']), $data); 
    $s = $db->get_rowData('sms_template',$tpID,$edit);
    $general->arrayContentShow($s);
    if(empty($s)){$general->redirect($pUrl,array(37,$pageTitle));}

    if(isset($_POST['edit'])){
        $title      = $_POST["title"];
        $sms_body   = $_POST["sms_body"];

        if(empty($title)){SetMessage(36,$titleFieldName);$error=1;}
        if(empty($sms_body)){SetMessage(36,'Template Body');$error=1;}

        if(!isset($error)){
            $data = array(
                $tpTitle    => $title,
                'body'    => $sms_body
            );
            $db->arrayUserInfoEdit($data);
            $where = array($tpID=>$edit);
            $update = $db->update('sms_template',$data,$where);
            if($update){$general->redirect($pUrl,30,$pageTitle);}
            else{
                $error=__LINE__;SetMessage(66);
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

                        <form action="" method="POST">

                            <div class="col-md-4">
                                <h4>Trigger</h4>
                                <?php
                                foreach($triggers as $k=>$t){
                                    ?>
                                    <p class="cpointer-bg" onclick="smsTemplateTriggreDataSet('<?php echo $k;?>')"><?php echo $k;?> -:--:--:--:--:-  <span  style="font-weight: bold;"><?php echo $t;?></span></p>
                                    <?php
                                }
                                ?>
                            </div>
                            <div class="col-md-8">
                                <?php
                                $general->inputBoxText('title',$titleFieldName,@$s['title'],'y');
                                ?>
                                <textarea class="form-control" spellcheck="false" id="sms_body" name="sms_body" style="height: 200px;line-height: 27px;font-size: 20px;font-weight: bold;"><?php echo @$s['body'];?></textarea>
                            </div>  
                            <div class="form-group m-b-0"><br>
                                <input type="submit" class="btn btn-info waves-effect waves-light pull-right" value="Update" name="edit">
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
    $general->pageHeader($db->l($rModule['title']), $data, $general->addBtnHtml($pUrl));
    $categorys=$db->selectAll('sms_template','where isActive=1  order by '.$tpTitle.' asc'); 
    $company_data = $db->get_company_data();
    $sms_settings =[];
    if(isset($company_data['sms_settings'])){
        $sms_settings=$company_data['sms_settings'];
    }
    $sms_event=[];
    if(isset($company_data['sms_event'])){
        $sms_event=$company_data['sms_event'];
    }
    ?>
    <div class="row">
        <div class="col-sm-12">
            <div class="white-box border-box">
                <?php show_msg();?>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover only_show">
                        <thead>
                            <tr>
                                <th><?php echo $db->l('sn') ?> </th>
                                <th>Title</th>
                                <th>SMS</th>
                                <th><?php echo $db->l('edit') ?> </th> 
                                <?php
                                foreach($sms_settings as $sk=>$s){
                                    if($s==1){
                                        ?><th><?=$db->l($sk);?></th><?php
                                    }
                                }
                                ?>
                            </tr>
                        </thead> 
                        <tbody>
                            <?php
                            $sl=1;
                            foreach($categorys as $c){
                                ?>
                                <tr>
                                    <td><?=$sl?></td>
                                    <td><?=$c[$tpTitle]?></td>
                                    <td><?php echo $general->content_show($c['body']);?></td>
                                    <td><a href="<?=$pUrl?>&edit=<?=$c[$tpID]?>" class="btn-edit" data-toggle="tooltip" data-placement="top" title="Edit">Edit</a></a></td>
                                    <?php
                                    foreach($sms_settings as $sk=>$s){
                                        if($s==1){
                                            ?>
                                            <td>
                                                <input type="radio" <?php if(isset($sms_event[$sk]) && intval($sms_event[$sk])==$c[$tpID]){?>checked<?php } ?> name="<?=$sk?>" value="<?php echo $c[$tpID] ;?>">
                                            </td>
                                            <?php
                                        }

                                    }
                                    ?>  
                                </tr>
                                <?php
                                $sl++;
                            }

                            ?>
                            <tr>
                                <td><?=$sl?></td>
                                <td colspan="3">No SMS send</td>
                                <?php
                                foreach($sms_settings as $sk=>$s){
                                    if($s==1){
                                        ?>
                                        <td>
                                            <input type="radio" <?php if(isset($smsSell[$sk]) && intval($smsSell[$sk])==0){?>checked<?php } ?> name="<?=$sk?>" value="0">
                                        </td>
                                        <?php
                                    }

                                }
                                ?>  
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="form-group m-b-0"><br>
                    <input type="submit" value="Update" id="sms_set_btn" class="btn btn-info pull-right sms_set_btn" name="s" onclick="smsTemplateSet();">  
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        var settings = <?= json_encode($sms_settings) ?>;
        function smsTemplateSet(){
            var smsdata = {};
            $.each(settings, function(key, value) {
                if(value==1){
                    smsdata[key]=getRadioValue(key); 
                }
            });
            $.ajax({
                type:'post',
                url:ajUrl,
                data:{sms_template_set:1,smsdata:smsdata},
                success:function(data){
                    if(typeof(data.status)  !== "undefined"){ 
                        setTimeout(() => {swMessageFromJs(data.m);}, 500);
                    }
                    else{
                        setTimeout(() => {swMessage(AJAX_ERROR_MESSAGE); }, 500);
                    }   
                },
                error: function(data) { 
                    setTimeout(() => {swMessage(AJAX_ERROR_MESSAGE); }, 500);
                }
            });

        }
    </script>
    <?php
}
?>

<script type="text/javascript">
    function smsTemplateTriggreDataSet(txt){
        var sms_body=$('#sms_body').val();
        sms_body+=' '+txt;
        $('#sms_body').val(sms_body);
    }
</script>