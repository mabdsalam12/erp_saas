
function newDPSMemberChange(mID){
    if(mID!=""){
        $.ajax({
            type:'post',
            url:ajUrl,
            data:{DPSMemberChange:1,mID:mID},
            success:function(data){
                if(data.status==1){
                    var m=data.member;
                    $("#mIDNo").val(m.mIDNo);
                    $("#mMobile").val(m.mMobile);
                    $("#mNID").val(m.mNID);  
                }
                else{
                    $("#mIDNo").val("");
                    $("#mMobile").val('');
                    $("#mNID").val(''); 
                }
            }
        });
    }else{
        $("#mIDNo").val("");
        $("#mMobile").val('');
        $("#mNID").val(''); 
    }

}
function newDPSPackageChange(dpID){
    var p = dpID;
    if(p!=''){
        $("#ampMonth").val(packages[p].dpAmount);
    }else{
        $("#ampMonth").val(''); 
    }
}
function newDPS(){
    if(productHideBtn==0){t(productHideBtn);return false;}
    var mID     = parse_int($('#mID').val());
    var dpID    = parse_int($('#dpID').val());
    var refmID  = parse_int($('#refmID').val());
    var startY  = parse_int($('#startY').val());
    var startM  = parse_int($('#startM').val());
    var lpY     = parse_int($('#lpY').val());
    var lpM     = parse_int($('#lpM').val());
    var lprY    = parse_int($('#lprY').val());
    var lprM    = parse_int($('#lprM').val());
    var share   = parse_int($('#share').val());
    var openingBalance= parse_int($('#openingBalance').val());
    var openingProfit= parse_float($('#openingProfit').val());
    errorSet=0;
    if(mID<1){
        errorSet=1;
        swMessage('Please select member');
    }
    else if(dpID<1){
        errorSet=1;
        swMessage('Please select package');
    }
    else if(share<1){
        errorSet=1;
        swMessage('Please Enter Share');
    }
    else if(startY<1){
        errorSet=1;
        swMessage('Please select start year');
    }
    else if(startM<1){
        errorSet=1;
        swMessage('Please select start month');
    }
    else if(lpY<1){//একাউন্ট করার সময় অন্তত এক মাসের বিল পে করবে
        errorSet=1;
        swMessage('Please select last pay year');
    }
    else if(lpM<1){
        errorSet=1;
        swMessage('Please select last pay month');
    }
    if(errorSet==0){
        $.post(ajUrl,{newDPS:1,mID:mID,refmID:refmID,dpID:dpID,share:share,startY:startY,startM:startM,lpY:lpY,lpM:lpM,lprY:lprY,lprM:lprM,openingBalance:openingBalance,openingProfit:openingProfit},function(data){
            if(data.status==1){
                //console.log(data.printLink);
                window.open(data.printLink);
                $('#mID').val('');
                $('#mIDNo').val('');
                $('#mMobile').val('');
                $('#mNID').val('');
                $('#refmID').val('');
                $('#dpID').val('');
                $('#ampMonth').val('');
                $('#startY').val('');
                $('#startM').val('');
                $('#lpY').val('');
                $('#lpM').val('');
                $('#lprY').val('');
                $('#lprM').val('');
                $('#openingBalance').val('');
                $('#openingProfit').val('');
                select2Call();
            }
            swMessageFromJs(data.m);
        }); 
    }
}
function dpsList(){
    $('#reportArea').html(loadingImage);
    var daNo    = $('#daNo').val();
    var mID     = parse_int($('#smID').val());
    var dpID    = parse_int($('#sdpID').val());
    var refmID  = parse_int($('#srefmID').val());
    $.post(ajUrl,{dpsList:1,mID:mID,refmID:refmID,dpID:dpID,daNo:daNo},function(data){
        if(data.status==1){
            $('#reportArea').html(data.html);
        }
        swMessageFromJs(data.m);
    }); 
}
function dpsTranHistory(daID,mID){
    $('#dpsReportArea').html(loadingImage);
    daID=daID;
    mID=mID;
    $.post(ajUrl,{dpsTranHistory:1,daID:daID,mID:mID},function(data){
        if(data.status==1){
            $('#dpsReportArea').html(data.html);
            $("#mName").html(data.mName);
            $("#daNot").html(data.daNo);
        }
        swMessageFromJs(data.m);
        $('#dpsHistoryModelBtn').click();
    });
}
function dpsPayInit(daID){
    $('#daID').val('');
    $('#damID').val('');
    $('#daAmount').val('');
    $('#due_advence').val('');
    $.post(ajUrl,{dpsPayInit:1,daID:daID},function(data){
        if(data.status==1){
            var Amount = data.dpAmount;
            var mName = data.mName;
            var due_advence = data.due_advence;
            $('#daID').val(daID);
            $('#damID').val(mName);
            $('#daAmount').val(Amount);
            $('#due_advence').val(due_advence);
        }
        swMessageFromJs(data.m);
    }); 
    $('#dpsPayModelBtn').click();
}
function dpsPay(){
    if(productHideBtn==0)return false;
    var daID=parse_int($('#daID').val());
    var payMonth=parse_int($('#payMonth').val());
    var payNote=($('#payNote').val());
    if(daID>0){
        if(payMonth>0){
            $.post(ajUrl,{dpsPay:1,daID:daID,payMonth:payMonth,payNote:payNote},function(data){
                if(data.status==1){
                    dpsList();
                    $('#daID').val('');
                    $('#payNote').val('');
                    $('#payMonth').val('');
                    $('.close').click();
                    window.open(data.printLink, '_blank').focus();
                    select2Call();
                }
                swMessageFromJs(data.m);
            }); 
        }
        else{
            swMessage('Invalid Pay month');
        }
    }
    else{
        swMessage('Invalid DPS');
    }
}

function newFDR(){
    if(productHideBtn==0)return false;
    var mID         = parse_int($('#mID').val());
    var amount      = parse_int($('#amount').val());
    var profit      = parse_float($('#profit').val());
    var refmID      = parse_int($('#refmID').val());
    var faProfit    = parse_int($('#faProfit').val());
    var profitInstallmentType = parse_int($('#profitInstallmentType').val());
    var profitInstallmentAmount = parse_int($('#profitInstallmentAmount').val());
    var startY  = parse_int($('#startY').val());
    var startM  = parse_int($('#startM').val());
    var clY     = parse_int($('#clY').val());
    var clM     = parse_int($('#clM').val());
    errorSet=0;
    if(mID<1){
        errorSet=1;
        swMessage('Please select member');
    }
    else if(amount<1){
        errorSet=1;
        swMessage('Please enter FDR amount');
    }
    else if(profit<=0){
        errorSet=1;
        swMessage('Please enter Profit Percent(%)');
    }
    else if(profitInstallmentType<=0){
        errorSet=1;
        swMessage('Please enter Profit Installment Type');
    }
    else if(startY<1){
        errorSet=1;
        swMessage('Please select start year');
    }
    else if(startM<1){
        errorSet=1;
        swMessage('Please select start month');
    }
    else if(clY<1){
        errorSet=1;
        swMessage('Please select FDR closing year');
    }
    else if(clM<1){
        errorSet=1;
        swMessage('Please select FDR closing month');
    }
    if(errorSet==0){
        var fdrData=
        {
            newFDR:1,
            mID:mID,
            refmID:refmID,
            amount:amount,
            profit:profit,
            profitInstallmentType:profitInstallmentType,
            profitInstallmentAmount:profitInstallmentAmount,
            startY:startY,
            startM:startM,
            faProfit:faProfit,
            clY:clY,
            clM:clM
        };
        $.post(ajUrl,fdrData,function(data){
            if(data.status==1){

                $('#mID').val('');
                $('#mIDNo').val('');
                $('#mMobile').val('');
                $('#mNID').val('');
                $('#refmID').val('');
                $('#dpID').val('');
                $('#ampMonth').val('');
                $('#startY').val('');
                $('#startM').val('');
                $('#lpY').val('');
                $('#lpM').val('');
                window.open(data.printLink, '_blank').focus();
                select2Call();
            }
            swMessageFromJs(data.m);
        }); 
    }
}