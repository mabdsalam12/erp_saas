<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="">
	<meta name="author" content="">
	<link rel="icon" href="<?=URL?>/favicon.php" type="image/x-icon">
	<!--<link rel="icon" type="image/x-icon" href="<?=URL?>/favicon.ico">-->
	<title><?php echo $thisPageTitle; ?></title>
	<link href="<?=URL?>/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="<?=URL?>/plugins/bootstrap-extension/css/bootstrap-extension.css" rel="stylesheet">
	<link href="<?=URL?>/plugins/sidebar-nav/dist/sidebar-nav.min.css" rel="stylesheet">
	<link href="<?=URL?>/plugins/morrisjs/morris.css" rel="stylesheet">
	<link href="<?=URL?>/plugins/bootstrap-daterangepicker/daterangepicker.css" rel="stylesheet">
	<link href="<?=URL?>/plugins/custom-select/custom-select.css" rel="stylesheet" type="text/css" />
	<link href="<?=URL?>/css/font/font.css" rel="stylesheet">
	<link href="<?=URL?>/css/style.css" rel="stylesheet">
	<link href="<?=URL?>/css/colors/default.css" id="theme" rel="stylesheet">
	<link href="<?=URL?>/css/cw-style.css?v=<?php echo RUNNING_VERSION; ?>" id="theme" rel="stylesheet">
	<script src="<?=URL?>/plugins/jquery/dist/jquery.min.js"></script>
	<script src="<?=URL?>/plugins/jqueryui/jquery-ui.min.js"></script>
	<script src="<?=URL?>/bootstrap/dist/js/tether.min.js"></script>
	<script src="<?=URL?>/bootstrap/dist/js/bootstrap.min.js"></script>
	<script src="<?=URL?>/plugins/bootstrap-extension/js/bootstrap-extension.min.js"></script>
	<script src="<?=URL?>/plugins/sidebar-nav/dist/sidebar-nav.min.js"></script>
	<link href="<?=URL?>/plugins/sweetalert/sweetalert.css" rel="stylesheet" type="text/css">
	<link href="<?=URL?>/plugins/datatables/jquery.dataTables.min.css" rel="stylesheet" type="text/css" />
	<script src="<?=URL?>/js/big.min.js"></script>
	<script src="<?=URL?>/js/custom.min.js"></script>
	<script src="<?=URL?>/plugins/custom-select/custom-select.min.js" type="text/javascript"></script>
	<script src="<?=URL?>/plugins/bootstrap-daterangepicker/moment.min.js"></script>
	<script src="<?=URL?>/plugins/bootstrap-daterangepicker/daterangepicker.js"></script>
	<script src="<?=URL?>/plugins/sweetalert/sweetalert.min.js"></script>
	<script src="<?=URL?>/plugins/sweetalert/jquery.sweet-alert.custom.js"></script>
	<script src="<?=URL?>/plugins/datatables/jquery.dataTables.min.js"></script>
	<script src="<?=URL?>/plugins/tiny-editable/mindmup-editabletable.js"></script>
	<script src="<?=URL?>/js/jquery.slimscroll.js?v=3"></script>
    <script src="<?=URL?>/js/jscolor.min.js"></script>
	<script type="text/javascript">
		const ajUrl                   = '<?php echo URL; ?>/?ajax=1';
		const pUrl                    = '<?php echo $pUrl; ?>';
		const MODULE_URL              = '<?=@MODULE_URL?>';
		const CURRENT_MODULE          = '<?=@$_GET[MODULE_URL]?>';
		const productHideBtn          = 1;
        const AJAX_ERROR_MESSAGE      = 'Some problem there. Please try again later';
		const MAIN_URL				  = '<?=URL?>';
		$(document).ready(function() {
			$('#loadingImage').html(loadingImage);
			$('.daterangepicker').daterangepicker({
				singleDatePicker: true,
				minDate: '06/01/1950',
				maxDate: '12/12/2050',
				locale: {
					format: 'DD-MM-YYYY'
				},
				showDropdowns: true
				}, function(start, end, label) {
					//console.log(start.toISOString(), end.toISOString(), label);
			});
			$('.daterangepickerMulti').daterangepicker({
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
			$('.daterangepicker_e').daterangepicker({
				singleDatePicker: true,
				minDate: '06/01/1950',
				autoUpdateInput: false,
				maxDate: '12/12/2050',
				locale: {
					format: 'DD-MM-YYYY',
					cancelLabel: 'Clear'
				},
				showDropdowns: true
				}, function(start, end, label) {
					//console.log(start.toISOString(), end.toISOString(), label);
			});

			$('.daterangepicker_e').on('apply.daterangepicker', function(ev, picker) {
				$(this).val(picker.startDate.format('DD-MM-YYYY'));
			});
			$(".show-btn").click(function() {
				$(this).parents("tr").next().toggleClass("tcls");
			}); //plus button
			select2Call();
			$(".slimscrollsidebar").slimScroll({height:"100%",position:"right",size:"5px",color:"#dcdcdc"})

		});
		function reportJsonToExcel(fileNameForExp){
			$('#exportBtn').html('Wait');
			$.ajax({
				type: "POST",
				url: ajUrl,
				data: {
					reportJsonToExcel: fileNameForExp
				},
				success: function(data) {
					if (data.status == 1) {
						$('#exportBtn').html('Export');
						window.location = data.link;
					}
				}

			})

		}
	</script>
	<script type="text/javascript" src="js/common.js?v=<?php echo RUNNING_VERSION; ?>"></script>
	<script type="text/javascript" src="js/custom.js?v=<?php echo RUNNING_VERSION; ?>"></script>
	<script type="text/javascript" src="js/report.js?v=<?php echo RUNNING_VERSION; ?>"></script>
	<script type="text/javascript" src="js/wiseInfoLoad.js?v=<?php echo RUNNING_VERSION; ?>"></script>
	<script type="text/javascript" src="js/operation.js?v=<?php echo RUNNING_VERSION; ?>"></script>
	<script type="text/javascript" src="js/somiti.js?v=<?php echo RUNNING_VERSION; ?>"></script>
	<?php
		$logoUrl=URL.'/images/'.PROJECT.'/logo_small.png';
	?>
	<style>

		.navbar-header {<?php echo 'background: #043D5E;';?>}
		.navbar-header a,.adm-logo-title{<?php echo 'color: #FFFFFF;';?>}
	</style>
</head>

<body class="fix-sidebar">
<div class="preloader">
	<div class="cssload-speeding-wheel"></div>
</div>
<div id="wrapper">

<?php
	if (defined('LOGIN_SESSION_ID')) {
		include(__DIR__."/left_nav.php");
	}
?>
<div id="page-wrapper">
<div class="container-fluid">
<div class="scroolbody">