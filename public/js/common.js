// Show loading state on a button
function buttonLoading(className, loadingText = "<i class='fa fa-spinner fa-spin'></i>") {
    const $btn = $('.' + className);
    $btn.addClass("disable");
    $btn.prop("disabled", true);
    $btn.html(loadingText);
}

// Restore button to normal state
function button_loading_destroy(className, originalText) {
    const $btn = $('.' + className);
    $btn.prop("disabled", false);
    $btn.removeClass("disable");
    $btn.html(originalText);
}
function are_you_sure(ty,title='Are you sure?',id=0,callback){
    swal({
        title: title,
        text: '',
        type: "info",
        showCancelButton: true,
        confirmButtonColor: "#27ae60",
        confirmButtonText: "Continue",
        cancelButtonText: "Cancel",
        }, function (isConfirm) {

            if (isConfirm) {
                if(ty==1){
                    callback(id);

                }
                else{
                    window.location = callback; 
                }
            } 
    });
}
function remove_row_by_id(rowId){
    swal({
        title: 'Are you sure to delete tnis?',
        text: '',
        type: "info",
        showCancelButton: true,
        confirmButtonColor: "#27ae60",
        confirmButtonText: "Continue",
        cancelButtonText: "Cancel",
        }, function (isConfirm) {
            if (isConfirm) {
                $('#'+rowId).remove();
            } 
    });
    //var checkConfirm = confirm('Are you sure to delete tnis?');if(checkConfirm == true){$('#'+rowId).remove();}
    return true;//return currently not use
}
function parse_float(rString){rString=parseFloat(rString);if(isNaN(rString))rString=0;return rString;}
function parse_int(rString){rString=parseInt(rString);if(isNaN(rString))rString=0;return rString;}
function select2Call(){$("select.select2").select2();}
function select2CallForClass(className){$("."+className).select2();}
function select2CallForCustom(selector){$(selector).select2();}
function swMessageFromJs(msgData){
    var msgHtml='';
    $.each(msgData,function(c,ms){
        $.each(ms,function(tp,m){
            if(tp==1){
                if(msgHtml!=''){msgHtml=msgHtml+"\n";}
                msgHtml=msgHtml+m;
            }
        });
    });
    if(msgHtml!=''){
        swMessage(msgHtml);
    }
    else{
        // console.log('empty sw message from js call');
    }
}
function swMessage(msgData){
    if(msgData){
        swal({
            title: msgData,
            timer: 5000, 
            },
        );
    }

}

function createMsgFromJson(jsonMsg,showId){
    if(showId==undefined){showId='message_show_box';}
    //    t(showId)
    //    t(jsonMsg)
    var msgHtml='';
    var i=0;
    $.each(jsonMsg,function(c,ms){
        mt='';mm=''
        $.each(ms,function(tp,m){
            if(tp==0){mt=m;}
            else if(tp==1){mm=m;}
        });
        //<div class="alert alert-"> <?=$msgNo.$msg?> </div>
        if(mt=='s'){msgHtml+= '<div class="alert alert-success">'+mm+'</div>';}
        else if(mt=='e'){msgHtml+= '<div class="alert alert-danger">'+mm+'</div>';}
            else if(mt=='i'){msgHtml+= '<div class="alert alert-info">'+mm+'</div>';}
    });
    creatMessageForHtml(msgHtml,showId,'html')
}
function creatMessageForHtml(msgData,showId,msgDataType){
    if(showId==undefined)showId='message_show_box';
    if(msgDataType==undefined)msgDataType='m';

    if(msgDataType=='m'){
        var msgHtml = '<div class="alert alert-danger">'+msgData+'</div>';
    }else{msgHtml=msgData;}
    $('#'+showId).html(msgHtml);
    $('#'+showId).show();
    $('html, body').animate({scrollTop: $( "#"+showId ).position().top}, 'slow');
}
function clearMessage(showId){
    if(showId==undefined)showId='message_show_box';
    //    t(showId)
    $('#'+showId).hide();
    //        alert(showId);
    return true;
}