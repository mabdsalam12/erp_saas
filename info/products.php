<?php
    $aStatus      = true;
$eStatus      = true;

$pageTitle      = $rModule['name'];

$can_see_unit_cost=$db->permission(9);
$companyID=$cmp->getCurrentCompanyID();
if($companyID>0){
    $units=$db->selectAll('unit','where isActive=1 and company_id='.$companyID.' order by name asc','id,name');
    $general->arrayIndexChange($units,'id');

    
    $use_product_category = 1;
    
    
    $types=$smt->get_all_product_type();
    

    $subCategorys = [];
    $categoryData = [];
    if($use_product_category==1){
        $categorys=$db->selectAll('product_category','where isActive=1 and company_id='.$companyID,'id,parent,name'); 
        $general->arrayIndexChange($categorys,'id');
        if(!empty($categorys)){
            foreach($categorys as $c){
                if($c['parent']==0){
                    $categoryData[$c['id']]=[
                        'id'            => $c['id'],
                        'name'            => $c['name'],
                        'childCategory'    => []
                    ];
                }
                else{
                    if(!isset($categoryData[$c['parent']])){
                        $parent = $categorys[$c['parent']];
                        $categoryData[$c['parent']]=[
                            'id'            => $parent['id'],
                            'name'            => $parent['name'],
                            'childCategory'    => []
                        ];
                    }
                    $subCategorys[$c['parent']][$c['id']] = $c;
                    if(!isset($categoryData[$c['parent']]['childCategory'])){
                        $categoryData[$c['parent']]['childCategory']=[] ;
                    }
                    $categoryData[$c['parent']]['childCategory'][]=[
                        'id'    => $c['id'],
                        'name'    => $c['name']
                    ];  
                }
            }
        }
    }
?>
<script type=""> <?php echo 'var categoryData='.json_encode($categoryData).';';?></script>
<?php 
    if(isset($_GET['add'])){
        $data = array($pUrl=>$pageTitle,'1'=>'Add');

        $general->pageHeader('Add '.$pageTitle,$data);

        if(isset($_POST['add'])){
            $name     = $_POST['name'];
            $code     = $_POST['code'];
            $unit_id       = intval($_POST['unit_id']);

            $sale_price = floatval($_POST['sale_price']);
            $VAT = floatval($_POST['VAT']);
            $type=0;
            
            $type = intval($_POST['type']);
            
            $category=0;
            $subCategory=0;
            if($use_product_category==1){
                $category       = intval($_POST['category']);
                $subCategory       = intval($_POST['subCategory']);
            }
            if($type==!0){
                $sale_price=0;
                $sale_price=0;
            }
            if(empty($name)){setMessage(36,$titleFieldName);$error=fl();}
            elseif(empty($code)){setMessage(36,'Code');$error=fl();}
            elseif(!array_key_exists($unit_id,$units)){$error=fl();setMessage(63,'Unit');}

            elseif($sale_price<=0&&$type==PRODUCT_TYPE_FINISHED){$error=fl();setMessage(63,'TP');}
            if($use_product_category==1){
                if(!isset($categoryData[$category])){$error=fl();setMessage(63,'Category');}
                elseif(!isset($subCategorys[$category][$subCategory])){$error=fl();setMessage(63,'Sub category');}
            }
            if(!isset($error)){
                
                $data = [
                    'title'         => $name,
                    'code'          => $code,
                    'unit_id'       => $unit_id,
                    'category_id'   => $subCategory,
                    'sale_price'    => $sale_price,
                    'type'          => $type,
                    'VAT'    => $VAT,
                
                ];
                
                
                
                $db->arrayUserInfoAdd($data);
                $db->transactionStart();
                $product_id=$db->insert('products',$data,true);
                if($product_id==false){
                    $error=fl();setMessage(66);
                }
                $product_price_log = $db->product_price_log($product_id,['sale_price'=>$data['sale_price'],'unit_cost'=>0]);
                if($product_price_log==false){$error=fl();setMessage(66);}
                $ac=false;
                if(!isset($error)){
                    $ac=true;
                }
                $db->transactionStop($ac);
                if(!isset($error)){
                    $general->redirect($pUrl,29,$pageTitle);
                }
            }
        }
    ?>
    <script >
        $(document).ready(function(){mainCategoryChange()});
    </script>
    <div class="row">
        <div class="col-lg-12">
            <div class="white-box border-box">
                <div class="row">
                    <div class="col-lg-12"><?php show_msg();?></div>
                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <form method="post" action="">
                            <div class="col-xs-6 col-sm-4 col-md-4">
                                <?php $general->inputBoxText('name','Product name',@$_POST['name'],'y');?>
                                <?php $general->inputBoxText('code','Code',@$_POST['code'],'y');?>
                                <?php
                                    if($use_product_category==1){
                                        $general->inputBoxSelect($categoryData,'Category','category','id','name',@$_POST['category']);
                                        $general->inputBoxSelect([],'Sub Category','subCategory','id','name',@$_POST['subCategory']);
                                    }
                                ?>
                                <?php $general->inputBoxSelect($units,'Unit','unit_id','id','name',@$_POST['unit_id']);?>
                                <?php $general->inputBoxText('sale_price','TP',@$_POST['sale_price']);?>
                                <?php $general->inputBoxText('VAT','VAT',@$_POST['VAT']);?>
                                <?php
                                    $general->inputBoxSelect($types,'Type','type','id','name',@$_POST['type']); 
                                ?>
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
        $u = $db->get_rowData('products',$tpID,$edit);
        $general->arrayContentShow($u);
        $general->arrayIndexChange($categorys,'id');
        if(empty($u)){$general->redirect($pUrl,array(37,$pageTitle));}
        $product_data = $general->getJsonFromString($u['data']);
        $get_one_free = intval(@$product_data['get_one_free']);
        if($use_product_category==1){
            $category= $categorys[$u['category_id']]['parent'];
        }
        $statuss=[
            ['id'=>1,'title'=>'Active'],
            ['id'=>0,'title'=>'In Active'],
            ['id'=>3,'title'=>'Archive']
        ];
        if(isset($_POST['edit'])){
            $pTitle     = $_POST[$tpTitle];
            $code     = $_POST['code'];
            $unID       = intval($_POST['unID']);

            $pSalePrice = floatval($_POST['sale_price']);
            $VAT = floatval($_POST['VAT']);
            $get_one_free = intval($_POST['get_one_free']);
            $box_unit_id = intval($_POST['box_unit_id']);
            $box_unit_quantity = intval($_POST['box_unit_quantity']);
            $type = intval($_POST['type']);
            $status = intval($_POST['status']);
            $subCategory=0;
            if($use_product_category==1){
                $category       = intval($_POST['category']);
                $subCategory       = intval($_POST['subCategory']);
            }
            
            if(empty($pTitle)){setMessage(36,$titleFieldName);$error=fl();}
            elseif(empty($code)){setMessage(36,'Code');$error=fl();}
            elseif(!array_key_exists($unID,$units)){$error=fl();setMessage(63,'Unit');}

            elseif($pSalePrice<=0&&$type==0){$error=fl();setMessage(63,'TP');}
            elseif($get_one_free<0){$error=fl();setMessage(63,'get one free');}
            if($box_unit_id>0){
                if(!array_key_exists($box_unit_id,$units)){$error=fl();setMessage(63,'Box unit');}
            }
            if($use_product_category==1){
                if(!isset($categoryData[$category])){$error=fl();setMessage(63,'Category');}
                elseif(!isset($subCategorys[$category][$subCategory])){$error=fl();setMessage(63,'Sub category');}
            }
            elseif(!isset($error)){
                $product_data['get_one_free'] = $get_one_free;
                $product_data['box_unit_quantity'] = $box_unit_quantity;
                if($product_data['box_unit_quantity']<=0){
                    unset($product_data['box_unit_quantity']);
                }
                $data = [
                    'title'         => $pTitle,
                    'code'          => $code,
                    'unit_id'       => $unID,
                    'box_unit_id'   => $box_unit_id,
                    'VAT'           => $VAT,
                    'category_id'   => $subCategory,
                    'sale_price'    => $pSalePrice,
                    'isActive'      => $status,
                    'data'          => json_encode($product_data),
                ];
                $data['type']=$type;
                $where=[$tpID=>$edit];
                $db->arrayUserInfoEdit($data);
                $db->transactionStart();
                $product_id=$db->update('products',$data,$where);
                if($product_id==false){
                    $error=fl();setMessage(66);  

                }
                $product_price_log = $db->product_price_log($edit,['sale_price'=>$data['sale_price'],'unit_cost'=>$u['unit_cost']]);
                if($product_price_log==false){$error=fl();setMessage(66);}

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
        $box_unit_quantity=0;
        if(isset($product_data['box_unit_quantity'])){
            $box_unit_quantity=intval($product_data['box_unit_quantity']);
        }
        $data = array($pUrl=>$pageTitle,'javascript:void()'=>$u[$tpTitle],'1'=>'Edit');
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
                                <?php $general->inputBoxText($tpTitle,$titleFieldName,$u[$tpTitle]);?>
                                <?php $general->inputBoxText('code','Code',@$u['code']);?>
                                <?php
                                    if($use_product_category==1){
                                        $general->inputBoxSelect($categoryData,'Category','category','id','title',$category);
                                        $general->inputBoxSelect($subCategorys[$category],'Sub Category','subCategory','id','title',$u['category_id']);
                                    }
                                ?>
                                <?php $general->inputBoxSelect($units,'Unit','unID','id','title',$u['unit_id']);?>
                                <?php $general->inputBoxSelect($units,'Box unit','box_unit_id','id','title',$u['box_unit_id']);?>
                                <?php $general->inputBoxText('box_unit_quantity','Box unit quantity',$box_unit_quantity);?>
                                <?php $general->inputBoxText('sale_price','TP',$u['sale_price']);?>
                                <?php $general->inputBoxText('VAT','VAT',$u['VAT']);?>
                                <?php
                                    

                                        $general->inputBoxSelect($types,'Type','type','id','title',$u['type']); 

                                    
                                ?>
                                <?php $general->inputBoxText('get_one_free','Get one free',@$get_one_free);?>
                                <?php $general->inputBoxSelect($statuss,'Status','status','id','title',$u['isActive'],haveSelect:'n');  ?>
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
    else if(isset($_GET['archive'])){
        $data = array($pUrl=>$pageTitle,'1'=>'Archive');
        $general->pageHeader('Archive '.$rModule['title'],$data);
    ?>
    <div class="row">
        <div class="col-sm-12">
            <div class="white-box">
                <form method="get">
                    <input type="hidden" name="mdl" value="products">
                    <?php
                        show_msg();
                        $q=['isActive=3'];
                        
                            $general->inputBoxSelectForReport($types,'Type','type','id','title',@$_GET['type']); 
                        ?> 
                        <div class="col-md-2">
                            <h5 class="box-title">Search</h5>
                            <input class="btn btn-success" type="submit" value="Search"/>

                        </div>

                        <?php 
                            if(isset($_GET['type']) && $_GET['type']>-1){
                                $q[]='type='.intval($_GET['type']);
                            }
                        
                        $products=$db->selectAll('products','where '.implode(' and ',$q));

                    ?>
                </form>
                <div class="col-md-12" id="reportArea">
                    <table class="table table-striped table-bordered table-hover">
                        <thead>
                            <tr>

                                <th>SN</th>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Stock</th>
                                <th>Unit</th>
                                <th>Type</th>
                                <th class="amount_td">TP</th>
                                <th class="amount_td">VAT(%)</th>
                                <th>Edit</th>
                            </tr>
                        </thead> 
                        <tbody>
                            <?php
                                $total=1;
                                foreach($products as $u){
                                ?>
                                <tr>

                                    <td><?=$total++?></td>
                                    <td ><?=$u['code']?></td>
                                    <td ><?=$u[$tpTitle]?></td>
                                    <td class="amount_td"><?=(float)$u['stock']?></td>
                                    <td ><?=$units[$u['unit_id']]['title']?></td>  
                                    <td><?=$types[$u['type']]['title']?></td>
                                    <td class="amount_td"><?php echo $general->numberFormat($u['sale_price']);?></td>
                                    <td class="amount_td"><?php echo $general->numberFormat($u['VAT']);?></td>
                                    <td><a href="<?=$pUrl?>&edit=<?=$u[$tpID]?>" class="btn btn-info">Edit</a>
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
    else{
        $general->arrayIndexChange($categorys,'id');
        $data = array($pUrl=>$pageTitle);
        $archived = '<a href="' . $pUrl . '&archive" class="btn btn-info">Archive</a>';
        $general->pageHeader($rModule['name'],$data,$general->addBtnHtml($pUrl).$archived);

    ?>
    <div class="row">
        <div class="col-sm-12">
            <div class="white-box">
                <form method="get">
                    <input type="hidden" name="mdl" value="products">
                    <?php
                        show_msg();
                        $q=['isActive in(1,0)'];
                        if(!empty($types)){
                            $q[]='type in('.implode(',',array_keys($types)).')';
                        }
                        else{
                            $q[]='type=-100';
                        }
                        
                        
                            $general->inputBoxSelectForReport($types,'Type','type','id','title',@$_GET['type']); 
                        ?> 
                        <div class="col-md-2">
                            <h5 class="box-title">Search</h5>
                            <input class="btn btn-success" type="submit" value="Search"/>

                        </div>

                        <?php 
                            if(isset($_GET['type']) && $_GET['type']>-1){
                                $q[]='type='.intval($_GET['type']);
                            }
                        
                        $products=$db->selectAll('products','where '.implode(' and ',$q));
                        $total=1;

                    ?>
                </form>
                <div class="col-md-12" >
                    <table class="table table-striped table-bordered table-hover" id="reportArea">
                        <thead>
                            <tr>

                                <th>SN</th>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Stock</th>
                                <th>Unit</th>
                                <?php
                                    if($can_see_unit_cost==1){
                                    ?><th>Unit cost</th><?php 
                                    }
                                    
                                    
                                    ?><th>Type</th> <?php 
                                    
                                ?>

                                <th class="amount_td">TP</th>
                                <th class="amount_td">VAT(%)</th>
                                <th>Edit</th>
                                <!-- <th>Status</th>-->
                            </tr>
                        </thead> 
                        <tbody>
                            <?php
                                

                                foreach($products as $u){

                                ?>
                                <tr>

                                    <td><?=$total++?></td>
                                    <td ><?=$u['code']?></td>
                                    <td ><?=$u[$tpTitle]?></td>
                                    <td class="amount_td"><?=(float)$u['stock']?></td>
                                    <td ><?=$units[$u['unit_id']]['title']?></td> 
                                    <?php
                                        if($can_see_unit_cost==1){
                                        ?><td class="amount_td"><?=$u['unit_cost']?></td><?php 
                                        }
                                        ?>
                                    
                                    <?php
                                        
                                        ?><td><?=$types[$u['type']]['title']?></td> <?php 
                                        
                                    ?>

                                    <td class="amount_td"><?php echo $general->numberFormat($u['sale_price']);?></td>
                                    <td class="amount_td"><?php echo $general->numberFormat($u['VAT']);?></td>

                                    <td><a href="<?=$pUrl?>&edit=<?=$u[$tpID]?>" class="btn btn-info">Edit</a>
                                    </td>
                                    <!-- <td><?php $general->onclickChangeBTN($u[$tpID],$general->checked($u['isActive']));?></td>-->
                                </tr>
                                <?php
                                }
                            ?>
                        </tbody>

                    </table>
                    <script type="">
                    $(document).ready(function () {
                            $('#reportArea thead th').each(function () {
                                var title = $(this).text();
                            });
                            var table = $('#reportArea').DataTable({
                                "columnDefs": [
                                    {
                                        className: "amount_td",
                                        targets: [4]
                                    }
                                ],
                                paging: false,
                                initComplete: function (){
                                    this.api()
                                    .columns()
                                    .every(function () {
                                        var that = this;
                                        $('input', this.header()).on('keyup change clear', function () {
                                            if (that.search() !== this.value) {
                                                that.search(this.value).draw();
                                            }
                                        });
                                    });
                                },
                            });
                        });
                
                    </script>
                </div>
            </div>
        </div>
    </div>
    <?php
    }
}
?>

