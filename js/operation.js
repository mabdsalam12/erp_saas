var contra_voucher={
    list(){
        let debit=$('#s_debit').val();
        let credit=$('#s_credit').val();
        ajax_report_request({contra_list:1,debit:debit,credit:credit},'trDetailsArea');
    },
    validation(){
        const date              = $('#date').val();
        const base_id           =parse_int( $('#base_id').val());
        const debit             =parse_int( $('#debit').val());
        const credit            =parse_int($('#credit').val());
        const amount            =parse_float($('#amount').val());
        const reference         = $('#reference').val();
        const transaction_charge= parse_float($('#transaction_charge').val());
        const note              = $('#note').val();
        if(debit<1){
            swMessage('Please select debit account');
            return false;
        }
        if(credit<1){
            swMessage('Please select credit account');
            return false;
        }
        if(amount<=0){
            swMessage('Please enter amount');
            return false;
        }
        const post_data = {
            date                : date,
            base_id             : base_id,
            debit               : debit,
            credit              : credit,
            amount              : amount,
            reference           : reference,
            transaction_charge  : transaction_charge,
            note                : note
        };
        return post_data;
    },
    add(){
        buttonLoading('add_button');
        const post_data = this.validation();
        post_data['contra_voucher_add'] = 1;
        let self = this;
        if(post_data){
            $.ajax({
                type:'post',
                url:ajUrl,
                data:post_data,
                success:function(data){
                    
                    if(typeof(data.status)  !== "undefined"){ 
                        if(data.status==1){
                            self.clear_form();
                        }
                        swMessageFromJs(data.m);
                    }   
                    else{
                        swMessage(AJAX_ERROR_MESSAGE);    
                    }
                    button_loading_destroy('add_button','Add');
                },
                error:function(data){
                    button_loading_destroy('add_button','Add');
                    swMessage(AJAX_ERROR_MESSAGE);  
                }
            });
        }
    },
    edit(){
        buttonLoading('edit_button');
        let post_data = false;
        const id = parse_int($('#id').val());
        if(id<1){
            swMessage('Invalid request');
        }
        else{
            post_data = this.validation();
            post_data['contra_voucher_edit'] = 1;
            post_data['id'] = id;
        }
        if(post_data){
            $.ajax({
                type:'post',
                url:ajUrl,
                data:post_data,
                success:function(data){
                    if(typeof(data.status)  !== "undefined"){ 
                        swMessageFromJs(data.m);
                    }   
                    else{
                        swMessage(AJAX_ERROR_MESSAGE);    
                    }
                    button_loading_destroy('edit_button','Update');
                },
                error:function(data){
                    button_loading_destroy('edit_button','Update');
                    swMessage(AJAX_ERROR_MESSAGE);  
                }
            });
        }
        else{
            button_loading_destroy('edit_button','Update');
        }
    },
    clear_form(){
        $('#base_id').val(0);
        $('#debit').val('');
        $('#credit').val('');
        $('#amount').val('');
        $('#reference').val('');
        $('#transaction_charge').val('');
        $('#note').val('');
        select2Call();
    },
    delete(id){
        are_you_sure(1,'Are you sure to delete this?',id,function(id){
            buttonLoading(`delete_${id}`);
            $.ajax({
                type:'post',
                url:ajUrl,
                data:{contra_voucher_delete:1,id:id},
                success:function(data){
                    if(typeof(data.status)  !== "undefined"){ 
                        if(data.status==1){
                            $('#contra_voucher_'+id).remove();
                        }
                        setTimeout(function(){
                            swMessageFromJs(data.m);
                        },100);
                    }   
                    else{
                        setTimeout(function(){
                            swMessage(AJAX_ERROR_MESSAGE);  
                        },100);
                    }
                    button_loading_destroy(`delete_${id}`,'Delete');
                },
                error:function(data){
                    setTimeout(function(){
                        swMessage(AJAX_ERROR_MESSAGE);  
                    },100);
                    button_loading_destroy(`delete_${id}`,'Delete');
                }
            });
        });
    }
}

//details 
function details_view(data){
    $('#details-body').html(loadingImage);
    $('#details-modal-title').html('Customer visit details');
    $.ajax({
        type:'post',
        url:ajUrl,
        data:data,
        success:function(data){
            $('#details-body').html('');
            if(typeof(data.status)!=='undefined'){
                if(data.status==1){
                    $('#details-body').html(data.html);
                }

                swMessageFromJs(data.m);
            }
            else{
                swMessage(AJAX_ERROR_MESSAGE); 
            }
        },
        error:function(){
            $('#details-body').html('');
            swMessage(AJAX_ERROR_MESSAGE); 
        }
    });
    $('#details-modal-btn').click();
}

function customer_visit_details_view(id){
    details_view({customer_visit_details_view:1,id:id});
}
function doctor_visit_details_view(id){
    details_view({doctor_visit_details_view:1,id:id});
}

function bazar_visit_details_view(id){
    doctor_visit_details_view({bazar_visit_details_view:1,id:id})
}


// sale
function productsSale(){
    buttonLoading('productsSale');
    $('#sale-print-btn').html('');
    let draftID = parse_int($('#draftID').val());
    let pay_type = parse_int($('#pay-type').val());
    let cID = parse_int($('#cID').val());
    let due_day = parse_int($('#due_day').val());
    let base_id = parse_int($('#base_id').val());
    let saleDate = $('#saleDate').val();
    let subTotal=parse_float($('#subTotal').html());
    let discount=parse_float($('#saleDiscount').val());
    let netPayable=parse_float($('#netPayable').html());
    let note = $('#note').val();
    let order_no = $('#order_no').val();
    let order_date = $('#order_date').val();
    let not_check_credit_limit = $('#not_check_credit_limit').is(':checked')?1:0;
   


    let products= {};
    let count=0;
    errorSet=0;

    if(cID<=0){swMessage('Please select a customers.');errorSet=1;}
    else if(base_id<1){swMessage('Please select a base.');errorSet=1;}
        else if(due_day<0){swMessage('Invalid due day.');errorSet=1;}
            else if(pay_type<1){swMessage('Please select a pay type.');errorSet=1;}
                else if(subTotal<0){swMessage('Please select product.');errorSet=1;}
                    else if(netPayable<0){
                        console.log('netPayable',netPayable)
                        swMessage('Invalid discount.');errorSet=1;

                    }


    if(errorSet==0){
        $('#saleProducts .pID').each(function(a,b){
            if(errorSet==0){
                let tID=$(this).closest('tr').attr('id');
                let pID                 = parse_int($('#'+tID+' .pID').val());
                let qty                 = parse_float($('#'+tID+' .qty').html());
                let free_qty            = parse_float($('#'+tID+' .free_qty').html());
                let total_qty           = parse_float($('#'+tID+' .total_qty').html());
                let salePrice_tp        = parse_float($('#'+tID+' .salePrice-tp').html());
                let salePrice           = parse_float($('#'+tID+' .salePrice').html());
                let product_discount    = parse_float($('#'+tID+' .discount').html());
                let sub_total           = parse_float($('#'+tID+' .sub_total').html());
                let VAT                 = parse_float($('#'+tID+' .VAT').html());
                if(product_discount>sub_total){
                    //console.log('product_discount>sub_total',product_discount,sub_total)
                    swMessage('Invalid discount.');errorSet=1;
                }
                if(!products.hasOwnProperty(pID)){
                    products[pID]={
                        qty:qty,
                        free_qty:free_qty,
                        total_qty:total_qty,
                        salePrice_tp:salePrice_tp,
                        salePrice:salePrice,
                        discount:product_discount,
                        VAT:VAT
                    }
                    count++;
                }
                else{
                    swMessage('Some product you select multiple time. Please remove one.');errorSet=1;
                }
            }
        });
        if(count==0){errorSet=1;swMessage('select a product');}
        if(errorSet==0){
            let postData={
                new_sale:1,
                not_check_credit_limit:not_check_credit_limit,
                pay_type:pay_type,
                draftID:draftID,
                cID:cID,
                base_id:base_id,
                due_day:due_day,
                saleDate:saleDate,
                products:products,
                discount:discount,
                note:note,
                order_no:order_no,
                order_date:order_date,
                PRODUCT_TYPE:PRODUCT_TYPE,

            };
            //console.log('post_data',postData);

            $.ajax({
                type:'post',
                url:ajUrl,
                data:postData,
                success:function(data){
                    button_loading_destroy('productsSale','Sale');   
                    if(typeof(data.status)  !== "undefined"){ 
                        if(data.status==1){
                            $('#sale-print-btn').html('<a href="'+MAIN_URL+'/?print=sale&id='+data.sale_id+'" target="_blank" class="mt-1 btn-lg btn-success pull-right">Print</a>');
                            $('#cID').val('');
                            $('#saleDiscount').val('');
                            $('#saleProducts').html('');
                            $('#pay-type').val('');
                            $('#due_day').val('');
                            $('#base_id').val('');
                            select2Call();
                            productSaleSubTotal();

                        }
                        swMessageFromJs(data.m);
                    }
                    else{
                        swMessage(AJAX_ERROR_MESSAGE); 
                    }
                },
                error: function(data) { 
                    button_loading_destroy('productsSale','Sale');   
                    swMessage(AJAX_ERROR_MESSAGE); 
                }
            });

        }
    }
    if(errorSet==1){
        button_loading_destroy('productsSale','Sale');   
    }
}
function sale_customer_change(customer_id){

    $('#customer_input_area .select2-container').attr('style','background-color:#FFFFFF');

    $.ajax({
        type:'post',
        url:ajUrl,
        data:{customerCurrentBalance:1,cID:customer_id},
        success:function(data){
            console.log('data',data)
            if(data.status==1){
                if(data.can_invoice==0){
                    $('#customer_input_area .select2-container').attr('style','background-color:red');
                }
            }
            //swMessageFromJs(data.m);
        },
        error:function(error) {
          console.log('Ajax Error:'+error);
        }   
    }); 

}
function income_and_expense_add(type){
    buttonLoading('income_and_expense_add');
    let date = $('#trDate').val();
    let base_id = parse_int($('#base_id').val());
    let debit = parse_int($('#debit').val());
    let credit = parse_int($('#credit').val());
    let amount = parse_float($('#amount').val());
    let note = $('#note').val();
    let error = 0;
    if(debit<1){swMessage('Please select debit account.');error=1;}
    else if(credit<1){swMessage('Please select credit account.');error=1;}
    else if(amount<=0){swMessage('Please enter amount.');error=1;}
    if(!error){
        $.ajax({
            type:'post',
            url:ajUrl,
            data:{
                income_and_expense_add:1,
                date:date,
                base_id:base_id,
                debit:debit,
                credit:credit,
                amount:amount,
                note:note,
                type:type
            },
            success:function(data){
                button_loading_destroy('income_and_expense_add','Add');
                if(typeof data.status !== 'undefined'){
                    if(data.status==1){
                        $('#base_id').val(0);
                        //$('#debit').val('');
                        //$('#credit').val('');
                        $('#amount').val('');
                        $('#note').val('');
                        select2Call();
                        income_expense_list(type);
                    }
                    swMessageFromJs(data.m);
                }
                else{
                    swMessage(AJAX_ERROR_MESSAGE);
                }
            },
            error:function(data){
                button_loading_destroy('income_and_expense_add','Add');
                swMessage(AJAX_ERROR_MESSAGE);
            }
        });
    }
    else{
        button_loading_destroy('income_and_expense_add','Add');
    }
}
function recoverableDelete(id){
        are_you_sure(1,'Are you sure to delete this?',id,function(id){
            buttonLoading(`recoverableDelete_${id}`);
            $.ajax({
                type:'post',
                url:ajUrl,
                data:{recoverableDelete:1,id:id},
                success:function(data){
                    if(typeof(data.status)  !== "undefined"){ 
                        if(data.status==1){
                            recoverable_due_list();
                        }
                        setTimeout(function(){
                            swMessageFromJs(data.m);
                        },100);
                    }   
                    else{
                        setTimeout(function(){
                            swMessage(AJAX_ERROR_MESSAGE);  
                        },100);
                    }
                    button_loading_destroy(`recoverableDelete_${id}`,'Delete');
                },
                error:function(data){
                    setTimeout(function(){
                        swMessage(AJAX_ERROR_MESSAGE);  
                    },100);
                    button_loading_destroy(`recoverableDelete_${id}`,'Delete');
                }
            });
        });
    }

function production_packaging_validation(){
    const date                = $('#date').val();
    const batch               = $('#batch').val();
    const pmo_no              = $('#pmo_no').val();
    const target_product_id   = parse_int($('#mpID').val());
    const manufacture_product_id= parse_int($('#manufacture_product_id').val());
    const spend_qty           = parse_int($('#spend_qty').val());
    const targetQuantity      = parse_int($('#targetQuantity').val());
    const yield               = parse_int($('#yield').val());
    const retention_sample    = parse_int($('#retention-sample').val());
    const re_packing    = parse_int($('#re_packing').val());
    const note                = $('#note').val();
    const extraCost           = parse_float($('#extraCost').val());
    let products            = {};
    let errorSet                = 0;

    if(target_product_id<1){swMessage('Please select Targeted Product');errorSet=1;}
    else if(batch==''){swMessage('Batch field is required');errorSet=1;}
    if(errorSet==0){
        let sr=0;
        $('#productionProducts .pID').each(function(a,b){
            if(errorSet==0){
                sr++;  
                const tID         = $(this).closest('tr').attr('id');
                const pID         = parse_int($('#'+tID+' .pID').val());
                const qty         = parse_float($('#'+tID+' .qty').html());
                const extra_cost  = parse_float($('#'+tID+' .extra_cost').html());
                if(qty<=0){swMessage('Invalid Quantity');errorSet=1;}
                products[pID]={
                    qty:qty,
                    extra_cost:extra_cost
                }
            }
        });
        if(errorSet==0 && sr<1) {swMessage('Please select Product for production');errorSet=1;}
        else if(re_packing==0 && manufacture_product_id<1){swMessage('Please manufacture product');errorSet=1;}
            else if(re_packing==0 &&  spend_qty<1){swMessage('Please enter valid spend quantity');errorSet=1;}
                else if(targetQuantity<1){swMessage('Please enter a valid Targeted Quantity');errorSet=1;}
                    else if(yield<1){swMessage('Yield field is required');errorSet=1;}
    }
    const production_type = (!re_packing)?3:7;
    if(errorSet==0){
        const postData={
            production_type         : production_type,
            re_packing              : re_packing,
            date                    : date,
            manufacture_product_id  : manufacture_product_id,
            spend_qty               : spend_qty,
            target_product_id       : target_product_id,
            batch                   : batch,
            pmo_no                  : pmo_no,
            targetQuantity          : targetQuantity,
            yield                   : yield,
            retention_sample        : retention_sample,
            note                    : note,
            products                : products,
            extraCost               : extraCost,
        };
        return postData;
    } 
    return false;
}
$(document).on('click','.production_packaging_add',production_packaging_add);
function production_packaging_add(){
    let postData = production_packaging_validation();
    if(postData!=false){
        buttonLoading('production_packaging_add');
        postData.production_packaging_add=1;
        $.ajax({
            type:'post',
            url:ajUrl,
            data:postData,
            success:function(data){
                if(typeof(data.status)  !== "undefined"){ 
                    if(data.status==1){
                        production_data_clear()
                    }
                    swMessageFromJs(data.m);
                }
                else{
                    swMessage(AJAX_ERROR_MESSAGE); 
                }
                button_loading_destroy('production_packaging_add','Production');
            },
            error: function(data) { 
                swMessage(AJAX_ERROR_MESSAGE); 
                button_loading_destroy('production_packaging_add','Production');
            }
        });
    }
}
$(document).on('click','.production_packaging_edit',production_packaging_edit);
function production_packaging_edit(){
    let id = parse_int($('#manufacture_id').val());
    if(id<1){ swMessage('Invalid packaging');}
    else{
        let postData = production_packaging_validation();
        if(postData!=false){
            buttonLoading('production_packaging_edit');
            postData.packaging_edit=1;
            postData['id']=id;
            $.ajax({
                type:'post',
                url:ajUrl,
                data:postData,
                success:function(data){
                    if(typeof(data.status)  !== "undefined"){ 
                        if(data.status==1){
                            production_data_clear();
                        }
                        swMessageFromJs(data.m);
                    }
                    else{
                        swMessage(AJAX_ERROR_MESSAGE); 
                    }
                    button_loading_destroy('production_packaging_edit','Production update');
                },
                error: function(data) { 
                    button_loading_destroy('production_packaging_edit','Production update');
                    swMessage(AJAX_ERROR_MESSAGE); 
                }
            });
        }
    }
}
function production_data_clear(){
    $('#mpID').val('');
    $('#batch').val('');
    $('#stock').val('');
    $('#pmo_no').val('');
    $('#extraCost').val('');
    $('#manufacture_id').val('');
    $('#productionProducts').html('');
    $('#targetQuantity').val('')
    $('#yield').val('')
    $('#percentage').val('0.00')
    $('#retention-sample').val('')
    $('#note').val('')
    select2Call();
    productionSubTotal();
}