<?php
$base = $db->selectAll('base','where status=1 order by title asc');
$general->arrayIndexChange($base,'id');
$types = $general->get_all_doctor_type();
$categorys = $general->get_all_doctor_category();
$productData = $db->getProductData(' and type='.PRODUCT_TYPE_FINISHED);

if(isset($_GET['add'])){
    $data = array($pUrl=>$rModule['title'],1=>'Add');
    $general->pageHeader('Add '.$rModule['title'],$data);
    if(isset($_POST['add'])){
        $name               = $_POST["name"];
        $mobile             = $_POST["mobile"];
        $code               = $_POST["code"];
        $address            = $_POST["address"];
        $base_id            = intval($_POST['base_id']);
        $category           = intval($_POST["category"]);
        $type               = intval($_POST["type"]);
        $product            = $_POST['product']??[];

        if(empty($name)){setMessage(36,'Name');$error=fl();}
        elseif(empty($code)){setMessage(36,'Code');$error=fl();}
        elseif(strlen($code)>20){setMessage(63,'Code');$error=fl();}
        elseif(!isset($base[$base_id])){$error=fl(); setMessage(63,'base');}
        elseif(!isset($categorys[$category])){setMessage(63,'category');$error=fl();}
        elseif(!isset($types[$type])){setMessage(63,'type');$error=fl();}
        else{
            $products=[];
            if(!empty($product)){
                foreach($product as $p){
                    if(!isset($productData[$p])){
                        $error=fl();
                        setMessage(63,'Product');
                        break;
                    }
                    $products[$p]=$p;
                }
            }
        }
        if(!isset($error)){

            $data = array(
                'name'          =>$name,
                'mobile'        =>$mobile,
                'code'          =>$code,
                'base_id'       =>$base_id,
                'address'       =>$address,
                'category'      =>$category,
                'type'          =>$type,

            );
            $db->transactionStart();
            $db->arrayUserInfoAdd($data);
            $id=$db->insert('doctor',$data,true);
            if($id!=false){
                if(!empty($products)){
                    foreach($products as $product_id){
                        $data=['doctor_id'=>$id,'product_id'=>$product_id];
                        $id=$db->insert('doctor_products',$data);
                        if(!$id){$error=fl(); setMessage(66); break;}
                    }
                }
            }         
            else{
                $error=fl();setMessage(66);
            }
            $ac=false;
            if(!isset($error)){
                $ac=true;
            }
            $db->transactionStop($ac);
            if(!isset($error)){
                $general->redirect($pUrl,29,'Doctor');
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
                                    <?php $general->inputBoxText('mobile','Mobile',@$_POST['mobile']);?>
                                    <?php $general->inputBoxText('code','Code',@$_POST['code']);?>
                                    <?php $general->inputBoxTextArea('address','Address',@$_POST['address']);?>
                                    <?php
                                        $general->inputBoxSelect($base,'Base','base_id','id','title',@$_POST['base_id']);
                                    ?>
                                    <?php $general->inputBoxSelect($categorys,'Category','category','id','title',@$_POST['category']);?>
                                    <?php $general->inputBoxSelect($types,'Type','type','id','title',@$_POST['type']);?>
                                    </div>
                                    <div class="col-12">
                                        <div class="row">
                                            <div class="form-group row col-12">
                                                <?php
                                                    if(!empty($productData)){
                                                        foreach($productData as $p){
                                                        ?>
                                                        <div class="col-3">
                                                        <div class="form-group row">
                                                            <label class="col-md-10 col-form-label" for="product-<?=$p['id']?>">
                                                                <?=$p['t']?>
                                                            </label>
                                                            <div class="col-md-2">
                                                                <input class="form-check-input" id="product-<?=$p['id']?>" type="checkbox" value="<?=$p['id']?>" name="product[]">
                                                            </div>
                                                        </div>
                                                        </div>

                                                        <?php 
                                                        }
                                                    }
                                                ?>
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
else{
    $base_doctors=[];
    if(!empty($doctors)){
        foreach($doctors as $d){
            $base_doctors[$d['base_id']][]=$d;
        }
    }
    $base = $db->selectAll('base');
    $general->pageHeader($rModule['title'],[],$general->addBtnHtml($pUrl));
?>
<div class="row">
    <div class="col-sm-12">
        <div class="white-box">
            <div class="row">
                <div class="col-sm-12 col-lg-12">
                    <?php
                        // $general->inputBoxSelectForReport($base,'Base','base_id','id','title');    
                    ?>
                    <div class="col-md-2">
                        <h5 class="box-title">Search</h5>
                        <button class="btn btn-success" onclick="doctor_list()">Search</button>
                        <script type="text/javascript">      
                            
                            function doctor_list(){
                                let base_id = $('#base_id').val();
                                $('#reportArea').html(loadingImage);
                                const doctor_list_data={doctor_list:1,base_id:base_id};
                                $.ajax({
                                    type:'post',
                                    url:ajUrl,
                                    data:doctor_list_data,
                                    success:function(data){
                                        $('#reportArea').html('');
                                        if(typeof(data.status)!=='undefined'){
                                            if(data.status==1){
                                                $('#reportArea').html(data.html);
                                            }
                                            swMessageFromJs(data.m);
                                        }
                                        else{
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
                                doctor_list();
                            });
                        </script>
                    </div>
                </div>
                <div class="col-sm-12 col-lg-12">
                    <?php
                        show_msg();
                    ?>
                </div>
                <div class="col-sm-12 col-lg-12" id="reportArea" style="overflow: auto;"></div>
            </div>
        </div>
    </div>
</div> 
<?php
}