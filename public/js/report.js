function ajax_report_request(requestData, targetElementId = 'reportArea') {
    // Get the first key from the data object
    const firstKey = Object.keys(requestData)[0];
    if (!firstKey) {
        console.error("The data object is empty or invalid.");
        return false;
    }

    

    // Update the target element with a loading image
    const targetElement = $('#' + targetElementId);
    if (!targetElement.length) {
        console.error(`Element with ID '${targetElementId}' not found.`);
        return false;
    }
    targetElement.html(loadingImage);

    // Get the date range value
    const dateRange = $('#dRange').val();
    if (dateRange) {
        requestData['dRange'] = dateRange;
    }

    // Store the requestData in localStorage with the first key as the identifier
    localStorage.setItem(firstKey, JSON.stringify(requestData));

    // Perform the AJAX request
    $.ajax({
        type: 'POST',
        url: ajUrl, // Ensure 'ajUrl' is defined globally or passed to the function
        data: requestData,
        success: function (response) {
            if (response && typeof response.status !== "undefined") {
                if (response.status == 1) {
                    targetElement.html(response.html);
                } else {
                    swMessageFromJs(response.m || "An unknown error occurred.");
                    targetElement.html('');
                }
            } else {
                targetElement.html('');
                swMessage(AJAX_ERROR_MESSAGE || "Unexpected error occurred.");
            }
        },
        error: function (error) {
            console.error("AJAX request failed:", error);
            targetElement.html('');
            swMessage(AJAX_ERROR_MESSAGE || "Error occurred during the AJAX request.");
        }
    });
}
function contra_list(){
    let debit=$('#s_debit').val();
    let credit=$('#s_credit').val();
    ajax_report_request({contra_list:1,debit:debit,credit:credit},'trDetailsArea');
}
function sales_discount_avg_value_sp(){
    ajax_report_request({sales_discount_avg_value_sp:1});
}
function headStatement(){
    const ledger_id   = parse_int($('#ledger_id').val());
    const type   = parse_int($('#type').val());
    ajax_report_request({headStatement:1,ledger_id:ledger_id,type:type})
}
function doctor_visit_list(){
    const base_id = $('#base_id').val();
    const doctor_id = $('#doctor_id').val();
    ajax_report_request({doctor_visit_list:1,base_id:base_id,doctor_id:doctor_id})
}
function doctor_visit_report(){
    const base_id = parse_int($('#base_id').val());
    const doctor_id = parse_int($('#doctor_id').val());
    const type = parse_int($('#type').val());
    ajax_report_request({doctor_visit_report:1,base_id:base_id,doctor_id:doctor_id,type:type})
}
function income_statement(){
    const date_range   = $('#date_range input[name="date_range[]"]').map( function(){return $(this).val(); }).get(); 
    const withoutZero = $('#withoutZero').is(':checked') ? 1 : 0;
    ajax_report_request({income_statement:1,date_range:date_range,withoutZero:withoutZero});
}
function statement_of_comprehensive_income(){
    const date_range   = $('#date_range input[name="date_range[]"]').map( function(){return $(this).val(); }).get(); 
    ajax_report_request({statement_of_comprehensive_income:1,date_range:date_range});
}
function trial_balance(){
    const date_range   = $('#date_range input[name="date_range[]"]').map( function(){return $(this).val(); }).get(); 
    ajax_report_request({trial_balance:1,date_range:date_range});
}
function income_expense_list(type){
    const hID=$('#shID').val();
    const uID=$('#uID').val();
    const caID=$('#charts_accounts_id').val();
    ajax_report_request({income_expense_list:1,hID:hID,uID:uID,type:type,caID:caID});
}
function general_ledger_summary(){
    const type = $('#type').val();
    //is checked without_zero_transaction then set veriable without_zero_transaction=1 or zero
    const without_zero = $('#without_zero').is(':checked') ? 1 : 0;
    const without_zero_transaction = $('#without_zero_transaction').is(':checked') ? 1 : 0;
    const only_cash_chart = $('#only_cash_chart').is(':checked') ? 1 : 0;
    ajax_report_request({general_ledger_summary:1,type,without_zero,without_zero_transaction,only_cash_chart});
}
function customer_statement(){
    const customer_id   = parse_int($('#customer_id').val());
    const customer_category_id   = parse_int($('#customer_category_id').val());
    const base_id   = parse_int($('#base_id').val());
    const bazar_id   = parse_int($('#bazar_id').val());
    const type   = $('#type').val();
    const type_zero   = $('#type_zero').val();
    const column_zero=$('#column_zero').val();
    ajax_report_request({customer_statement:1,base_id,customer_category_id,bazar_id,customer_id,type,type_zero,column_zero});
}
function sales_report(){
    const with_tp  = $('#with_tp').val();
    const toll_sale_type  = $('#toll_sale_type').val();
    const toll_base_type=$('#toll_base_type').val();
    ajax_report_request({sales_report:1,with_tp:with_tp,toll_sale_type:toll_sale_type,toll_base_type:toll_base_type});
}
function customer_visit_list(){
    const base_id = $('#base_id').val();
    const customer_id = $('#customer_id').val();
    ajax_report_request({customer_visit_list:1,base_id:base_id,customer_id:customer_id})
}
function paid_and_unpaid_invoice_report(){
    const base_id = $('#base_id').val();
    const customer_id = $('#customer_id').val();
    const paid_type = $('#paid_type').val();
    const pay_type = $('#pay_type').val();
    ajax_report_request({paid_and_unpaid_invoice_report:1,base_id:base_id,customer_id:customer_id,paid_type:paid_type,pay_type:pay_type})
}
function account_payable_summary(){
    const supplier_id = $('#supplier_id').val();
    ajax_report_request({account_payable_summary:1,supplier_id:supplier_id})
}
 function recoverable_due_list(){
    const employee_id = parse_int($('#employee_id').val());
    const customer_id = parse_int($('#customer_id').val());
    const zero_type = parse_int($('#zero_type').val());
    const base_id = parse_int($('#base_id').val());
    const report_type = parse_int($('#report_type').val());
    ajax_report_request({recoverable_due_list:1,employee_id:employee_id,customer_id:customer_id,zero_type:zero_type,report_type:report_type,base_id:base_id});
}
 function supplier_list(){
    ajax_report_request({supplier_list:1});
}