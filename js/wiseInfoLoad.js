function base_wise_customer(base_id,customer_append_id='',current_customer_id='',all_select_option_title='All'){
    if(customer_append_id==''){customer_append_id='customer_id'};
    $('#'+customer_append_id).html('<option value="">'+all_select_option_title+'</option>');
    if(base_id>0){
        if(typeof(base_customers[base_id])!='undefined'){
            $.each(base_customers[base_id],function(a,b){
                let sel = '';

                $('#'+customer_append_id).append('<option '+sel+' value="'+b.id+'">'+b.name+'</option>');
            });

        }
    }
    else{
        $.each(base_customers,function(mpo_id,b){
            $.each(base_customers[mpo_id],function(a,b){
                let sel = '';
                $('#'+customer_append_id).append('<option '+sel+' value="'+b.id+'">'+b.name+'</option>');
            }); 
        }); 
    }
    if(current_customer_id!=''){
        $('#'+customer_append_id).val(current_customer_id);
    }
    select2Call();
}
function base_wise_doctor(base_id,all_select_option_title='Select doctor'){
    $('#doctor_id').html('<option value="">'+all_select_option_title+'</option>');
    if(base_id>0){
        if(typeof(base_doctors[base_id])!='undefined'){
            $.each(base_doctors[base_id],function(a,b){
                let sel = '';
                if(typeof(doctor_id)!='undefined' && doctor_id>0&&b.id==doctor_id){
                    sel = 'selected';
                }
                $('#doctor_id').append('<option '+sel+' value="'+b.id+'">'+b.name+'</option>');
            });

        }
    }
    else if(all_select_option_title=='All'){
        $.each(base_doctors,function(base_id,b){
            $.each(base_doctors[base_id],function(a,b){
                let sel = '';
                if(typeof(doctor_id)!='undefined' && doctor_id>0&&b.id==doctor_id){
                    sel = 'selected';
                }
                $('#doctor_id').append('<option '+sel+' value="'+b.id+'">'+b.name+'</option>');
            }); 
        }); 
    }
    select2Call();

}
function base_wise_user(base_id,all_select_option_title='Select user',user_id=0){
    $('#user_id').html('<option value="">'+all_select_option_title+'</option>');
    if(base_id>0){
        if(typeof(base_wise_users[base_id])!='undefined'){
            $.each(base_wise_users[base_id],function(a,b){
                let sel = '';
                if( user_id>0&&b.id==user_id){
                    sel = 'selected';
                }
                $('#user_id').append('<option '+sel+' value="'+b.id+'">'+b.name+'</option>');
            });

        }
    }
    else if(all_select_option_title=='All'){
        $.each(base_wise_users,function(base_id,b){
            $.each(base_wise_users[base_id],function(a,b){
                let sel = '';
                if(user_id>0&&b.id==user_id){
                    sel = 'selected';
                }
                $('#user_id').append('<option '+sel+' value="'+b.id+'">'+b.name+'</option>');
            }); 
        }); 
    }
    select2Call();

}
function base_wise_bazar(base_id,all_select_option_title='Select bazar',bazar_id=0){
    $('#bazar_id').html('<option value="">'+all_select_option_title+'</option>');
    if(base_id>0){
        if(typeof(bazar_wise_bazars[base_id])!='undefined'){
            $.each(bazar_wise_bazars[base_id],function(a,b){
                let sel = '';
                if( bazar_id>0&&b.id==bazar_id){
                    sel = 'selected';
                }
                $('#bazar_id').append('<option '+sel+' value="'+b.id+'">'+b.title+'</option>');
            });

        }
    }
    else if(all_select_option_title=='All'){
        $.each(bazar_wise_bazars,function(base_id,b){
            $.each(bazar_wise_bazars[base_id],function(a,b){
                let sel = '';
                if(bazar_id>0&&b.id==bazar_id){
                    sel = 'selected';
                }
                $('#bazar_id').append('<option '+sel+' value="'+b.id+'">'+b.title+'</option>');
            }); 
        }); 
    }
    select2Call();

}
function base_wise_area(base_id,all_select_option_title='All'){
    $('#area_id').html('<option value="">'+all_select_option_title+'</option>');
    if(base_id>0){
        if(typeof(base_areas[base_id])!='undefined'){
            $.each(base_areas[base_id],function(a,b){
                let sel = '';
                if(typeof(area_id)!='undefined' && area_id>0&&b.id==area_id){
                    sel = 'selected';
                }
                $('#area_id').append('<option '+sel+' value="'+b.id+'">'+b.title+'</option>');
            });

        }
    }
    else if(all_select_option_title=='All'){
        $.each(base_areas,function(base_id,b){
            $.each(base_areas[base_id],function(a,b){
                let sel = '';
                if(typeof(area_id)!='undefined' && area_id>0&&b.id==area_id){
                    sel = 'selected';
                }
                $('#area_id').append('<option '+sel+' value="'+b.id+'">'+b.title+'</option>');
            }); 
        });
    }
    select2Call();

}