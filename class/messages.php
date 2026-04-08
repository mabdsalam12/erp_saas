<?php
    function set_message($message,$type='e'){
        setMessage(1,"$message.");
    }
    function setMessage($msgNo){
        if(!is_array($msgNo)){$msgNo=func_get_args();}
        $_SESSION['msg'][] = $msgNo;
    }
    function setErrorMessage($e){
        setMessage(133,$e);
    }   
    function m($messageId){
        $w=array();
        $w[1]     = "e|@1@.";//
        $w[2]     = 's|@1@';
        $w[3]     = 'i|@1@';
        $w[4]     = 's|';
        $w[5]     = 'e|';
        $w[6]     = 'i|Your login on hold. please contact admin.';
        $w[7]     = 'e|@1@ already exist.';
        $w[8]     = 'i|This route currently unavailable.';
        $w[9]     = 'e|@1@ already sold.';
        $w[10]    = 'e|@1@ reserved by another one.';
        $w[11]    = 'i|';
        $w[12]    = 'i|This request already processed.';
        $w[13]    = 's|';
        $w[14]    = 'i|@1@ delete successfully.';
        $w[15]    = 'i|';
        $w[16]    = 'i|';
        $w[17]    = 'e|';
        $w[18]    = 'e|';
        $w[19]    = 'e|';
        $w[20]    = 'i|This invoice already canceled.';
        $w[21]    = 'e|@1@ already canceled.';
        $w[22]    = 'i|This invoice already canceled.';
        $w[23]    = 'e|Your licence currently suspend.';
        $w[24]    = 'e|';
        $w[25]    = 'e|';
        $w[26]    = 'e|@1@ not ready for ticket sale. Please contact your administrator. Code 26';
        $w[27]    = 'e|@1@ not ready for pocket counter add. Please contact your administrator. Code 27';
        $w[28]    = 'e|@1@ not ready for departure expence. Please contact your administrator. Code 28';
        $w[29]    = 's|New @1@ added successfully.';
        $w[30]    = 's|@1@ updated successfully.';
        $w[31]    = 'e|This departure cost already updted. If need update please contact administrator.';
        $w[36]    = 'e|@1@ field is required.';
        $w[37]    = 'e|Invalid @1@ edit request.';
        $w[45]    = 'e|Invalid username or password.';//
        $w[46]    = 'i|Some problem to your login. Please try again or contact your administartor.';//
        $w[47]    = 'e|Invalid login.';
        $w[48]    = 'i|Your login expired. Please login again for security.';
        $w[49]    = 's|You are successfully logout.';
        $w[50]    = 'e|@1@ not ready for @2@ entry. Please contact your administrator. Code 50';
        $w[51]    = 'e|Please select a valid parent';
        $w[52]    = '';
        $w[53]    = 'e|.';
        $w[54]    = 'e|Password and confirm password not match.';
        $w[55]    = 'e|Username not available.';
        $w[56]    = 'e|.';
        $w[57]    = 'e|.';
        $w[58]    = 'e|.';
        $w[59]    = '';
        $w[60]    = 's|.';
        $w[61]    = 'e|Please upload .png file.';
        $w[62]    = 'e|File size maximum 300KB';
        $w[63]    = 'e|Invalid @1@.';
        $w[64]    = '';
        $w[65]    = 'e|Please select a valid customer.';
        $w[66]    = 'e|Some problem there. Please try again later.';
        $w[67]    = "e|Charge cannot be greater than amount.";
        $w[68]    = 's|New deposit insert successfully.';
        $w[69]    = 'e|[ <i>@1@</i> ] multiple in list.';
        $w[70]    = 'e|[ <i>@1@</i> ] stock quantity not enough. @2@ available.. Request @3@';
        $w[71]    = 'e|[ <i>@1@</i> ] free stock quantity not enough. @2@ available. Request @3@';
        $w[72]    = 'e|[ <i>@1@</i> ] sale price not set.';
        $w[73]    = 'e|Invalid comission for <i>@1@</i>.';
        $w[74]    = 'e|[ <i>@1@</i> ] total quantity cannot be ziro.';
        $w[75]    = 'e|Please select minimum one product for sale.';
        $w[76]    = 'e|Net sale amount cannot be zero.';
        $w[77]    = 'e|Due amount cannot be less then ziro.';
        $w[78]    = 'e|Customer not enough ballance.';
        $w[79]    = 'e|Invoice number already used.';
        $w[80]    = 's|Sale invoice post successfully.';
        $w[81]    = 'e|You are not authorize to set this type role.';
        $w[82]    = 's|Reserve Quotation created Successfully.';
        $w[83]    = 'e|Invalid product for this invoice';
        $w[84]    = 's|Sale return entry successfully.';
        $w[85]    = 'e|Invalid sale details request.';
        $w[86]    = 'i|Any product not set for return.';
        $w[87]    = 'e|Invalid purchase details request.';
        $w[88]    = 's|Purchase return entry successfully.';
        $w[89]    = 'e|Maximum pay amount @1@.';
        $w[90]    = 'e|Not accept pay amount zero.';
        $w[91]    = 'e|Invalid sale payment request';
        $w[92]    = "e|It's already paid.";
        $w[93]    = 's|New payment recive successfully.';
        $w[94]    = 'e|Customer not have enouth balance.';
        $w[95]    = 's|Deposit return successfully.';
        $w[96]    = 'e|@1@ is currently inactive.';
        $w[97]    = 'e|Please select some valide product for transfer.';
        $w[98]    = 'e|Your cannot deliver any product to same branch.';
        $w[99]    = 'e|Invalid quantity for [<i>@1@</i>]';
        $w[101]   = 's|Your Transfer has is set in draft and waiting for authorized persosnals approvel.';
        $w[102]   = 'e|Please select minimum one product for Transfer.';
        $w[103]   = 's|Product transfer successfully.';
        $w[104]   = 's|Purchase Edit Done Successfully.';
        $w[105]   = 's|Purchase invoice save to draft.';
        $w[106]   = 's|Sale invoice save to draft.';
        $w[107]   = 'e|Quotation ID number already used.';
        $w[108]   = 'e|Please select minimum one product for sale quotation.';
        $w[109]   = 'e|Grand Total amount cannot be zero.';
        $w[110]   = 's|Sale quotation saved.';
        $w[111]   = 'e|@1@ cannot be empty.';
        $w[112]   = 'e|Quotation ID field is required.';
        $w[113]   = 'e|Part no already used.';
        $w[114]   = 's|Stock to reserve transfer successfully.';
        $w[115]   = 's|New adjut note entry successfully.';
        $w[116]   = 'e|Allready back this reserve';
        $w[117]   = 'e|Invalid quantity. @1@ available, @2@ request.';
        $w[118]   = 's|Product reserve to stock transfer successfully.';
        $w[119]   = 's|Delivery note update successfully.';
        $w[120]   = 'e|[ <i>@1@</i> ] delivery remining @2@. Request @3@.';
        $w[122]   = 'e|Allready partially draft';
        $w[123]   = 'e|Allready submited.';
        $w[124]   = 'e|Ledger list cannot be empty.';
        $w[125]   = 'e|Total Debit and Credit balance must be equal for each branch.';
        $w[126]   = 's|New Voucher @1@ successfully.';
        $w[127]   = 'e|@1@ not available.';
        $w[128]   = 'e|@1@ Chart of accounts not set';
        $w[129]   = 'e|This branch not ready for any transection. Please contact Administrator';
        $w[130]   = 'e|Paid amount cannot be greater then invoice amount';
        $w[131]   = 'i|Your request URL currently unavailable';
        $w[132]   = 'e|.';
        $w[133]   = 'e|Error Code @1@.';
        $w[134]   = 'e|';
        $w[135]   = 'e|';
        $w[136]   = 'e|';
        $w[137]   = 'e|';
        $w[138]   = 'e|';
        $w[139]   = 'i|';
        $w[140]   = 'i|';
        $w[141]   = 'i|আপনি একজন সুপার এডমিন হিসেবে লগইন করেছেন। খুব জরুরী কিছু না হলে কিছু এন্ট্রি দিবেননা।';
        $w[142]   = 'e|';
        $w[143]   = 's| Purchase added successfully';
        $w[144]   = 's|';
        $w[145]   = 'e|';
        $w[146]   = 'e|You are not authorize to @1@.';//
        $w[147]   = 'e|Inactive user status contact your administrator.';//
        $w[148]   = 's|Voucher removed.';
        $w[149]   = 'e|This voucher not manually removeable.';
        $w[150]   = 'i|@1@ remove successfully';
        $w[151]   = 'i|This Ledger opening balance not editable.';
        $w[152]   = 'e|All Branch Debit and credit must be equal.';
        $w[152]   = 'e|Data not found.';

        $w[222]   = '<h3>Data not found</h3>';
        $w[223]   = 'e|Please upload valid file like (*.jpg, *.jpeg, *.png, *.gif).';
        $w[224]   = 'e|Maximum file size 2MB. Pleace check size for @1@';
        $w[225]   = 's|Password Change successfully.';


        return $w[$messageId];
    }
    function jArrayMessageSet(&$jArray){
        $jArray['m']=show_msg('y');
    }
    function show_msg($jsonShow='No'){
        if($jsonShow=='Y'||$jsonShow=='y')$jsonShow='Yes';
        if($jsonShow=='Yes'){$rt=array();}
        if(isset($_SESSION['msg'])){
            $sc = count($_SESSION['msg']);
            for($i=0; $i<$sc; $i++){
                $mCode = $_SESSION['msg'][$i];
                if(!is_array($mCode)){
                    $ms = m($mCode);
                    $m = explode('|',$ms);get_message($m[1],$m[0]);
                }
                else{
                    $co = count($mCode);
                    $ms = m($mCode[0]);
                    $m = explode('|',$ms);
                    $mainMessage = $m[1];
                    for($j=1; $j<$co; $j++){$mainMessage = str_ireplace('@'.$j.'@',$mCode[$j],$mainMessage);}
                    if($jsonShow!='Yes'){get_message($mainMessage,$m[0]);}
                    else{$rt[]=array($m[0],$mainMessage);}
                }
            }
            unset($_SESSION['msg']);
        }
        if($jsonShow=='Yes'){return $rt;}
    }
    function get_message($msg, $cat,$no=''){
        if ($cat == 'w'){
            $type='warning';
            $t = "Warning";
        }
        elseif ($cat == 's'){
            $type='success';
            $t = 'Success';
        }
        elseif ($cat == 'e'){
            $type='danger';
            $t = 'Error';
        }
        else{
            $type='info';
            $t = 'Information';
        }
        if($no){$msgNo = $t.' No '.$no.': ';}else{$msgNo = '';}
?>
    <div class="alert alert-<?php echo $type;?>"> <?=$msgNo.$msg?> </div>
<?php
    }

    function showJsonMessage(&$jArray=[]){
        $jArray['m']=show_msg('y');
    }