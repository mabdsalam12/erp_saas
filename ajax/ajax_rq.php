<?php

    $jArray = ['status'=>0,'m'=>[]];
    $gAr    = [];
    $dir = __DIR__;
    $runTimes=array();
    $runTimes[__LINE__]=date('i:s').' '.microtime();
    if(isset($_GET['stch']))                                {include("system/stch.php");}
    elseif(isset($_POST['change_premessions']))             {include("system/change_premessions.php");}
    elseif(isset($_GET['loginVerifyComplete']))             {include("system/loginVerifyComplete.php");}
    elseif(isset($_POST['get_premessions']))                {include("system/get_premessions.php");}
    elseif(isset($_POST['copyPermissionFrom']))             {include("system/copyPermissionFrom.php");}
    elseif(isset($_POST['moduleOrder']))                    {include("system/moduleOrder.php");}
    elseif(isset($_POST['reportJsonToExcel']))              {include("reportJsonToExcel.php");}
    elseif(isset($_POST['notficationDetailsLoad']))         {include("notification/notficationDetailsLoad.php");}
    elseif(isset($_POST['employeeSalaryInfo']))             {include("account/employeeSalaryInfo.php");}
    elseif(isset($_POST['payEmployeeSalary']))              {include("account/payEmployeeSalary.php");}
    else{
        $requestProcess=[
            'sms_template_set'              => "/system/sms_template_set.php",
            'product_reject_entry'          => "/operation/inventory/product_reject_entry.php",
            'rejectDelete'                  => "/operation/inventory/rejectDelete.php",

            'voucher_details'               => "/account/voucher/voucher_details.php",
            'employeeDiductionUpdate'       => "/account/employeeDiductionUpdate.php",
            'getSupplierDue'                => "/account/getSupplierDue.php",
            'supNewTransaction'             => "/account/supNewTransaction.php",
            'cashFlowLoad'                  => "/account/cashFlowLoad.php",
            'income_expense_list'           => "/account/income_expense_list.php",
            'headForIncExpSet'              => "/account/headForIncExpSet.php",
            'voucherRemove'                 => "/account/voucherRemove.php",
            'voucher_list'                  => "/account/voucher_list.php",
            'recoverable_due_list'          => "/account/recoverable_due_list.php",
            'recoverableDelete'             => "/account/recoverableDelete.php",
            'save_employee_salary'          => "/account/save_employee_salary.php",
            'doctor_honurium_add'           => "/account/doctor_honurium_add.php",


            'new_sale'                      => "/operation/sale/new_sale.php",
            'productsSaleDraft'             => "/operation/sale/productsSaleDraft.php",
            'saleDraftAdd'                  => "/operation/sale/saleDraftAdd.php",
            'saleEdit'                      => "/operation/sale/saleEdit.php",





            'saleReturnInit'                => "/operation/return/saleReturnInit.php",
            'purchaseReturnInit'            => "/operation/return/purchaseReturnInit.php",
            'saleReturn'                    => "/operation/return/saleReturn.php",
            'purchaseReturn'                => "/operation/return/purchaseReturn.php",

            

            'productReturnAdd'              => "/operation/productReturnAdd.php",
            'purchaseDraftAdd'              => "/operation/purchaseDraftAdd.php",
            'productReturnPayCollect'       => "/operation/productReturnPayCollect.php",
            'productUnitCostUpdate'         => "/operation/productUnitCostUpdate.php",
            
            'get_monthly_bill_details'      => "/operation/system/get_monthly_bill_details.php",
            'pay_monthly_bill'              => "/operation/system/pay_monthly_bill.php",
            
            'supplierStatement'              => "/report/supplierStatement.php",
            'supHistoryReport'              => "/report/supHistoryReport.php",
            'employeeDueStatment'           => "/report/employeeDueStatment.php",
            'suuplierSummery'               => "/report/suuplierSummery.php",
            'purStokStatmant'               => "/report/purStokStatmant.php",
            'productsReport'                => "/report/productsReport.php",
            'commissionReport'              => "/report/commissionReport.php",
            'suplayerClosingReport'         => "/report/suplayerClosingReport.php",
            'protibedon'                    => "/report/protibedon.php",
            'employeeCommissionStatment'    => "/report/employeeCommissionStatment.php",

            'suplierStatment'               => "/report/suplierStatment.php",
            'employeeSalaryReport'          => "/report/employeeSalaryReport.php",
            'dealerDueCollect'              => "/report/dealerDueCollect.php",
            'memberStatment'                => "/report/memberStatment.php",
            'loneReport'                    => "/report/loneReport.php",
            'specialInvestReport'           => "/report/specialInvestReport.php",
            'specInvDetails'                => "/report/specInvDetails.php",
            'supplierWisePurchaseReport'    => "/report/supplierWisePurchaseReport.php",
            'userPurchaseReport'            => "/report/userPurchaseReport.php",
            'item_wise_sale_report'         => "/report/item_wise_sale_report.php",
            'sales_report'                  => "/report/sales_report.php",
            'get_supplier_transactions'                  => "/report/get_supplier_transactions.php",
            
            'pharmacy_wise_doctors_contribution'=> "/report/pharmacy_wise_doctors_contribution.php",
            'doctors_wise_contribution'     => "/report/doctors_wise_contribution.php",
            'doctor_honurium_list'          => "/list/doctor_honurium_list.php",


            'saleDraftList'                 => "/list/saleDraftList.php",
            'loadSupProducts'               => "/list/loadSupProducts.php",
            'reject_list'                   => "/list/reject_list.php",
            'monthly_target_list'           => "/list/monthly_target_list.php",
            'productReturnList'             => "/list/productReturnList.php",
            'personList'                    => "/list/personList.php",
            'saleReturnList'                => "/list/saleReturnList.php",
            'purchaseReturnList'            => "/list/purchaseReturnList.php",
            'productsSaleDraftList'         => "/list/productsSaleDraftList.php",
            
            
            'income_and_expense_add'        => "/account/income_and_expense_add.php",
            'products_sale_update'          => "/operation/sale/sale_update.php",
            
            'manufacture_add'               => "/operation/production/production_add.php",
            'production_packaging_add'      => "/operation/production/production_add.php",
            'manufacture_edit'              => "/operation/production/production_manufacture_edit.php",
            'packaging_edit'                => "/operation/production/production_manufacture_edit.php",
            'production_details_view'       => "/operation/production/production_details_view.php",
            'production_delete'             => "/operation/production/production_delete.php",

            'sale_return_entry'             => "/operation/return/sale_return_entry.php", 
            'salse_return_details_view'     => "/operation/return/salse_return_details_view.php", 
            'salse_return_process_init'     => "/operation/return/salse_return_process_init.php", 
            'sale_return_process'           => "/operation/return/sale_return_process.php", 
            'sale_return_delete'            => "/operation/return/sale_return_delete.php", 
            'salse_details_view'            => "/operation/sale/salse_details_view.php", 
            'sale_delete'                   => "/operation/sale/sale_delete.php", 
            'purchase_return_entry'         => "/operation/return/purchase_return_entry.php", 
            'purchaseAdd'                   => "/operation/purchaseAdd.php",

            'purchase_update'               => "/purchase/update.php",
            'purchase_delete'               => "/purchase/purchase_delete.php",
            'purchase_details_view'         => "/purchase/purchase_details_view.php",
            'purchase_order_details_view'   => "/purchase/purchase_order_details_view.php",
            'purchase_requisition_details_view'=> "/purchase/purchase_requisition_details_view.php", 
            'purchase_order_list'           => "/purchase/purchase_order_list.php",
            'purchase_requisition_list'     => "/purchase/purchase_requisition_list.php",


            'customerCurrentBalance'        => "/customer/customerCurrentBalance.php",
            'customer_transaction'          => "/customer/customer_transaction.php",
            'customerPaymentList'           => "/customer/customerPaymentList.php",
            'getCustomerBalance'            => "/customer/getCustomerBalance.php",
            'customer_transaction_remove'   => "/customer/customer_transaction_remove.php",
            'customer_collection_report'   => "/customer/customer_collection_report.php",
            'customer_collection_action'   => "/customer/customer_collection_action.php",
            'zero_balance_customer_list'   => "/customer/zero_balance_customer_list.php",
            'send_customer_closing_sms'    => "/customer/send_customer_closing_sms.php",


            'customer_statement'            => "/statement/customer_statement.php",
            'headStatement'                 => "/statement/headStatement.php",
            'product_statement'             => "/statement/product_statement.php",

            'gift_distribute_remove'        => "/gift_distribute/gift_distribute_remove.php",
            'gift_distribute'               => "/gift_distribute/gift_distribute.php",
            'gift_distribute_list'          => "/gift_distribute/gift_distribute_list.php",
            'gift_distribute_details_view'  => "/gift_distribute/gift_distribute_details_view.php",

            'personStatment'                => "/person/personStatment.php",
            'transactionWithPerson'         => "/person/transactionWithPerson.php",
            'personCurrentBalance'          => "/person/personCurrentBalance.php",
            'personPaymentList'             => "/person/personPaymentList.php",
            'saleAndPurchase'               => "/dashboard/saleAndPurchase.php",
            
            'transactionWithEmployee'       => "/employee/transactionWithEmployee.php",
            'employeePaymentList'           => "/employee/employeePaymentList.php",
            'employeeStatement'              => "/employee/employeeStatement.php",
            'employeeCurrentBalance'        => "/employee/employeeCurrentBalance.php",
            
            'purchase_requisition_add'      => "/operation/purchase_requisition_add.php",
            'purchase_order_add'            => "/operation/purchase_order_add.php",
            
            'supplier_list'                 => "/list/supplier_list.php",
            'production_list'               => "/list/production_list.php", 
            'sale_retun_list'               => "/list/sale_retun_list.php", 
            'sale_list'                     => "/list/sale_list.php",
            'saleEditList'                  => "/list/saleEditList.php",
            'recoverable_collection_report' => "/report/recoverable_collection_report.php",
            'credit_note_report'            => "/report/credit_note_report.php",
            'sales_discount_avg_value_sp'   => "/report/sales_discount_avg_value_sp.php",
            'return_damage_expiry_repor'    => "/report/return_damage_expiry_repor.php",
            'product_report'                => "/report/product_report.php",
            'customerList'                  => "/list/customerList.php",
            'typed_wise_product'            => "/operation/typed_wise_product.php",  
            'purchaseReport'                => "/list/purchaseReport.php",
            'honorarium_evaluation_list'    => "/list/honorarium_evaluation_list.php",
            'bazar_list'                     => "/list/bazar_list.php",
            'contra_list'                   => "/list/contra_list.php",
            'customer_closing_sms_list'     => "/list/customer_closing_sms_list.php",
            
            'mpo_deposit_request'           => "/account/mpo_deposit_request.php",
            'mpo_deposit_action'            => "/account/mpo_deposit_action.php",
            'user_balance_retport'          => "/account/user_balance_retport.php",
            'voucher_entry'                 => "/account/voucher_entry.php",
            'contra_voucher_add'            => "/account/contra_voucher_add.php",
            'contra_voucher_edit'           => "/account/contra_voucher_edit.php",
            'contra_voucher_delete'           => "/account/contra_voucher_delete.php",


            'salary_payment_report'         => "/report/salary_payment_report.php",
            'user_statment'                 => "/report/user_statment.php",
            'doctor_list'                   => "/report/doctor_list.php",
            'prescription_survey_report'    => "/report/prescription_survey_report.php",
            'income_statement'              => "/report/income_statement.php",
            'trial_balance'                 => "/report/trial_balance.php",
            'general_ledger_summary'        => "/report/general_ledger_summary.php",
            'paid_and_unpaid_invoice_report'=> "/report/paid_and_unpaid_invoice_report.php",
            'salary_payment_data'           => "/report/salary_payment_data.php",
            'account_payable_summary'       => "/report/account_payable_summary.php",
            'statement_of_comprehensive_income'=> "/report/statement_of_comprehensive_income.php",
            
            'customer_visit_list'           => "/visit/customer_visit_list.php",
            'customer_visit_details_view'   => "/visit/customer_visit_details_view.php",
            'bazar_visit_list'              => "/visit/bazar_visit_list.php",
            'bazar_visit_details_view'      => "/visit/bazar_visit_details_view.php",
            'doctor_visit_list'             => "/visit/doctor_visit_list.php", 
            'doctor_visit_report'           => "/visit/doctor_visit_report.php", 

            'doctor_visit_details_view'     => "/visit/doctor_visit_details_view.php",
            'user_assign_data'              => "/user/user_assign_data.php",
            'base_assign'                   => "/user/base_assign.php",
            'get_fixed_assets'              => "/fixed_assets/get_fixed_assets.php",
            'depreciation_save'             => "/fixed_assets/depreciation_save.php",
            'get_depreciation_history'      => "/fixed_assets/get_depreciation_history.php",
            
            'message_save'                  => "/message/message_save.php",
            'getMessagePending'             => "/message/getMessagePending.php",


        ];
        foreach($requestProcess as $k=>$rp){
            if(isset($_POST[$k])){
                $include1=$rp;
                break;
            }
        }
        if(isset($include1)){
            include __DIR__.$include1;
        }
        else{
            setMessage(1,'Invalid request');
        }
        if(empty($jArray['m'])){
            if(isset($error)){setErrorMessage($error);}
            $jArray['m']=show_msg('y');
        }
        $general->jsonHeader($jArray);
    }