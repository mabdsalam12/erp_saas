<?php
	$general->pageHeader($rModule['name']);
?>
<div class="white-box border-box">
	<div class="row">
		<div class="col-md-12">
			<div class="col-sm-12 col-md-12 col-lg-12" id="date_range">
				<div class="col-md-3" >
					<h5 class="box-title">&nbsp;</h5>
					<input type="text" name="date_range[]" class="daterangepickerMulti form-control" value="">
				</div>
                <div id="add-date-range-area"></div>
				<div class="col-md-2">
               
                <h5 class="box-title"> &nbsp;</h5>
					<button class="btn btn-success" id="add-date-range">Add</button>
				</div>
				<div class="col-md-2">
					<h5 class="box-title">Search</h5>
					<button class="btn btn-success" id="report-button">Search</button>
				</div>
			</div>
			<script>
                $('#report-button').click(function(){trial_balance();})
                $('#add-date-range').click(function(){
                    let html = $("#add-date-range-div").html();
                    const id =Math.floor(Math.random()*10000);
                    $('#add-date-range-area').append(`<div class="col-md-3" id="${id}">${html}</div>`);
                    $(`#${id} .daterangepickerMulti`).daterangepicker({
                        minDate: '06/01/1950',
                        autoApply: true,
                        separator: " to ",
                        maxDate: '12/12/2050',
                        locale: {
                            format: 'DD-MM-YYYY',
                            separator: " to "
                        },
                        ranges: {
                            'Today': [moment(), moment()],
                            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                            'This Month': [moment().startOf('month'), moment().endOf('month')],
                            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                            'Last 6 Month': [moment().subtract(6, 'month').startOf('day'), moment().endOf('day')],
                            'Last 2 Year': [moment().subtract(24, 'month').startOf('day'), moment().endOf('day')]
                        },
                        alwaysShowCalendars: true,
                        showDropdowns: true,
                        opens: "center"
                        }, function(start, end, label) {
                            //console.log(start.toISOString(), end.toISOString(), label);
                    });
                    $(`#${id} .btn-danger`).click(function(){$(`#${id}`).remove();})
                })
			</script>
		</div>
        <div class="col-md-3" id="add-date-range-div" style="display: none;">
            <h5 class="box-title">&nbsp;</h5>
            <div class="input-group">
                <input type="text" name="date_range[]" class="daterangepickerMulti form-control" value="">
                <button class="btn btn-danger">x</button>
            </div>
        </div>

		<div class="col-sm-12 col-lg-12"><?php show_msg();?></div>
		<div class="col-sm-12 col-lg-12" id="reportArea"></div>
	</div>
</div>