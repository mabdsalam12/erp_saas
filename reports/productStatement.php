<?php
    $pID=intval(@$_GET['pID']);
    $general->pageHeader($rModule['title']);
    $dRange = date('d-m-Y').' to '.date('d-m-Y');
?>
<div class="white-box border-box">
    <div class="row">
        <div class="col-md-12">
            <div class="col-sm-12 col-md-12 col-lg-12">
                <div class="col-md-2">
                    <h5 class="box-title">Date</h5>
                    <input type="text" id="dRange" class="daterangepickerMulti form-control" value="<?php echo $dRange;?>">
                </div>
                <div class="col-md-4">
                    <h5 class="box-title">Products</h5>
                    <select id="product_id" class="col-md-8 form-control select2">
                        <option value="">Select Product</option>
                        <?php
                            $products=$db->selectAll('products','where isActive=1 order by title asc');
                            if(!empty($products)){
                                foreach($products as $e){
                        ?><option value="<?php echo $e['id'];?>" <?php echo $general->selected($e['id'],$pID);?>><?php echo $e['title'];?></option><?php
                                }
                            }
                        ?>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <h5 class="box-title">Search</h5>
                    <a href="javascript:void()" class="btn btn-success" onclick="product_statement()">Search</a>
                </div>
            </div> 
            <script type="text/javascript">
                $(document).ready(function(){
                    product_statement();
                });
            </script>
        </div>

        <div class="col-sm-12 col-lg-12"><?php show_msg();?></div>
        <div class="col-sm-12 col-lg-12" id="reportArea"></div>
    </div>
</div>