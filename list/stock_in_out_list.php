<?php
    $general->pageHeader($rModule['title']);
    $products=$db->getProductData('and isActive in(0,1)');
    $general->arraySortByColumn($products,'t');
    $types=$smt->get_all_product_type();
    $types[]=[
        'id'=>-1,
        'title'=>'All'
    ];
    $general->arraySortByColumn($types,'id');
?>

<div class="row">
    <div class="col-sm-12">
        <div class="white-box">
            <div class="row">
                <div class="col-sm-12 col-lg-12">   
                    <div class="col-md-2">
                        <h5 class="box-title">Date</h5>
                        <input type="text" id="dRange" class="daterangepickerMulti form-control" value="">
                    </div>                
                    <?php
                        $general->inputBoxSelectForReport($products,'Product','product_id','id','t');
                        $general->inputBoxSelectForReport($types,'Type','type','id','title','','','',false);
                    ?>

                    <div class="col-md-2">
                        <h5 class="box-title">Search</h5>
                        <button class="btn btn-success" onclick="reject_list()">Search</button>
                        <script type="text/javascript">
                            function reject_list(){
                                let product_id   = parse_int($('#product_id').val());
                                let type   = parse_int($('#type').val());
                                let request = {
                                    reject_list:1,
                                    product_id: product_id,
                                    stock_in_out_type:stock_in_out_type,
                                    type: type
                                };
                                ajax_report_request(request,'reportArea');
                            }
                            function rejectDelete(id) {
                                const buttonId = `rejectDelete_${id}`; // Button ID for loading state management

                                // Start button loading state
                                buttonLoading(buttonId);

                                // Validate the ID
                                if (!id) {
                                    console.error("The 'id' parameter is required for rejectDelete.");
                                    swMessage("Invalid ID provided.");
                                    button_loading_destroy(buttonId, 'Delete');
                                    return false;
                                }

                                // Construct the request data
                                const request = { rejectDelete: 1,stock_in_out_type:stock_in_out_type, id: id };

                                // Perform the AJAX request
                                $.ajax({
                                    type: 'POST',
                                    url: ajUrl, // Ensure `ajUrl` is defined globally or passed in context
                                    data: request,
                                    success: function (response) {
                                        // End button loading state
                                        button_loading_destroy(buttonId, 'Delete');

                                        if (response && typeof response.status !== 'undefined') {
                                            if (response.status == 1) {
                                                // Remove the specific element with the given ID
                                                $('#reject_' + id).remove();
                                                setTimeout(() => {swMessageFromJs(response.m || "Item successfully deleted.");}, 100);
                                                
                                            } else {
                                                setTimeout(()=>{swMessageFromJs(response.m || "Operation failed. Please try again.");}, 100);
                                            }
                                        } else {
                                            setTimeout(()=>{swMessage(AJAX_ERROR_MESSAGE || "Unexpected error occurred.");}, 100);
                                        }
                                    },
                                    error: function (xhr, status, error) {
                                        // End button loading state
                                        button_loading_destroy(buttonId, 'Delete');

                                        // Log the error to the console
                                        console.error("AJAX request failed:", error);

                                        // Show error message to the user
                                        setTimeout(()=>{swMessage(AJAX_ERROR_MESSAGE || "Error occurred during the AJAX request.");}, 100);
                                    }
                                });

                                return true;
                            }
                            
                            $(document).ready(function(){
                                // const report_data = JSON.parse(localStorage.getItem('reject_list_data'+stock_in_out_type));
                                // if(report_data){
                                //     if(report_data.hasOwnProperty('dRange')){
                                //         $('#dRange').val(report_data.dRange);
                                //         if(report_data.product_id>0){
                                //             $('#product_id').val(report_data.product_id);
                                //         }
                                        
                                //         if(report_data.type!=''){
                                //             $('#type').val(report_data.type);
                                //         }
                                //         select2Call();
                                //     }
                                // }
                                reject_list();
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
