var loadingImage = '<img style="width:100px!important" src="plugins/images/main_loading.gif" alt="Wait Loading...." title="Wait Loading....">';
var autoInc=1;
const BOX_SEPARATOR='-';
function t(t){console.log(t);}


function mainCategoryChange(){
    let category=parse_int($('#category').val());
    if(typeof(NEED_ALL_FOR_FIRST_SELECT)=='undefined'){
        $('#subCategory').html('<option value="">Select sub category</option>');
    }
    else{
        $('#subCategory').html('<option value="">All</option>');
    }
    if(category>0){
        let count = 0;
        $.each(categoryData[category].childCategory,function(a,b){
            count++;
        });
        $.each(categoryData[category].childCategory,function(a,b){
            let sel = '';
            if(count==1){
                sel = 'selected';
            }
            $('#subCategory').append('<option '+sel+' value="'+b.id+'">'+b.name+'</option>');
        });

    }
    select2Call();
}
function purchaseSubCategoryChange(){  
    let subCategory=parse_int($('#subCategory').val());
    if(typeof(NEED_ALL_FOR_FIRST_SELECT)=='undefined'){
        $('#pID').html('<option value="">Select Product</option>');
    }
    else{
        $('#pID').html('<option value="">All</option>');
    }
    if(typeof(productData)!='undefined'){
        let count = 0;
        $.each(productData,function(a,b){
            if(b.pc==subCategory){
                count++;
            }
        });
        $.each(productData,function(a,b){
            if(b.pc==subCategory){
                let sel = '';
                if(count==1){
                    sel = 'selected';
                    saleProductChange(a);
                }
                $('#pID').append('<option '+sel+' value="'+a+'">'+b.t+'</option>');
            }
        });
    }
    select2Call();

}

function dicimalTow(floatVal,fixedLength){
    if(parse_int(fixedLength)<=0){fixedLength=2}
    //t(floatVal)
    return Number(floatVal).toFixed(fixedLength);
}
function voucherRemove(veID){
    clearMessage();
    if(confirm('Are you sure to remove this voucher?')){
        $.post(ajUrl,{voucherRemove:1,veID:veID},function(data){
            if(data.status==1){
                swMessageFromJs(data.m);
                cashFlowLoad(0);
            }
            else{
                createMsgFromJson(data.m);
            }
        })
    }
}
function returnSame(d){return d;}
function productPurchaseSave(){
    var ppDate=$('#ppDate').val();
    var ppRemarks=$('#ppRemarks').val();
    var pID=parse_int($('#pID').val());
    var supID=parse_int($('#supID').val());
    var ppQty=parse_int($('#ppQty').val());
    var ppUnitPrice=parse_float($('#ppUnitPrice').val());
    errorSet=0;
    if(ppQty<=0){
        swMessage('Please enter quantity.');errorSet=4;
    }
    else if(supID==0){
        swMessage('Please select supplier.');errorSet=5;
    }
    else if(ppUnitPrice<=0){
        swMessage('Please enter unite price.');errorSet=6;
    }
    if(errorSet==0){
        $.post(ajUrl,{productPurchaseSave:1,ppDate:ppDate,ppRemarks:ppRemarks,pID:pID,supID:supID,ppQty:ppQty,ppUnitPrice:ppUnitPrice},function(data){
            if(data.status==1){   
                $('#ppUnitPrice').val('');
                $('#ppQty').val('');
                $('#supID').val('');
                select2Call();
                createMsgFromJson(data.m);
            }
            else{swMessageFromJs(data.m);}
        });
    }
    else{
        t('error'+errorSet);
    }
}
function getRadioValue (radioButtonName) {
    if( $('input[name='+radioButtonName+']:radio:checked').length > 0 ) {
        return $('input[name='+radioButtonName+']:radio:checked').val();
    }
    else {
        return 0;
    }
}
function purchaseProductChange(pID){
    let type = parse_int($('#product_type').val());
    if(typeof(productData[type][pID])!=='undefined'){
        let p=productData[type][pID];
        $('#unitPrice').val(p.s);
        $('#salePrice').val(p.s);
        $('#qtyLabel').html(p.u);
        $('#pStock').val(p.st);
        purchaseRowTotal()
    }
    else{
        $('#unitPrice').val('');
        $('#salePrice').val('');
        $('#qtyLabel').html('');
        $('#pStock').val('');
    }
}
function production_product_change(pID){
    let p=productData[pID];
    $('#unitPrice').val(p.uc);
    $('#qtyLabel').html(p.u);
    $('#stock').val(p.st);
    purchaseRowTotal()
}
function production_manufacture_product_change(pID){
    var p=manufacture_product_data[pID];
    $('.manufacture_qty_label').html(p.u);
    $('#manufacture_available_qty').val(p.st);
    $('#manufacture_unit_price').val(p.uc);
    purchaseRowTotal()
}
function production_manufacture_total(){
    let manufacture_unit_price=$('#manufacture_unit_price').val(p.uc);
    let spend_qty=$('#spend_qty').val(p.uc);
    let total=(manufacture_unit_price*spend_qty);
    //$('#total').val(total.toFixed(2));
}
function format_big_number(big_object,decimal=2){
    // let output=big_object.toFixed(decimal);
    // output = output.replace(/\.?0+$/, '');
    // return output;
    let output = Number(big_object.toFixed(decimal));
    console.log('format_big_number',big_object.toString(),decimal,output.toString());
    return output.toString();
}
function purchaseRowTotal(){
    let extra_cost = new Big(parse_float($('#extra_cost').val()));
    let qty=new Big(parse_float($('#qty').val()));
    let unitPrice=new Big(parse_float($('#unitPrice').val()));
    let total = qty.times(unitPrice);
    //console.log('purchase row',qty.toString(),unitPrice.toString(),total.toString());
    total=total.plus(extra_cost);
    $('#total').val(format_big_number(total,6));
}
function purchaseSubTotal(){
    let total=new Big(0);
    let zero_price=false;
    if ($('#zero_price_purchase').prop('checked')) {
        zero_price=true;
    }
    $('#purchaseProducts .pID').each(function(a,b){
        let tID=$(this).closest('tr').attr('id');
        let qty=new Big(parse_float($('#'+tID+' .qty').html()));
        let unitPrice=new Big(parse_float($('#'+tID+' .unitPrice').html()));
        
        let tt=qty.times(unitPrice);
        console.log('sub total',qty.toString(), unitPrice.toString(),tt.toString());

        total=total.plus(tt);
        console.log('total in row',total)
    });
    let VAT= new Big(parse_float($('#VAT').val()));
    let AIT= new Big(parse_float($('#AIT').val()));
    let discount= new Big(parse_float($('#discount').val()));
    if(zero_price){
        total   = new Big(0);
        discount= new Big(0);
        VAT     = new Big(0);
        AIT     = new Big(0);
    }
    else{
        console.log('no zero')
    }

    console.log('total',total.toString())
    $('#subTotal').html(format_big_number(total,6));

    let netPayable=total.minus(discount);
    netPayable=netPayable.minus(AIT)
    netPayable=netPayable.minus(VAT)
    
    $('#netPayable').html(format_big_number(netPayable,6));  
}
function purchaseReturnAddToCart(){
    var pID         = parse_float($('#pID').val());
    //    t('pID')
    //    t(pID)
    let errorSet=0;
    var unitPrice   = parse_float($('#unitPrice').val());
    var qty         = parse_float($('#qty').val());
    if(pID<=0){swMessage('Please select a product');errorSet=1;}
    else if(unitPrice<=0){swMessage('Please enter a valid Unit price');errorSet=1;}
        else if(qty<=0){swMessage('Please enter a valid Quantity');errorSet=1;}
    if(errorSet==0){
        var total=(unitPrice*qty).toFixed(2);
        var p=productData[pID];
        var id='pd_'+pID+'_'+autoInc;autoInc++;
        $('#purchaseProductsTr .pID').val(pID)
        $('#purchaseProductsTr .pTitle').html(p.t)
        $('#purchaseProductsTr .unTitle').html(p.u)
        $('#purchaseProductsTr .unitPrice').html(unitPrice.toFixed(2))
        $('#purchaseProductsTr .qty').html(qty)
        $('#purchaseProductsTr .total').html(total)
        $('#purchaseProductsTr .remove').attr('onclick','remove_row_by_id(\''+id+'\');purchaseSubTotal()')
        var purchaseProductsTr=$('#purchaseProductsTr').html();
        //        t('purchaseProductsTr')
        //        t(purchaseProductsTr)
        $('#purchaseProducts').append('<tr id="'+id+'">'+purchaseProductsTr+'</tr>');
        $('#pID').val('');
        $('#unitPrice').val('');
        $('#salePrice').val('');
        $('#total').val('');
        $('#qty').val('');
        select2Call();
        var tr_sl_start=1;$('#purchaseProducts .autoSerial').each(function(){$(this).html(tr_sl_start);tr_sl_start++;});
        purchaseSubTotal(0);
    }
}
function purchaseAddToCart(){

    //    t('pID')
    //    t(pID)
    errorSet=0;
    let qty=new Big(parse_float($('#qty').val()));
    let unitPrice=new Big(parse_float($('#unitPrice').val()));
    let salePrice=new Big(parse_float($('#salePrice').val()));
    let type = parse_int($('#product_type').val());
    let category    = parse_int($('#category').val());
    let subCategory    = parse_int($('#subCategory').val());
    let pID         = parse_float($('#product_id').val());

    if(USE_PRODUCT_CATEGORY==1 && category<=0 ){swMessage('Please select a category');errorSet=1;}
    else if(USE_PRODUCT_CATEGORY==1 && typeof(categoryData[category])== "undefined"){swMessage('invalid category');errorSet=1;}
        else if(subCategory<=0 && USE_PRODUCT_CATEGORY==1){swMessage('Please select a sub category');errorSet=1;}
            else if(USE_PRODUCT_CATEGORY==1 && typeof(categoryData[category].childCategory[subCategory])== "undefined"){swMessage('invalid category');errorSet=1;}
                else if(type<0){swMessage('Please select a product type');errorSet=1;}
                    else if(pID<=0){swMessage('Please select a product');errorSet=1;}
                        else if(unitPrice.lt(0) ){swMessage('Invalid Unit Price');errorSet=1;}
                            else if(qty.lt(0)){swMessage('Please enter a valid Quantity');errorSet=1;}
    if(errorSet==0){
        let total_amount = unitPrice.times(qty);
        let total=format_big_number(total_amount,6);
        let p=productData[type][pID];
        let id='pd_'+pID+'_'+autoInc;autoInc++;
        $('#purchaseProductsTr .pID').val(pID);
        $('#purchaseProductsTr .pTitle').html(p.t);
        $('#purchaseProductsTr .unTitle').html(p.u);
        $('#purchaseProductsTr .unitPrice').html(format_big_number(unitPrice,6));
        $('#purchaseProductsTr .salePrice ').html(format_big_number(salePrice,6));
        $('#purchaseProductsTr .qty').html(format_big_number(qty,6));
        $('#purchaseProductsTr .total').html(total);
        $('#purchaseProductsTr .remove').attr('onclick','remove_row_by_id(\''+id+'\');purchaseSubTotal()');
        let purchaseProductsTr=$('#purchaseProductsTr').html();
        $('#purchaseProducts').append('<tr id="'+id+'">'+purchaseProductsTr+'</tr>');
        $('#product_id').val('');
        $('#category').val('');
        $('#subCategory').val('');
        $('#unitPrice').val('');
        $('#salePrice').val('');
        $('#total').val('');
        $('#qty').val('');
        select2Call();
        var tr_sl_start=1;$('#purchaseProducts .autoSerial').each(function(){$(this).html(tr_sl_start);tr_sl_start++;});
        purchaseSubTotal();
    }
}
function productionAddToCart(){
    errorSet=0;
    let qty         = parse_int($('#qty').val());
    let category    = parse_int($('#category').val());
    let subCategory = parse_int($('#subCategory').val());
    let extra_cost  = 0;
    if(can_show_price==1){
        extra_cost  = parse_int($('#extra_cost').val());
    }
    let pID         = parse_float($('#pID').val());
    console.log('productData[pID]',productData[pID])
    if(USE_PRODUCT_CATEGORY==1 &&category<=0 ){swMessage('Please select a category');errorSet=1;}
    else if(USE_PRODUCT_CATEGORY==1 && typeof(categoryData[category])== "undefined"){swMessage('invalid category');errorSet=1;}
        else if(USE_PRODUCT_CATEGORY ==1 && subCategory<=0 ){swMessage('Please select a sub category');errorSet=1;}
            else if(USE_PRODUCT_CATEGORY == 1 && typeof(categoryData[category].childCategory[subCategory])== "undefined"){swMessage('invalid category');errorSet=1;}
                else if(pID<=0){swMessage('Please select a product');errorSet=1;}
                    //else if(unitPrice<=0 ){swMessage('Invalid Unit Price');errorSet=1;}
                    else if(qty<=0){swMessage('Please enter a valid Quantity');errorSet=1;}
                        else if(qty>productData[pID].st){swMessage('Product is low in stock');errorSet=1;}
    if(errorSet==0){
        let p=productData[pID];
        let total=0;
        let unitPrice=0;
        if(can_show_price==1){
            unitPrice = p.uc;
            total=(unitPrice*qty);
            total+=extra_cost;
        }
        let c, sc;
        if(USE_PRODUCT_CATEGORY==1){
            c=categoryData[category];
            sc=categoryData[category].childCategory[subCategory];
        }
        let id='pd_'+pID+'_'+autoInc;autoInc++;
        $('#productionProductsTr .pID').val(pID)

        $('#productionProductsTr .pTitle').html(p.t)

        if(USE_PRODUCT_CATEGORY==1){
            $('#productionProductsTr .categoryID').val(sc.id)
            $('#productionProductsTr .category').html(c.title)
            $('#productionProductsTr .subCategory').html(sc.title)
        }
        $('#productionProductsTr .unTitle').html(p.u)
        $('#productionProductsTr .qty').html(qty)
        if(can_show_price==1){
            $('#productionProductsTr .extra_cost').html(extra_cost.toFixed(2))
            $('#productionProductsTr .total').html(total.toFixed(2))
            $('#productionProductsTr .unitPrice').html(unitPrice.toFixed(2))
        }
        $('#productionProductsTr .remove').attr('onclick','remove_row_by_id(\''+id+'\');productionSubTotal()')
        let purchaseProductsTr=$('#productionProductsTr').html();
        //        t('purchaseProductsTr')
        //        t(purchaseProductsTr)
        $('#productionProducts').append('<tr id="'+id+'">'+purchaseProductsTr+'</tr>');
        $('#pID').val('');
        $('#category').val('');
        $('#subCategory').val('');
        if(can_show_price==1){
            $('#unitPrice').val('');
            $('#salePrice').val('');
            $('#extra_cost').val('');
            $('#total_extra_cost').val('');
            $('#total').val('');
        }
        $('#qty').val('');
        select2Call();
        var tr_sl_start=1;$('#productionProducts .autoSerial').each(function(){$(this).html(tr_sl_start);tr_sl_start++;});
        productionSubTotal();
    }
}

function return_product_add_to_cart(){
    let pID         = parse_float($('#pID').val());
    errorSet=0;
    let salePrice   = parse_float($('#product_sale_price').val());
    let qty         = parse_int($('#product_quantity').val());

    if(pID<=0){swMessage('Please select a product');errorSet=1;}
    else if(qty<=0){swMessage('Please enter a valid Quantity');errorSet=1;}
        else if(salePrice<=0){swMessage('Please enter a valid sale price');errorSet=1;}
    if(errorSet==0){
        let p=productData[pID];


        let total=(salePrice*qty).toFixed(2);
        let id='pd_'+pID+'_'+autoInc;autoInc++;
        $('#return-products-tr .pID').val(pID);

        $('#return-products-tr .pTitle').html(p.t);
        $('#return-products-tr .unTitle').html(p.u);
        $('#return-products-tr .salePrice ').html(salePrice.toFixed(2));
        $('#return-products-tr .qty').html(qty);
        $('#return-products-tr .total').html(total);
        $('#return-products-tr .remove').attr('onclick','remove_row_by_id(\''+id+'\');product_return_subtotal()')
        var purchaseProductsTr=$('#return-products-tr').html();
        //        t('purchaseProductsTr')
        //        t(purchaseProductsTr)
        $('#return-product').append('<tr id="'+id+'">'+purchaseProductsTr+'</tr>');
        $('#pID').val('');
        $('#productSalePrice').val('');
        $('#total').val('');
        $('#productSaleqty').val('');
        select2Call();
        let tr_sl_start=1;$('#return-product .autoSerial').each(function(){$(this).html(tr_sl_start);tr_sl_start++;});
        product_return_subtotal();

    }
}

function product_return_subtotal(){
    let total=0;
    $('#return-product .pID').each(function(a,b){
        let tID=$(this).closest('tr').attr('id');
        let pID = $('#'+tID+' .pID').val();
        let tt =parse_float($('#'+tID+' .total').html());
        total+=tt;
    });
    $('#subTotal').html(total.toFixed(2));
    let discount=parse_float($('#return-discount').val());
    let netPayable=total-discount;
    $('#netPayable').html(netPayable.toFixed(2));
}
function type_wise_product_change(type){
    $('#product_id').html('<option value="">Select Product</option>');
    if(type>=0){
        if(typeof(productData[type])!='undefined'){
            $.each(productData[type],function(a,b){
                $('#product_id').append('<option value="'+b.id+'">'+b.t+'</option>');
            });
        }
    }
    select2Call();
}
function type_wise_supplyer_and_product(type){
    $('#supplier_id').html('<option value="">Select supplier</option>');
    if(type>=0){
        if(typeof(supplyer_data[type])!='undefined'){
            $.each(supplyer_data[type],function(a,b){
                let sel = '';
                if(typeof(supID)!='undefined' && supID>0&&b.id==supID){
                    sel = 'selected'; 
                }
                $('#supplier_id').append('<option '+sel+' value="'+b.id+'">'+b.name+'</option>');
            });

        }
    }
    type_wise_product_change(type);
    select2Call();
}

function productionSubTotal(){
    let total=0;
    $('#productionProducts .pID').each(function(a,b){
        let tID=$(this).closest('tr').attr('id');
        let product_total=parse_float($('#'+tID+' .total').html());
        total+=product_total;
    });
    let targetQuantity   = parse_int($('#targetQuantity').val());
    let yield   = parse_int($('#yield').val());
    let percentage=0;
    if(targetQuantity>0 && yield>0){
        percentage =  (yield/targetQuantity)*100
    }
    if(can_show_price==1){
        $('#subTotal').html(total.toFixed(2));
        let extraCost=parse_float($('#extraCost').val());
        let netPayable=total+extraCost;
        $('#netPayable').html(netPayable.toFixed(2));
        $('#percentage').html(percentage.toFixed(2));
    }
}
function production_manufacture_validation(){
    let date = $('#date').val();
    let batch = $('#batch').val();
    let target_product_id   = parse_int($('#mpID').val());
    let targetQuantity   = parse_int($('#targetQuantity').val());
    let yield   = parse_int($('#yield').val());
    let retention_sample   = parse_int($('#retention-sample').val());
    let note   = $('#note').val();
    let extraCost= parse_float($('#extraCost').val());
    let products= {};
    errorSet=0;

    if(target_product_id<1){swMessage('Please select Targeted Product');errorSet=1;}
    else if(batch==''){swMessage('Bach field is required');errorSet=1;}
    if(errorSet==0){
        let sr=0;
        $('#productionProducts .pID').each(function(a,b){
            if(errorSet==0){
                sr++;  
                let tID=$(this).closest('tr').attr('id');
                let pID         = parse_int($('#'+tID+' .pID').val());
                let unitPrice   = productData[pID].s
                let qty         = parse_float($('#'+tID+' .qty').html());
                let extra_cost         = parse_float($('#'+tID+' .extra_cost').html());
                if(qty<=0){swMessage('Invalid Quantity');errorSet=1;}
                products[pID]={
                    unitPrice:unitPrice,
                    qty:qty,
                    extra_cost:extra_cost
                }
            }
        });
        if(errorSet==0 && sr<1) {swMessage('Please select Product for prodction');errorSet=1;}
        else if(targetQuantity<1){swMessage('Please enter a valid Targeted Quantity');errorSet=1;}
            else if(yield<1){swMessage('Yield field is required');errorSet=1;}
    }
    if(errorSet==0){
        let postData={
            production_type     : 5,
            date                : date,
            target_product_id   : target_product_id,
            batch               : batch,
            targetQuantity      : targetQuantity,
            yield               : yield,
            retention_sample    : retention_sample,
            note                : note,
            products            : products,
            extraCost           : extraCost,
        };

        return postData;
    } 
    return false;
}
function production_manufacture_add(){

    let postData = production_manufacture_validation();
    if(postData!=false){
        buttonLoading('production_manufacture_add');
        postData['manufacture_add']=1;

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
                button_loading_destroy('production_manufacture_add','Production');
            },
            error: function(data) { 
                button_loading_destroy('production_manufacture_add','Production');
                swMessage(AJAX_ERROR_MESSAGE); 
            }
        });
    }

}
function production_manufacture_edit(){
    let id = parse_int($('#manufacture_id').val());
    if(id<1){ swMessage('Invalid manufacture');}
    else{
        let postData = production_manufacture_validation();
        if(postData!=false){
            buttonLoading('production_manufacture_edit');
            postData['manufacture_edit']=1;
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
                    button_loading_destroy('production_manufacture_edit','Production update');
                },
                error: function(data) { 
                    button_loading_destroy('production_manufacture_edit','Production update');
                    swMessage(AJAX_ERROR_MESSAGE); 
                }
            });
        }
    }
}





function saleReport(){
    console.log('It is not userd');return false;
    var supID   = parse_int($('#supID').val());
    var dsr     = parse_int($('#dsr').val());
    var sr      = parse_int($('#sr').val());
    var type    = parse_int($('#type').val());
    var wp      = parse_int($('#wp').val());
    var dRange  = $('#dRange').val();
    $('#reportArea').html(loadingImage);
    let sale_report_data={saleReport:1,dRange:dRange,supID:supID,dsr:dsr,sr:sr,type:type,wp:wp};
    $.ajax({
        type:'post',
        url:ajUrl,
        data:sale_report_data,
        success:function(data){
            if(data.status==1){
                $('#reportArea').html(data.html);
            }
            swMessageFromJs(data.m);
        }
    });
}
function purchaseReport(){
    let supplier_id   = parse_int($('#supplier_id').val());
    let type   = parse_int($('#type').val());
    let dRange  = $('#dRange').val();
    $('#reportArea').html(loadingImage);
    const local_data={purchaseReport:1,dRange:dRange,supplier_id:supplier_id,type:type};
    localStorage.setItem('purchase_report_data', JSON.stringify(local_data));
    $.ajax({
        type:'post',
        url:ajUrl,
        data:{purchaseReport:1,dRange:dRange,supplier_id:supplier_id,type:type},
        success:function(data){
            if(typeof(data.status)  !== "undefined"){ 
                if(data.status==1){
                    $('#reportArea').html(data.html);
                }
                swMessageFromJs(data.m);
            }   
            else{
                swMessage(AJAX_ERROR_MESSAGE);    
            }
        },
        error:function(data){
            swMessage(AJAX_ERROR_MESSAGE);  
        }
    });
}
function user_balance_retport(){
    $('#reportArea').html(loadingImage); 
    $.ajax({
        type:'post',
        url:ajUrl,
        data:{user_balance_retport:1},
        success:function(data){
            if(typeof(data.status)  !== "undefined"){ 
                if(data.status==1){
                    $('#reportArea').html(data.html);
                }
                swMessageFromJs(data.m);
            }   
            else{
                swMessage(AJAX_ERROR_MESSAGE);    
            }
        },
        error:function(data){
            swMessage(AJAX_ERROR_MESSAGE);  
        }
    });
}
function user_statment(){ 
    let dRange  = $('#dRange').val();
    let user_id  =parse_int($('#user_id').val());
    if(user_id<1){swMessage('Select a user.')}
    else{
        $('#reportArea').html(loadingImage); 
        $.ajax({
            type:'post',
            url:ajUrl,
            data:{user_statment:1,dRange:dRange,user_id:user_id},
            success:function(data){
                if(typeof(data.status)  !== "undefined"){ 
                    if(data.status==1){
                        $('#reportArea').html(data.html);
                    }
                    swMessageFromJs(data.m);
                }   
                else{
                    swMessage(AJAX_ERROR_MESSAGE);    
                }
            },
            error:function(data){
                swMessage(AJAX_ERROR_MESSAGE);  
            }
        });
    }
}



function purchaseAdd(){
    buttonLoading('purchaseAdd')
    let supplier_id   = parse_int($('#supplier_id').val());
    let type= parse_int($('#product_type').val());
    let supplier_no= $('#supplier-no').val();
    let bill_date = $('#bill-date').val();
    let challan_no = $('#challan-no').val();
    let challan_date = $('#challan-date').val();
    let po_no = $('#po-no').val();
    let mrr_code = $('#mrr-code').val();
    let mrr_date = $('#mrr-date').val();
    let discount= parse_float($('#discount').val());
    let VAT= parse_float($('#VAT').val());
    let AIT= parse_float($('#AIT').val());
    let note= $('#note').val();
    let zero_price=0;
    if ($('#zero_price_purchase').prop('checked')) {
        zero_price=1;
    }
    //var draftID= parse_int($('#draftID').val());
    let products= {};
    errorSet=0;

    if(supplier_id<=0){errorSet=1;;swMessage('Please select Supplier');}

    let count=0;
    if(errorSet==0){
        $('#purchaseProducts .pID').each(function(a,b){
            console.log('d',b)
            if(errorSet==0){
                let tID         = $(this).closest('tr').attr('id');
                let pID         = parse_int($('#'+tID+' .pID').val());
                let categoryID  = parse_int($('#'+tID+' .categoryID').val());

                let qty=new Big(parse_float($('#'+tID+' .qty').html()));
                let unitPrice=new Big(parse_float($('#'+tID+' .unitPrice').html()));
        
                // let tt=qty.times(unitPrice);



                // let unitPrice   = parse_float($('#'+tID+' .unitPrice').html());
                // let qty         = parse_float($('#'+tID+' .qty').html());
                if(zero_price==0&&unitPrice.lt(0)){swMessage('Invalid Unit Price');errorSet=1;return;}
                else if(qty.lt(0)){swMessage('Invalid Quantity');errorSet=1;return;}
                if(!products.hasOwnProperty(pID)){
                    products[pID]={
                        categoryID:categoryID,
                        unitPrice:format_big_number(unitPrice,6),
                        qty:format_big_number(qty,6)
                    }
                    count++;
                }
                else{
                    swMessage('Some product you select multiple time. Please remove one.');errorSet=1;return;
                }
            }
        });
    }
    else if(count==0){errorSet=1;swMessage('select a product '.errorSet);}
    if(errorSet==0){
        let postData={
            purchaseAdd:1,
            supplier_id:supplier_id,
            type:type,
            supplier_no:supplier_no,
            bill_date:bill_date,
            challan_no:challan_no,
            challan_date:challan_date,
            po_no:po_no,
            mrr_code:mrr_code,
            mrr_date:mrr_date,
            products:products,
            discount:discount,
            VAT:VAT,
            AIT:AIT,
            note:note,
            zero_price:zero_price
        };
        $.ajax({
            type:'post',
            url:ajUrl,
            data:postData,
            success:function(data){
                if(typeof(data.status)!=="undefined"){
                    if(data.status==1){
                        $('#product_type').val('');
                        $('#supplier_id').val('');
                        $('#supplier-no').val('');
                        $('#challan-no').val('');
                        $('#po-no').val('');
                        $('#mrr-code').val('');
                        $('#discount').val('');
                        $('#VAT').val('');
                        $('#AIT').val('');
                        $('#note').val('');
                        $('#purchaseProducts').html('');
                        select2Call();
                        purchaseSubTotal();
                    }
                    swMessageFromJs(data.m);
                }
                else{
                    swMessage(AJAX_ERROR_MESSAGE);  
                }
                button_loading_destroy('purchaseAdd','Purchase');
            },
            error:function(data){
                swMessage(AJAX_ERROR_MESSAGE);  
                button_loading_destroy('purchaseAdd','Purchase');
            }
        });
    }
    else{
        button_loading_destroy('purchaseAdd','Purchase');
    }
}
function purchase_update(){
    buttonLoading('purchase_update')
    let purchase_id   = parse_int($('#purchase_id').val());
    let supplier_id   = parse_int($('#supplier_id').val());
    let type= parse_int($('#product_type').val());
    let supplier_no= $('#supplier-no').val();
    let bill_date = $('#bill-date').val();
    let challan_no = $('#challan-no').val();
    let challan_date = $('#challan-date').val();
    let po_no = $('#po-no').val();
    let mrr_code = $('#mrr-code').val();
    let mrr_date = $('#mrr-date').val();
    let discount= parse_float($('#discount').val());
    let VAT= parse_float($('#VAT').val());
    let AIT= parse_float($('#AIT').val());
    let note= $('#note').val();
    let zero_price=0;
    if ($('#zero_price_purchase').prop('checked')) {
        zero_price=1;
    }
    //var draftID= parse_int($('#draftID').val());
    let products= {};
    errorSet=0;

    if(purchase_id<=0){swMessage('Invalid purchase');errorSet=1;}
    else if(supplier_id<=0){swMessage('Please select Supplier');errorSet=1;}
    let count=0;
    if(errorSet==0){
        $('#purchaseProducts .pID').each(function(a,b){
            if(errorSet==0){
                let tID         = $(this).closest('tr').attr('id');
                let pID         = parse_int($('#'+tID+' .pID').val());
                let categoryID  = parse_int($('#'+tID+' .categoryID').val());
                let unitPrice   = parse_float($('#'+tID+' .unitPrice').html());
                let qty         = parse_float($('#'+tID+' .qty').html());
                if(zero_price==0&&unitPrice<=0){swMessage('Invalid Unit Price');errorSet=1;return;}
                if(qty<=0){swMessage('Invalid Quantity');errorSet=1;return;}
                if(!products.hasOwnProperty(pID)){
                    products[pID]={
                        categoryID:categoryID,
                        unitPrice:unitPrice,
                        qty:qty
                    }
                    count++;
                }
                else{
                    swMessage('Some product you select multiple time. Please remove one.');errorSet=1;return;
                }
            }
        });
    }
    if(count==0){errorSet=1;swMessage('select a product');}
    if(errorSet==0){
        let postData={
            purchase_update:1,
            id:purchase_id,
            supplier_id:supplier_id,
            type:type,
            supplier_no:supplier_no,
            bill_date:bill_date,
            challan_no:challan_no,
            challan_date:challan_date,
            po_no:po_no,
            mrr_code:mrr_code,
            mrr_date:mrr_date,
            products:products,
            discount:discount,
            VAT:VAT,
            AIT:AIT,
            note:note,
            zero_price:zero_price
            //draftID:draftID,
        };
        $.ajax({
            type:'post',
            url:ajUrl,
            data:postData,
            success:function(data){
                if(typeof(data.status)!=="undefined"){
                    if(data.status==1){
                        $('#product_type').val('');
                        $('#purchase_id').val('');
                        $('#supplier_id').val('');
                        $('#supplier-no').val('');
                        $('#challan-no').val('');
                        $('#po-no').val('');
                        $('#mrr-code').val('');
                        $('#discount').val('');
                        $('#VAT').val('');
                        $('#AIT').val('');
                        $('#note').val('');
                        $('#purchaseProducts').html('');
                        select2Call();
                        purchaseSubTotal();
                    }
                    swMessageFromJs(data.m);
                }
                else{
                    swMessage(AJAX_ERROR_MESSAGE);  
                }
                button_loading_destroy('purchase_update','Update');
            },
            error:function(data){
                swMessage(AJAX_ERROR_MESSAGE);  
                button_loading_destroy('purchase_update','Update');
            }
        });
    }
    else{
        button_loading_destroy('purchase_update','Update');
    }
}
function productReturnAdd(){
    var scID    = parse_int($('#scID').val());
    var supID   = parse_int($('#supID').val());
    var productType   = parse_int($('#productType').val());
    var supInvNo= $('#supInvNo').val();
    var purDate = $('#purDate').val();
    var discount= parse_float($('#discount').val());
    var products= {};
    errorSet=0;
    $('#purchaseProducts .pID').each(function(a,b){
        if(errorSet==0){
            var tID=$(this).closest('tr').attr('id');
            var pID         = parse_int($('#'+tID+' .pID').val());
            var unitPrice   = parse_float($('#'+tID+' .unitPrice').html());
            var qty         = parse_float($('#'+tID+' .qty').html());
            if(unitPrice<=0){swMessage('Invalid unit price');errorSet=1;}
            else if(qty<=0){swMessage('Invalid Quantity');errorSet=1;}
            products[pID]={
                up:unitPrice,
                q:qty
            }
        }
    });
    //t(products)
    var postData={
        productReturnAdd:1,
        scID:scID,
        supID:supID,
        supInvNo:supInvNo,
        purDate:purDate,
        products:products,
        discount:discount,
        productType:productType,
    };
    if(productType<1){swMessage('Please select return type');errorSet=1;}
    if(errorSet==0){
        $.ajax({
            type:'post',
            url:ajUrl,
            data:postData,
            success:function(data){
                if(data.status==1){
                    //$('#supID').val('');
                    $('#supInvNo').val('');
                    $('#discount').val('');
                    $('#purchaseProducts').html('');
                    select2Call();
                    purchaseSubTotal();
                }
                swMessageFromJs(data.m);
            }
        });
    }
}
function moreDueEmpAdd(){
    var empDueArea=$('#empDueArea').html();
    var nextID='dueEmpDistribute_'+autoInc;autoInc++;
    $('#dudDistEmpArea').append('<div class="form-group row dueDistribute" id="'+nextID+'">'+empDueArea+'</div>');
    select2CallForCustom('#'+nextID+' .eID');
}
function moreDueColEmpAdd(){
    var empDueArea=$('#empDueColArea').html();
    var nextID='dueEmpDistribute_'+autoInc;autoInc++;
    $('#dueColEmpArea').append('<div class="form-group row dueCol" id="'+nextID+'">'+empDueArea+'</div>');
    select2Call()
}
function moreSalesReturnAdd(){
    var rowID='rtp_'+autoInc;autoInc++;
    $('#salesReturnInitArea .form-group').attr('id',rowID);
    $('#salesReturnInitArea .removeRow').attr('onclick','remove_row_by_id(\''+rowID+'\');dealerSaleRowTotal()');
    var salesReturnInitArea=$('#salesReturnInitArea').html();
    $('#salesReturnArea').append(salesReturnInitArea);
    $('#salesReturnInitArea .form-group').attr('id','abdldo'+autoInc);
    select2Call();
}
function getCartonQtyFromID(pID,tID){
    var qtyData     = $('#'+tID).html();
    var cartonData  = qtyData.split(SALE_CARTOON_SEPARATOR);
    var carton      = parse_int(cartonData[0]);
    var unit        = 0;
    if(cartonData[1]!=undefined){
        unit=parse_int(cartonData[1]);
    }
    var perCarton=products[pID].pCarton;
    if(perCarton<=1){perCarton=1;}
    var totalUnit=0;
    if(carton>0){
        totalUnit+=(carton*perCarton);
    }
    if(unit>0){
        totalUnit+=unit;
    }
    //    t('totalUnit')
    //    t(totalUnit)
    return totalUnit;
}
var dealerSaleHideShow=0;
function loadSectionCashBalance(scID){
    $('#sectionBalance').val('Loading ...');
    $.ajax({
        type:'post',
        url:ajUrl,
        data:{loadSectionCashBalance:1,scID:scID},
        success:function(data){
            if(data.status==1){
                $('#sectionBalance').val(data.balance);
            }
            else{
                $('#sectionBalance').val('');
            }
            swMessageFromJs(data.m);
        }
    });
}
function saleDraftAdd(){
    var saleData={};
    var returnData={};
    var otCostData={};
    var empDueData={};
    var empDueColData={};
    var dueData={};
    var dueColData={};
    var noteData={};
    var totalCollect=0;
    var tSaleAmount=0;
    var totalSaleReturn=0;
    var totalDueCollect=0;
    var totalOtherCost=0;
    var totalDueDistribute=0;
    $('#saleAddBtn').hide();
    $('#saleAddBtn2').show();
    var cID=0;
    if(customerSale==1){
        cID=parse_int($('#cID').val());
        if(cID==0){swMessage('Please select customer');errorSet=1;}
    }
    errorSet=0;
    var date=$('#date').val();
    var sNote=$('#sNote').val();
    var sr=parse_int($('#sr').val());
    var supID=parse_int($('#supID').val());
    var draftID=parse_int($('#draftID').val());
    var dsr=parse_int($('#dsr').val());
    var rtID=parse_int($('#rtID').val());
    if(sr==0){swMessage('Please select SR');errorSet=1;}
    else if(dsr==0){swMessage('Please select DSR');errorSet=1;}
        else if(rtID==0){swMessage('Please select Route');errorSet=1;}
    if(errorSet==0){
        $('.sp').each(function(a,b){
            var tID=this.id;
            var arr=tID.split('_');
            var pID=arr[1];
            var pSalePrice=parse_float($('#'+tID+' .pSalePrice').html());
            var discount=0;
            if(customerSale==1){
                discount=parse_float($('#'+tID+' .dis').html());
                if(discount!=0){
                    pSalePrice=pSalePrice-discount;
                    $('#'+tID+' .actualSalePrice').html(' '+pSalePrice.toFixed(2)+'/=');
                }
                else{
                    $('#'+tID+' .actualSalePrice').html('');
                }
            }

            var orderQty=getCartonQtyFromID(pID,tID+' .orderQty');
            var returnQty=getCartonQtyFromID(pID,tID+' .returnQty');
            var saleQty=orderQty-returnQty; 
            var saleAmount=saleQty*pSalePrice;
            tSaleAmount+=saleAmount;
            if(orderQty>0){
                if(saleQty>=0){
                    saleData[pID]={
                        p:pID,
                        o:orderQty,
                        r:returnQty
                    }
                    if(discount!=0){
                        saleData[pID].d=discount;
                    }
                }
                else if(saleQty<0){
                    swMessage(products[pID].pTitle+' Invalid Sale Quantity');errorSet=1;
                }
            }
            var returnGdQty=parse_float($('#'+tID+' .returnGdQty').html()); 
            var returnGdUP=parse_float($('#'+tID+' .returnGdUP').html()); 
            var returnDmQty=parse_float($('#'+tID+' .returnDmQty').html()); 
            var returnDmUP=parse_float($('#'+tID+' .returnDmUP').html()); 
            var returnGdAmount=returnGdQty*returnGdUP;
            var returnDmAmount=returnDmQty*returnDmUP;
            if(returnGdAmount<0){
                swMessage(products[pID].pTitle+' Invalid Return Good Quantity');errorSet=1;
            }
            if(returnDmAmount<0){
                swMessage(products[pID].pTitle+' Invalid Return Damage Quantity');errorSet=1;
            }
            if(returnGdQty>0||returnDmQty>0){
                totalSaleReturn+=returnGdAmount;
                totalSaleReturn+=returnDmAmount;
                returnData[pID]={
                    p:pID,
                    g:returnGdQty,
                    ga:returnGdUP,
                    d:returnDmQty,
                    da:returnDmUP,
                };
            }

        });
        var extraAmount=parse_int($('#extraAmount').val());
        if(tSaleAmount>0){
            tSaleAmount+=extraAmount;
        }
    }
    if(errorSet==0){
        $('#otherCosts .otherCost').each(function(a,b){
            tID=this.id;
            var type=parse_int($('#'+tID+' .costType').val());
            var amount=parse_float($('#'+tID+' .amount').val());
            var remarks=$('#'+tID+' .remarks').val();
            if(amount>0){
                totalOtherCost+=amount;
                otCostData[type]={
                    t:type,
                    r:remarks,
                    a:amount
                };
            }
        });
    }
    t('otCostData')
    t(otCostData)
    if(errorSet==0){
        $('#dueCollectArea .dueCol').each(function(a,b){
            var tID=this.id;
            var amount=parse_float($('#'+tID+' .amount').val());
            var remarks=$('#'+tID+' .remarks').val();
            var type=$('#'+tID+' .type').val();
            dueColData[type]={t:type,a:amount,r:remarks};
            totalDueCollect+=amount;
        });
        $('#dueColEmpArea .dueCol').each(function(a,b){
            var tID=this.id;
            var amount=parse_float($('#'+tID+' .amount').val());
            var eID=parse_float($('#'+tID+' .eID').val());
            var remarks=$('#'+tID+' .remarks').val();
            if(eID>0&&amount>0){
                empDueColData[eID]={
                    eID:eID,
                    a:amount,
                    r:remarks
                };
                totalDueCollect+=amount;
            }
            else if(amount>0){
                //swMessage('Some due employee not set yet');errorSet=1;
            }
        });
    }
    if(errorSet==0){
        var totalCostView=totalSaleReturn+totalOtherCost;
        var availabelBalance=(tSaleAmount+totalDueCollect)-totalCostView;

        $('.noteCollect').each(function(a,b){
            var tID=this.id;
            //t(tID)
            var count   = parse_float($('#'+tID+' .count').val());
            var type    = $('#'+tID+' .type').val();
            var amount  = parse_int($('#'+tID+' .amount').val());
            if(amount>0){
                var total   = count*amount;
                totalCollect+=total;
                noteData[type]={
                    t:type,
                    a:amount
                };
            }
        });
        var totalDue=availabelBalance-totalCollect;
        $('#dueDistributeArea .dueDistribute').each(function(a,b){
            var tID=this.id;
            var amount=parse_float($('#'+tID+' .amount').val());
            if(amount>0){
                var type=$('#'+tID+' .type').val();
                var trm='';
                var remarks=$('#'+tID+' .remarks').val();
                if(remarks!=''){
                    trm=remarks;
                }
                totalDueDistribute+=amount;
                dueData[type]={t:type,a:amount,r:trm};
            }
        });

        t('dueData')
        t(dueData)

        $('#dudDistEmpArea .dueDistribute').each(function(a,b){
            var tID=this.id;
            var amount=parse_float($('#'+tID+' .amount').val());
            var eID=parse_float($('#'+tID+' select.eID').val());
            var remarks=$('#'+tID+' .remarks').val();
            if(eID>0&&amount>0){
                empDueData[eID]={
                    eID:eID,
                    a:amount,
                    r:remarks
                };
                totalDueDistribute+=amount;
            }
            else if(amount>0){
                //swMessage('Some due employee not set yet');errorSet=1;
            }
        });
        t(empDueData)
        t('empDueData')
        totalDue=Math.round(totalDue);
        totalDueDistribute=Math.round(totalDueDistribute);
    }
    if(errorSet==0){
        postData={
            saleDraftAdd    : 1,
            supID           : supID,
            draftID         : draftID,
            date            : date,
            sr              : sr,
            dsr             : dsr,
            rtID            : rtID,
            cID             : cID,
            saleData        : saleData,
            extraAmount     : extraAmount,
            dueColData      : dueColData,
            empDueColData   : empDueColData,
            returnData      : returnData,
            otCostData      : otCostData,
            noteData        : noteData,
            sNote           : sNote,
            dueData         : dueData,
            empDueData      : empDueData,
        };

        $.ajax({
            type:'post',
            url:ajUrl,
            data:postData,
            success:function(data){
                $('#saleAddBtn2').hide();
                $('#saleAddBtn').show();
                if(data.status==1){
                    $('#draftID').val(data.draftID);
                    //saleReport();
                    var newLink="?mdl=dealerSale&supID="+supID+'&draftID='+data.draftID;
                    if(customerSale==1){
                        newLink+='&customerSale=1'
                    }
                    window.history.pushState("", "", newLink);
                }
                swMessageFromJs(data.m)
            }
        });
    }
    if(errorSet==1){
        $('#saleAddBtn2').hide();
        $('#saleAddBtn').show();
    }
}
function loadSupProducts(supID){
    $('#pID').html('<option value="">Select Product</option>');
    $.ajax({
        type:'post',
        url:ajUrl,
        data:{loadSupProducts:1,supID:supID},
        success:function(data){
            if(data.status==1){
                $.each(data.products,function(a,b){
                    $('#pID').append('<option value="'+b.pID+'">'+b.pTitle+'</option>');
                });
                select2Call();
            }
            swMessageFromJs(data.m)
        }
    });
}
function supplierStatement(){
    var supID= parse_int($('#supID').val());
    var scID= parse_int($('#scID').val());
    var dRange  = $('#dRange').val();
    $('#reportArea').html(loadingImage);
    $.ajax({
        type:'post',
        url:ajUrl,
        data:{supplierStatement:1,scID:scID,dRange:dRange,supID:supID},
        //data:{suuplierStatment:1,scID:scID,dRange:dRange,supID:supID},
        success:function(data){
            $('#reportArea').html('');
            if(data.status==1){
                $('#reportArea').html(data.html);
            }
            swMessageFromJs(data.m);
        }
    });
}
function investReport(){
    var type    = $('#type').val();
    var dRange  = $('#dRange').val();
    var psID    = $('#psID').val();
    $('#reportArea').html(loadingImage);

    $.ajax({
        type:'post',
        url:ajUrl,
        data:{investReport:1,dRange:dRange,psID:psID,type:type},
        success:function(data){
            //t('data.status')
            //t(data.status)
            $('#reportArea').html('');
            if(data.status==1){
                $('#reportArea').html(data.html);
            }
            swMessageFromJs(data.m);
        },
        error:function(a,b){
            t('a')
            t(a)
            t('b')
            t(b)
        }
    });
}
function employeeSalaryReport(){
    var eID     = parse_int($('#eID').val());
    var type    = parse_int($('#type').val());
    var dRange= $('#dRange').val();
    $('#reportArea').html(loadingImage);
    $.ajax({
        type:'post',
        url:ajUrl,
        data:{employeeSalaryReport:1,dRange:dRange,eID:eID,type:type},
        success:function(data){
            $('#reportArea').html('');
            if(data.status==1){
                $('#reportArea').html(data.html);
            }
            swMessageFromJs(data.m);
        }
    });
}
function suuplierSummery(){
    var supID   = parse_int($('#supID').val());
    var scID    = parse_int($('#scID').val());
    var dRange  = $('#dRange').val();

    $('#reportArea').html(loadingImage);
    $.ajax({
        type:'post',
        url:ajUrl,
        data:{suuplierSummery:1,scID:scID,dRange:dRange,supID:supID},
        success:function(data){
            $('#reportArea').html('');
            if(data.status==1){
                $('#reportArea').html(data.html);
            }
            swMessageFromJs(data.m);
        }
    });

}
function product_statement(){
    var product_id     = parse_int($('#product_id').val());
    var type    = parse_int($('#type').val());
    var dRange  = $('#dRange').val();

    $('#reportArea').html(loadingImage);
    $.ajax({
        type:'post',
        url:ajUrl,
        data:{product_statement:1,product_id:product_id,dRange:dRange,type:type},
        success:function(data){
            $('#reportArea').html('');
            if(data.status==1){
                $('#reportArea').html(data.html);
            }
            swMessageFromJs(data.m);
        }
    });
}
function cashFlowLoad(){
    var hID=$('#hID').val();
    var uID=$('#uID').val();
    var vType=$('#vType').val();
    var dRange=$('#dRange').val();
    $('#trDetailsArea').html(loadingImage);
    $.post(ajUrl,{cashFlowLoad:1,hID:hID,uID:uID,vType:vType,dRange:dRange},function(data){
        if(data.status==1){
            $('#trDetailsArea').html(data.html);
        }
        else{
            $('#trDetailsArea').html('');
            swMessageFromJs(data.m);
        }
    });
}

function incomeExpGrid(vType){
    var hID=$('#shID').val();
    var uID=$('#uID').val();
    var scID=$('#scID').val();
    var dRange=$('#dRange').val();
    $('#trDetailsArea').html(loadingImage);
    $.post(ajUrl,{cashFlowLoad:1,scID:scID,hID:hID,uID:uID,vType:vType,dRange:dRange},function(data){
        if(data.status==1){
            $('#trDetailsArea').html(data.html);
        }
        else{
            $('#trDetailsArea').html('');
            swMessageFromJs(data.m);
        }
    });
}

function payEmployeeSalaryInit(eID){
    emp=empDatas[eID];
    $('#eIDe').val(eID);
    $('#modalTitle').html('Pay Salary');
    $('#salaryPayInfoArea').show();
    $('#employe').val(emp.eName);
    $('#employeDesignation').val(emp.edTitle);
    $('#employeeSalayr').val(emp.eSalary);
    $('#employeeDue').val(emp.due);
    $('#employeeBalance').val(emp.balance);
    $("#payEmployeeSalaryModelAct").click();
}

function payEmployeeSalary(){
    var eID     = parse_int($('#eIDe').val());
    var pay     = parse_int($('#payAmount').val());
    var note    = $('#payNote').val();
    clearMessage();
    errorSet=0;
    if(eID<1){
        errorSet=1;
        swMessage('Please select employee for pay');
    }
    else if(pay<1){
        errorSet=1;
        swMessage('Invalid Payment Amount');
    }
    if(errorSet==0){
        $.post(ajUrl,{payEmployeeSalary:1,eID:eID,pay:pay,note:note},function(data){
            if(data.status==1){
                $('#emp_'+eID+' .balance').html(data.balance);
                $('#eIDe').val('');
                $('#employe').val('');
                $('#employeeSalayr').val('');
                $('#payAmount').val('');
                $('#payNote').val('');
                $('#payEmployeeSalaryModel').modal('toggle');
                select2Call();
                swMessageFromJs(data.m);
            }
            else{
                swMessageFromJs(data.m);
                $('#trDetailsArea').html('');
            }
        }); 
    }

}

function getSupplierDue(){
    var supID= parse_int($('#supID').val());
    var scID= parse_int($('#scID').val());
    if(supID>0){
        $.post(ajUrl,{getSupplierDue:1,supID:supID,scID:scID},function(data){
            if(data.status==1){
                $('#sPayable').val(data.subBalance);
            }
            else{
                $('#sPayable').val('');
            }
            swMessageFromJs(data.m);
        });            
    }
}
function extarAmountChange(type,amount){
    var extraAmount=parse_int($('#extraAmount').val());
    if(type=='p'){
        extraAmount+=amount;
    }
    else if(type=='m'){
        extraAmount-=amount;
    }
    else if(type=='c'){
        extraAmount=0;
    }
    if(extraAmount<0){
        extraAmount=0;
    }
    $('#extraAmount').val(extraAmount);
    $('#eextam').html(extraAmount);
    $('#eextam').show();
    setTimeout(function(){
        $('#eextam').hide();
        dealerSaleRowTotal()
        },1000);
}
function headForIncExpSet(hID,hType,hStatus){
    var ch=0;
    if(hStatus==true){
        ch=1;
    }
    $.post(ajUrl,{headForIncExpSet:1,hID:hID,hType:hType,hStatus:ch},function(data){
        //t(data)
    })
}
function product_box_price(p){
    let unit_price=parse_float(p.s);
    if(p.box_unit_quantity>0){
        unit_price=unit_price*p.box_unit_quantity;
    }
    return unit_price;
}
function saleProductChange(pID){
    if(typeof(productData[pID])!=="undefined"){
        let p=productData[pID];
        console.log('p',p);
        $('#VAT').val(0);
        $('#quantity_label').html(p.u);
        $('#qtyLabel').html(p.u);
        if(p.box_unit_quantity>0){
            $('#quantity_label').html(p.box_unit+BOX_SEPARATOR+p.u);  
            $('#product_sale_price').val(product_box_price(p)+BOX_SEPARATOR+p.s);
            $('#product-tp').val(product_box_price(p)+BOX_SEPARATOR+p.s);
        }
        else{
            $('#product-tp').val(p.s);
            $('#product_sale_price').val(p.s);
            $('#quantity_label').html(p.u);  
            $('#qtyLabel').html(p.u);  
        }
    }
    else{
        $('#product_sale_price').val('');
        $('#quantity_label').html('-');
        $('#qtyLabel').html('-');
    }
    saleRowTotal()
}

function saleRowTotal(){
    let product_tp=new Big(parse_float($('#product-tp').val()));
    let salePrice=new Big(parse_float($('#product_sale_price').val()));
    let qty=new Big(parse_float($('#product_quantity').val())); 
    let pID = parse_int($('#pID').val());
    let discount = new Big(0);  
    let VAT_percent=new Big(0);
    let VAT=new Big(0);
    if(typeof(productData[pID])!=="undefined"){
        let p=productData[pID];
        discount = product_tp.minus(salePrice);
        let free_qty =new Big(0);
        VAT_percent=new Big(p.VAT);
        if(p.get_one_free>0){
            if(qty>p.get_one_free){
                free_qty = qty.div(p.get_one_free);
            } 
        }
        if(free_qty.gt(0)){
            $('#freeQTY').val(free_qty);
        }
        else{
            $('#freeQTY').val('');
        }
    }
    else{
        $('#freeQTY').val('');
    }
    let total_discount = qty.times(discount);

    let total=salePrice.times(qty);

    if(VAT_percent.gt(0)){
        VAT=total.times(VAT_percent).div(100);
        //VAT= Math.round((VAT_percent*total)/100)
    }
    total=total.plus(VAT);
    $('#product-discount').val(format_big_number(total_discount,6));
    $('#VAT').val(format_big_number(VAT,6));
    $('#total').val(format_big_number(total,6));
}
function gift_row_total(){
    let product_tp  = new Big(parse_float($('#salePrice').val()));
    let qty         = new Big(parse_float($('#product-gift-qty').val()));
    let total       = product_tp.times(qty);
    $('#total').val(total.toFixed(2));
}


function productSaleAddToCart(){
    let category    = parse_int($('#category').val());
    let subCategory = parse_int($('#subCategory').val());
    let pID         = parse_float($('#pID').val());
    errorSet=0;
    //let salePrice_tp= parse_float($('#product-tp').val());
    //let salePrice   = parse_float($('#product_sale_price').val());
    let box_qty     = parse_int($('#product_box_quantity').val());

    //let qty         = parse_float($('#product_quantity').val());
    //let free_qty    = parse_int($('#freeQTY').val());
    // let VAT         = parse_float($('#VAT').val());

    let qty=new Big(parse_float($('#product_quantity').val()));
    let VAT=new Big(parse_float($('#VAT').val()));
    let salePrice=new Big(parse_float($('#product_sale_price').val()));
    let free_qty=new Big(parse_float($('#freeQTY').val()));
    let salePrice_tp=new Big(parse_float($('#product-tp').val()));

    if(USE_PRODUCT_CATEGORY==1 && category<=0 ){swMessage('Please select a category');errorSet=1;}
    else if(USE_PRODUCT_CATEGORY==1 && typeof(categoryData[category])== "undefined"){swMessage('invalid category');errorSet=1;}
        else if(USE_PRODUCT_CATEGORY==1 && subCategory<=0 ){swMessage('Please select a sub category');errorSet=1;}
            else if(USE_PRODUCT_CATEGORY==1 && typeof(categoryData[category].childCategory[subCategory])== "undefined"){swMessage('invalid category');errorSet=1;}
                else if(pID<=0){swMessage('Please select a product');errorSet=1;}
                    else if(qty.lt(0)){swMessage('Please enter a valid Quantity');errorSet=1;}
                        else if(free_qty<0){swMessage('Please enter a valid free Quantity');errorSet=1;}

    if(errorSet==0){
        let p=productData[pID];

        let total_qty=qty.plus(free_qty);
        if(total_qty.gt(p.st)){swMessage('Product stock out!');errorSet=1;}
        else if(salePrice.gt(p.s)){swMessage('Invalid sale price.');errorSet=1;}
            else if(p.s>0&&salePrice.eq(0)){swMessage('Invalid discount.');errorSet=1;}
                else{
                    let actual_sp=salePrice_tp.minus(salePrice);
                    let total_discount = actual_sp.times(qty);

                    let sub_total=(salePrice_tp.times(qty));
                    let total=(salePrice.times(qty)).plus(VAT);
                    let c,sc;
                    if(USE_PRODUCT_CATEGORY==1){
                        let c=categoryData[category];
                        let sc=categoryData[category].childCategory[subCategory];
                    }
                    let id='pd_'+pID+'_'+autoInc;autoInc++;
                    $('#saleProductsTr .pID').val(pID);
                    if(USE_PRODUCT_CATEGORY==1){
                        $('#saleProductsTr .category').html(c.title)
                        $('#saleProductsTr .subCategory').html(sc.title)
                    }
                    console.log('total_qty',qty.toFixed(2));
                    $('#saleProductsTr .pTitle').html(p.t);
                    $('#saleProductsTr .unTitle').html(p.u);
                    $('#saleProductsTr .salePrice-tp ').html(format_big_number(salePrice_tp,6));
                    $('#saleProductsTr .salePrice ').html(format_big_number(salePrice,6));
                    $('#saleProductsTr .qty').html(format_big_number(qty,6));
                    $('#saleProductsTr .free_qty').html(format_big_number(free_qty,6));
                    $('#saleProductsTr .total_qty').html(format_big_number(total_qty,6));
                    $('#saleProductsTr .total').html(format_big_number(total,6));
                    $('#saleProductsTr .discount').html(format_big_number(total_discount,6));
                    $('#saleProductsTr .VAT').html(format_big_number(VAT,6));
                    $('#saleProductsTr .sub_total').html(format_big_number(sub_total,6));
                    $('#saleProductsTr .remove').attr('onclick','remove_row_by_id(\''+id+'\');productSaleSubTotal()')
                    const saleProductsTr=$('#saleProductsTr').html();
                    //        t('purchaseProductsTr')
                    //        t(purchaseProductsTr)
                    $('#saleProducts').append('<tr id="'+id+'">'+saleProductsTr+'</tr>');
                    $('#pID').val('');
                    $('#productSalePrice').val('');
                    $('#product_quantity').val('');
                    $('#product-tp').val('');
                    $('#product-discount').val('');
                    $('#total').val('');
                    $('#productSaleqty').val('');
                    $('#freeQTY').val('');
                    $('#subCategory').val('');
                    $('#category').val('');
                    select2Call();
                    let tr_sl_start=1;$('#saleProducts .autoSerial').each(function(){$(this).html(tr_sl_start);tr_sl_start++;});
                    productSaleSubTotal();
                }
    }
}
function productsSaleDraft(){
    buttonLoading('productsSaleDraft');
    let draftID = parse_int($('#draftID').val());
    let cID = parse_int($('#cID').val());
    let saleDate = $('#saleDate').val();
    // let subTotal=parse_float($('#subTotal').html());
    let discount=parse_float($('#saleDiscount').val());
    // let netPayable=parse_float($('#netPayable').html());
    // console.log('subTotal');
    let order_no = $('#order_no').val();
    let order_date = $('#order_date').val();

    let products= {};
    let count=0;
    errorSet=0;
    if(errorSet==0){
        $('#saleProducts .pID').each(function(a,b){
            if(errorSet==0){
                let tID=$(this).closest('tr').attr('id');
                let pID             = parse_int($('#'+tID+' .pID').val());
                let qty             = parse_float($('#'+tID+' .qty').html());
                let free_qty        = parse_float($('#'+tID+' .free_qty').html());
                let total_qty       = parse_float($('#'+tID+' .total_qty').html());
                let salePrice_tp    = parse_float($('#'+tID+' .salePrice-tp').html());
                let salePrice       = parse_float($('#'+tID+' .salePrice').html());
                let product_discount= parse_float($('#'+tID+' .discunt').val());
                let VAT           = parse_float($('#'+tID+' .VAT').html());
                if(product_discount>salePrice){swMessage('Invalid discount.');errorSet=1;}
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
        });
        if(count==0){errorSet=1;swMessage('select a product');}
        else{

            let postData={
                productsSaleDraft:1,
                draftID:draftID,
                cID:cID,
                saleDate:saleDate,
                products:products,
                 order_no:order_no,
                order_date:order_date,
                discount:discount
            };
            console.log(postData);
            $.ajax({
                type:'post',
                url:ajUrl,
                data:postData,
                success:function(data){
                    button_loading_destroy('productsSaleDraft','Draft');
                    if(typeof(data.status)  !== "undefined"){ 
                        if(data.status==1){
                            let newLink="?mdl=sale&draftID="+data.draft_id;
                            window.history.pushState("", "", newLink);
                            select2Call();
                        }
                        swMessageFromJs(data.m);
                    }
                    else{
                        swMessage(AJAX_ERROR_MESSAGE); 
                    }
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) { 
                    button_loading_destroy('productsSaleDraft','Draft');
                    swMessage(AJAX_ERROR_MESSAGE); 
                }
            });
        }
    }
    if(errorSet==1){
        button_loading_destroy('productsSaleDraft','Draft');   
    }
}
function productDiscount(id){
    let main_id = $(id).closest('tr').attr('id');
    let discount = parse_float($(id).val());
    let sub_total = parse_float($('#'+main_id+' .sub_total').html());
    let total =  sub_total - discount;
    $('#'+main_id+' .total').html(total);
    productSaleSubTotal();
}
function productSaleSubTotal(){
    let total=new Big(0);
    let total_product_discount=new Big(0);
    let total_vat=new Big(0);
    $('#saleProducts .pID').each(function(a,b){
        let tID=$(this).closest('tr').attr('id');
        //let pID = $('#'+tID+' .pID').val();
        //let p=productData[pID]; 
        //let qty=parse_float($('#'+tID+' .qty').html());
        total = total.plus(parse_float($('#'+tID+' .total').html()));
        total_product_discount = total_product_discount.plus(parse_float($('#'+tID+' .discount').html()));
        total_vat = total_vat.plus(parse_float($('#'+tID+' .VAT').html()));
        // let tt=salePrice*qty;
        //t('#'+tID+' .unitPrice');

    });
    $('#subTotal').html(format_big_number(total,6));
    $('#total_product_discount').html(format_big_number(total_product_discount,6));
    $('#product_discount_footer').val(format_big_number(total_product_discount,6));
    $('#total_VAT').html(format_big_number(total_vat,6));
    let discount=parse_float($('#saleDiscount').val());
    let netPayable=total-discount;
    $('#netPayable').html(netPayable.toFixed(2));
}

function delete_voucher(voucher_id){
    if(voucher_id>0){
        buttonLoading(`delete_voucher_${voucher_id}`);
        $.ajax({
            type:'post',
            url:ajUrl,
            data:{voucherRemove:1,voucher_id:voucher_id},
            success:function(data){
                button_loading_destroy(`delete_voucher_${voucher_id}`,'');
                if(typeof(data.status)  !== "undefined"){ 
                    if(data.status==1){
                        $(`#delete_voucher_${voucher_id}`).remove()
                    }
                    setTimeout(() => {swMessageFromJs(data.m);}, 500);
                    
                }
                else{
                    
                    setTimeout(() => {swMessage(AJAX_ERROR_MESSAGE); }, 500);
                }   
            },
            error: function(data) { 
                button_loading_destroy(`delete_voucher_${voucher_id}`,'Remove');
                setTimeout(() => {swMessage(AJAX_ERROR_MESSAGE); }, 500);
            }
        });
    }
}




$(document).on('keyup','#return-discount',function(){product_return_subtotal();});
$(document).on('keyup','#discount,#VAT,#AIT',function(){purchaseSubTotal();});
$(document).on('keyup change','#qty,#salePrice,#extra_cost',function(){purchaseRowTotal();});
$(document).on('keyup change','#unitPrice,#qty,#salePrice',function(){purchaseRowTotal();});
$(document).on('keyup change','#targetQuantity,#yield',function(){productionSubTotal();});
$(document).on('keyup change','#product_quantity,#product_sale_price',function(){saleRowTotal();});  
$(document).on('change','#category',function(){mainCategoryChange();purchaseSubCategoryChange()}); 
$(document).on('change','#subCategory',function(){purchaseSubCategoryChange()});    
