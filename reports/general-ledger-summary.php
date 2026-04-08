<?php
	$general->pageHeader($rModule['title']);
?>
<div class="white-box border-box">
	<div class="row">
		<div class="col-md-12">
			<div class="col-sm-12 col-md-12 col-lg-12">
				<div class="col-md-3" >
					<h5 class="box-title">&nbsp;</h5>
					<input type="text" id="dRange" class="daterangepickerMulti form-control" value="">
				</div>
                <?php 
				$ledger_types =$acc->get_ledger_types();
				$general->inputBoxSelectForReport($ledger_types,'Type','type','id','title');
				?>
			
				<div class="col-md-2">
					<h5 class="box-title"><label for="only_cash_chart">Only Cash chart</label></h5>
					<input type="checkbox" id="only_cash_chart" class="form-control">
				</div>
				<div class="col-md-2">
					<h5 class="box-title"><label for="without_zero">Without zero</label></h5>
					<input type="checkbox" id="without_zero" class="form-control">
				</div>
				<div class="col-md-2">
					<h5 class="box-title"><label for="without_zero_transaction">Without zero transaction</label></h5>
					<input type="checkbox" id="without_zero_transaction" class="form-control">
				</div>
				<div class="col-md-2">
					<h5 class="box-title">Search</h5>
					<button class="btn btn-success" id="report-button">Search</button>
				</div>
			</div>
			<script>
                $('#report-button').click(function(){general_ledger_summary();})
                
			</script>
		</div>


		<div class="col-sm-12 col-lg-12"><?php show_msg();?></div>
		<div class="col-sm-12 col-lg-12" id="reportArea"></div>
	</div>
</div>