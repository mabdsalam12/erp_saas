<?php
	$suppliers=$db->selectAll('suppliers','where isActive=1 order by supName asc');

	$general->pageHeader($rModule['title']);
?>
<div class="row">
	<div class="col-sm-12">
		<div class="white-box">
			<div class="row">
				<div class="col-sm-12 col-lg-12">
					<div class="col-md-2">
						<h5 class="box-title">Date</h5>
						<input type="text" id="dRange" class="daterangepickerMulti form-control" value="<?php echo date('d-m-Y').' to '.date('d-m-Y');?>">
					</div>
					<div class="col-md-2">
						<h5 class="box-title">Supplier</h5>
						<select id='supID' class="form-control select2">
							<option value="">All Supplier</option>
							<?php
								foreach($suppliers as $sup){
							?>
								<option value="<?php echo $sup['supID'];?>"><?php echo $sup['supName'];?></option>
							<?php
								}
							?>
						</select>
					</div>
					<div class="col-md-2">
						<h5 class="box-title">DSR</h5>
						<select id='dsr' class="form-control select2">
							<option value="">All DSR</option>
							<?php
								foreach($dsrs as $sup){
							?>
								<option value="<?php echo $sup['eID'];?>"><?php echo $sup['eName'];?></option>
							<?php
								}
							?>
						</select>
					</div>
					<div class="col-md-2">
						<h5 class="box-title">WP</h5>
						<select id='wp' class="form-control">
							<option value="0">Without profit</option>
							<option value="1">With profit</option>
						</select>
					</div>
					<div class="col-md-2">
						<h5 class="box-title">Search</h5>
						<a href="javascript:void()" class="btn btn-success" onclick="saleReport()">Search</a>
						<script type="text/javascript">
							$(document).ready(function(){

								
								saleReport();
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