<?php
$general->pageHeader($rModule['title']);
$base = $db->selectAll('base','where status=1 order by code');
$customer_data=$smt->get_base_wise_all_customer();
$base_customers=$customer_data['base_customers'];
$doctors = $db->selectAll('doctor','','id,base_id,name');
$base_doctors=[];
if(!empty($doctors)){
    foreach($doctors as $d){
        $base_doctors[$d['base_id']][]=$d;
    }
}
?>
<script type="text/javascript">
    const base_customers=<?=json_encode($base_customers)?>;
    const base_doctors=<?=json_encode($base_doctors)?>;
    $(document).ready(function(){
        $('#base_id, #message_type').on('change',function(){
            const customer_or_doctor = $('#customer_or_doctor');
            customer_or_doctor.empty();
            const base_id = parse_int($('#base_id').val());
            const type = parse_int($('#message_type').val());
            if(type>0){
                const title = type==1?'Customer':'Doctor';
                customer_or_doctor.append(`<h2>${title}</h2>`);
                customer_or_doctor.append(`<div class="form-group row">
                    <div class="col-md-8">
                        <input class="form-control"  placeholder="Search ${title}" id="search" type="text" />
                    </div>`
                );
                $("#search").on("keyup", function () {
                    const value = $(this).val().toLowerCase();

                    $("#customer_or_doctor .form-check").filter(function () {
                        let checkbox = $(this).find("input[type='checkbox']");
                        let label = $(this).find("label").text().toLowerCase();

                        if (checkbox.is(":checked")) {
                            // Always show checked items
                            $(this).show();
                        } else {
                            // Show only if label matches search
                            $(this).toggle(label.indexOf(value) > -1);
                        }
                    });
                });

            }
            let dataList = [];
            if (type === 1 && base_customers[base_id]) {
                dataList = base_customers[base_id];
            } else if (type === 2 && base_doctors[base_id]) {
                dataList = base_doctors[base_id];
            }
            
            $.each(dataList, function (i, b) {
                customer_or_doctor.append(`
                    <div class="form-check">
                        <input class="form-check-input customer_or_doctor" 
                            type="checkbox" 
                            value="${b.id}" 
                            id="customer_or_doctor_${b.id}">
                        <label class="form-check-label ml-2" for="customer_or_doctor_${b.id}">
                            ${b.name}
                        </label>
                    </div>
                `);
            });
        });
    });
    $(document).ready(function(){
        getMessagePending()
    });
    function getMessagePending() {
        $.ajax({
            type: 'POST',
            url: ajUrl,
            data: { getMessagePending: 1 },
            dataType: 'json', // Expecting JSON
            timeout: 10000    // 10s timeout
        })
        .done(function (data) {
            // Validate response
            if (data && typeof data.status !== "undefined") {
                if (data.status == 1) {
                    $('#pending_message').text(data.pending_message || 0);

                    // If there are pending messages, poll again after 1 second
                    if (parseInt(data.pending_message) > 0) {
                        setTimeout(getMessagePending, 1000);
                        
                    }
                }
            } else {
                console.error(AJAX_ERROR_MESSAGE,data);
            }
        })
        .fail(function (jqXHR, textStatus, errorThrown) {
            console.error(AJAX_ERROR_MESSAGE);
        });
    }

    $(document).on('click','#message_save',message_save);

function message_save() {
    const message = $('#message').val().trim();
    const base_id = parseInt($('#base_id').val()) || 0;
    const type = parseInt($('#message_type').val()) || 0;

    // Validation
    if (!message) {
        swMessage('Invalid message');
        return;
    }
    if (base_id < 1) {
        swMessage('Select a base');
        return;
    }
    if (type < 1) {
        swMessage('Select a type');
        return;
    }

    // Collect selected IDs
    let ids = [];
    $('.customer_or_doctor:checked').each(function () {
        const id = parseInt($(this).val()) || 0;
        if (id > 0) ids.push(id);
    });

    if (ids.length < 1) {
        const title = (type === 1) ? 'Customer' : 'Doctor';
        swMessage(`Select at least one ${title}`);
        return;
    }

    buttonLoading('message_save');

    $.ajax({
        type: 'POST',
        url: ajUrl,
        dataType: 'json',
        data: {
            message_save: 1,
            message: message,
            base_id: base_id,
            type: type,
            ids: ids
        }
    })
    .done(function (data) {
        if (data && typeof data.status !== "undefined") {
            if (data.status == 1) {
                $('#customer_or_doctor').empty();
                $('#message').val('');
                $('#base_id').val('');
                $('#message_type').val('');
                select2Call();
            }
            swMessageFromJs(data.m);
        } else {
            swMessage(AJAX_ERROR_MESSAGE);
        }
    })
    .fail(function (jqXHR, textStatus, errorThrown) {
        swMessage(AJAX_ERROR_MESSAGE);
        console.error("AJAX error:", textStatus, errorThrown);
    })
    .always(function () {
        button_loading_destroy('message_save', 'Add');
    });
}
</script>
<div class="col-sm-12">
    <div class="white-box border-box">
        <div><?php show_msg();?></div>
        <div class="row">
            <div class="col-md-12">


            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-xs-4 col-md-4 col-sm-4">
                <h4 style="display: flex; justify-content: space-between; align-items: center;">
                    Pending Message
                    <span id="pending_message" style="color: red; font-weight: bold;">0</span>
                </h4>
                <?php $general->inputBoxTextArea('message','Message');?>
                <?php $general->inputBoxSelect($base,'Base','base_id','id','title');?>
                <?php $general->inputBoxSelect([['i'=>1,'t'=>'Customer'],['i'=>2,'t'=>'Doctor']],'Type','message_type','i','t');?>
                
            </div>
            <div class="col-xs-4 col-md-4 col-sm-4">
                <div id="customer_or_doctor" style="overflow-y: auto !important;height: 200px !important;"></div>
            </div>
             <div class="col-xs-4 col-md-4 col-sm-4">
                <div class="row">
                    <div class="col-sm-12">
                        <button id="message_save" class="m-2 btn btn-info waves-effect waves-light pull-left m-t-10 message_save">Add</button>
                    </div>
                </div>
             </div>

            
        </div>
    </div>
</div>

