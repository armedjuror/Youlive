function triggerAlert(icon, message, theme, reload=false, goto=''){
    document.getElementById('MessageModalIconContainer').innerHTML = '<i class="fa '+ icon +' fa-3x text-' + theme + '"></i>'
    document.getElementById('MessageModalTextContainer').innerHTML = '<span class="text-' + theme + '">' + message + '</span>'
    document.getElementById('MessageModalButtonContainer').innerHTML = '<button class="btn btn-' + theme + '" type="button" data-dismiss="modal">CLOSE</button>'
    $('#MessageModal').modal('show');
    if (reload) {
        $('#MessageModal').on('hidden.bs.modal', () => {
            location.reload();
        });
    }else if(goto !== ''){
        $('#MessageModal').on('hidden.bs.modal', () => {
            window.location.href = goto;
        });
    }
}

function make_ajax_call(
    form,
    url,
    loader_message,
    success_callback,
    type = 'POST',
    useDefaultLoader=true,
    parseResponse=true,
    isJquery=true,
    varList='',
    loader_message_id='',
    form_data = new FormData()
){
    document.getElementById('AjaxLoaderMessage').innerHTML = loader_message
    if (useDefaultLoader){
        $('#AjaxLoader').show()
    }
    if (isJquery){
        let formData
        if (form){
            formData = new FormData(form);
        }
        else{
            formData = form_data
        }
        $.ajax({
            type: type,
            url: url,
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            success: function (response) {
                document.getElementById('AjaxLoaderMessage').innerHTML = ''
                if (IN_DEVELOPMENT){
                    console.log(response)
                }
                if (parseResponse){
                    let jsonData;
                    try {
                        jsonData = JSON.parse(response)
                    } catch (e) {
                        triggerAlert('fa-exclamation-circle', '<span style="font-size: large;font-weight: bold;" >Oops, Something went wrong!</span><br>ERROR CODE: PLE_PARSE', 'danger');
                        $('#AjaxLoader').hide()
                    }
                    if (jsonData.status_code === 1) {
                        success_callback(jsonData)
                        $('#AjaxLoader').hide()
                    } else if (jsonData.status_code === 503) {
                        location.reload()
                    } else {
                        triggerAlert('fa-exclamation-circle', '<span style="font-size: large;font-weight: bold;" >'+ jsonData.error_msg +'</span><br>ERROR CODE: '+ jsonData.error_code, 'danger');
                        $('#AjaxLoader').hide()
                    }
                }
                else{
                    success_callback(response)
                    $('#AjaxLoader').hide()
                }
            }
        });
    }
    else{
        let request = new XMLHttpRequest()
        request.open(type, url, true)
        request.setRequestHeader("Content-type", "application/x-www-form-urlencoded")
        request.onreadystatechange = function () {
            if (request.readyState === 4 && request.status === 200) {
                document.getElementById('AjaxLoaderMessage').innerHTML = ''
                if (IN_DEVELOPMENT){
                    console.log(request.responseText)
                }
                if (parseResponse) {
                    let response_content
                    try {
                        response_content = JSON.parse(request.responseText)
                    }catch (e){
                        triggerAlert('fa-exclamation-circle', '<span style="font-size: large;font-weight: bold;" >Oops, Something went wrong!</span><br>ERROR CODE: PLE_PARSE', 'danger');
                        $('#AjaxLoader').hide()
                    }
                    if (response_content['status_code'] === 1) {
                        success_callback(response_content)
                        $('#AjaxLoader').hide()
                    } else if (response_content['status_code'] === 503) {
                        location.reload()
                    } else {
                        triggerAlert('fa-exclamation-circle', '<span style="font-size: large;font-weight: bold;" >'+ response_content['error_msg'] +'</span><br>ERROR CODE: '+ response_content['error_code'], 'danger');
                        $('#AjaxLoader').hide()
                    }
                }else{
                    success_callback(request.responseText)
                    $('#AjaxLoader').hide()
                }
            }
        }
        request.send(varList)
        if (!useDefaultLoader){
            document.getElementById(loader_message_id).innerHTML = loader_message
        }
    }
}

function triggerUserForm(object, type, user_id='', name='', email='', channel_id='') {
    if (type === 'add') {
        document.getElementById('userModalLabel').innerText = 'Add User'
        document.getElementById('user-form').innerHTML = '' +
            '<input type="hidden" value="add" id="userOperation" required>\n' +
            '<input type="hidden" value="' + channel_id + '" name="channel_id" required>\n' +
            '                        <div class="form-group">\n' +
            '                            <select name="type" required class="form-control">\n' +
            '                                <option value="" selected>---SELECT USER TYPE---</option>\n' +
            '                                <option value="admin">Admin</option>\n' +
            '                                <option value="operator">Operator</option>\n' +
            '                            </select>\n' +
            '                        </div>\n' +
            '                        <div class="form-group">\n' +
            '                            <input type="text" name="name" class="form-control" id="name" placeholder="Name" required>\n' +
            '                        </div>\n' +
            '                        <div class="form-group">\n' +
            '                            <input type="email" name="email" class="form-control" id="email" placeholder="Email" required>\n' +
            '                        </div>\n' +
            '                        <div class="form-group">\n' +
            '                            <input type="text" name="password" class="form-control" id="password" placeholder="Password" required>\n' +
            '                        </div>\n' +
            '                        <div class="form-group text-right">\n' +
            '                            <input type="submit" name="submit" class="btn btn-' + THEME + '" value="Submit">\n' +
            '                        </div>'
    }
    else if(type==='modify'){
        document.getElementById('userModalLabel').innerText = 'Modify User'
        document.getElementById('user-form').innerHTML = '<input type="hidden" value="modify" id="userOperation" required>\n' +
            '                        <input type="hidden" value="' + user_id + '" id="userId" name="pk" required>\n' +
            '                        <div class="form-group">\n' +
            '                            <input type="text" name="name" class="form-control" id="name" value="' + name + '" placeholder="Name" required>\n' +
            '                        </div>\n' +
            '                        <div class="form-group">\n' +
            '                            <input type="email" name="email" class="form-control" id="email" value="' + email + '" placeholder="Email" required>\n' +
            '                        </div>\n' +
            '                        <div class="form-group text-right">\n' +
            '                            <input type="submit" name="submit" class="btn btn-' + THEME + '" value="Modify">\n' +
            '                        </div>'
    }
    else if(type==='password'){
        document.getElementById('userModalLabel').innerText = 'Change Password'
        document.getElementById('user-form').innerHTML = '<input type="hidden" value="password" id="userOperation" required>\n' +
            '                        <input type="hidden" value="' + user_id + '" id="userId" name="pk" required>\n' +
            '                        <div class="form-group">\n' +
            '                            <input type="text" name="password" class="form-control" id="password" placeholder="New Password" required>\n' +
            '                        </div>\n' +
            '                        <div class="form-group text-right">\n' +
            '                            <input type="submit" name="submit" class="btn btn-' + THEME + '" value="Change Password">\n' +
            '                        </div>'
    }
    else if(type === 'delete'){
        document.getElementById('userModalLabel').innerText = 'Are you sure to delete?'
        document.getElementById('user-form').innerHTML = '<div class="alert alert-danger">\n' +
            '                            This is an irreversible action.\n' +
            '                        </div>\n' +
            '                        <input type="hidden" value="delete" id="userOperation" required>\n' +
            '                        <input type="hidden" value="' + user_id + '" id="userId" name="pk" required>\n' +
            '                        <div class="form-group text-right">\n' +
            '                            <input type="submit" name="submit" class="btn btn-' + THEME + '" value="Delete">\n' +
            '                        </div>'
        const rr = $(object).closest('tr');
        const dt = $('#dataTable').dataTable();
        console.log(rr.index())
    }
    $('#userModal').modal('show');
}
function triggerStreamForm(object, type, stream_id='', user_id='') {
    if (type === 'add') {
        document.getElementById('streamModalLabel').innerText = 'Create Stream'
        document.getElementById('stream-form').innerHTML = '' +
            '<input type="hidden" value="add" id="streamOperation" required>\n' +
            '<input type="hidden" value="' + user_id + '" name="user_id" required>\n' +
            '                        <div class="form-group">\n' +
            '                            <input type="text" name="title" class="form-control" id="title" placeholder="Title" required>\n' +
            '                        </div>\n' +
            '                        <div class="form-group">\n' +
            '                            <select name="ingestionType" required class="form-control">\n' +
            '                                <option value="" selected>---SELECT INGESTION TYPE---</option>\n' +
            '                                <option value="rtmp">rtmp</option>\n' +
            '                                <option value="dash">dash</option>\n' +
            '                                <option value="webrtc">webrtc</option>\n' +
            '                                <option value="hls">hls</option>\n' +
            '                            </select>\n' +
            '                        </div>\n' +
            '                        <div class="form-group">\n' +
            '                            <select name="frameRate" required class="form-control">\n' +
            '                                <option value="" selected>---SELECT FRAME RATE---</option>\n' +
            '                                <option value="30fps">30fps</option>\n' +
            '                                <option value="60fps">60fps</option>\n' +
            '                                <option value="24fps">24fps</option>\n' +
            '                            </select>\n' +
            '                        </div>\n' +
            '                        <div class="form-group">\n' +
            '                            <select name="resolution" required class="form-control">\n' +
            '                                <option value="" selected>---SELECT RESOLUTION---</option>\n' +
            '                                <option value="144p">144p</option>\n' +
            '                                <option value="240p">240p</option>\n' +
            '                                <option value="360p">360p</option>\n' +
            '                                <option value="480p">480p</option>\n' +
            '                                <option value="720p">720p</option>\n' +
            '                                <option value="1080p">1080p</option>\n' +
            '                                <option value="1440p">1440p</option>\n' +
            '                                <option value="2160p">2160p</option>\n' +
            '                            </select>\n' +
            '                        </div>\n' +
            '                        <div class="form-group text-right">\n' +
            '                            <input type="submit" name="submit" class="btn btn-' + THEME + '" value="Create">\n' +
            '                        </div>'
    }
    else if(type === 'delete'){
        document.getElementById('streamModalLabel').innerText = 'Are you sure to delete?'
        document.getElementById('stream-form').innerHTML = '<div class="alert alert-danger">\n' +
            '                            This is an irreversible action.\n' +
            '                        </div>\n' +
            '                        <input type="hidden" value="delete" id="streamOperation" required>\n' +
            '                        <input type="hidden" value="' + stream_id + '" id="streamId" name="pk" required>\n' +
            '                        <div class="form-group text-right">\n' +
            '                            <input type="submit" name="submit" class="btn btn-' + THEME + '" value="Delete">\n' +
            '                        </div>'
        const rr = $(object).closest('tr');
        const dt = $('#dataTable').dataTable();
        console.log(rr.index())
    }
    $('#streamModal').modal('show');
}
function triggerEventForm(object, type, event_id='', user_id='', channel_id='', title='', description='', scheduled_start_time='', privacy_status='') {
    if (type === 'add') {
        const success = response => {
            const streams = response.result;
            let options = '<option value="">Select Stream Key</option>'
            for(let i=0; i < streams.length; i++){
                options += '<option value="' + streams[i].id + '">' + streams[i].title + ' - ' + streams[i].streamKey + '</option>'
            }
            document.getElementById('eventModalLabel').innerText = 'Create Event'
            document.getElementById('event-form').innerHTML = '' +
                '<input type="hidden" value="add" id="eventOperation" required>\n' +
                '<input type="hidden" value="' + user_id + '" name="created_by" required>\n' +
                '                        <div class="form-group">\n' +
                '                            <input type="text" name="title" class="form-control" id="title" placeholder="Title" required>\n' +
                '                        </div>\n' +
                '                        <div class="form-group">\n' +
                '                            <textarea name="description" class="form-control" id="description" placeholder="Description" required></textarea>\n' +
                '                        </div>\n' +
                '                        <div class="form-group">\n' +
                '                            <label for="scheduled_start_time">Scheduled Start Time</label>\n'+
                '                            <input type="datetime-local" name="scheduled_start_time" class="form-control" id="scheduled_start_time" required>\n' +
                '                        </div>\n' +
                '                        <div class="form-group">\n' +
                '                            <label for="thumbnail">Thumbnail (Resolution: 1280 x 720)</label>\n'+
                '                            <input type="file" name="thumbnail" class="form-control" id="thumbnail" accept="image/jpeg, image/png" required>\n' +
                '                        </div>\n' +
                '                        <div class="form-group text-danger" id="thumbnail-error" style="display: none">\n' +
                '                        </div>\n' +
                '                        <div class="form-group">\n' +
                '                            <select name="privacy_status" required class="form-control">\n' +
                '                                <option value="" selected>Set Privacy Status</option>\n' +
                '                                <option value="public">Public</option>\n' +
                '                                <option value="private">Private</option>\n' +
                '                                <option value="unlisted">Unlisted</option>\n' +
                '                            </select>\n' +
                '                        </div>\n' +
                '                        <div class="form-group">\n' +
                '                            <select name="stream" class="form-control" id="stream" required>' + options + '</select>\n' +
                '                        </div>\n' +
                '                        <div class="form-group text-right">\n' +
                '                            <input type="submit" name="submit" class="btn btn-' + THEME + '" value="Create">\n' +
                '                        </div>'
        }
        make_ajax_call('', 'api.php/streams?ak='+user_id, '', success, 'GET')
    }
    else if(type === 'thumbnail'){
        document.getElementById('eventModalLabel').innerText = 'Change Thumbnail'
        document.getElementById('event-form').innerHTML = '' +
            '<input type="hidden" value="thumbnail" id="eventOperation" required>\n' +
            '                        <input type="hidden" value="' + event_id + '" id="eventId" name="pk" required>\n' +
            '                        <div class="form-group">\n' +
            '                            <input type="file" name="thumbnail" class="form-control" id="thumbnail" accept="image/jpeg, image/png" required>\n' +
            '                        </div>\n' +
            '                        <div class="form-group text-danger" id="thumbnail-error" style="display: none">\n' +
            '                        </div>\n' +
            '                        <div class="form-group text-right">\n' +
            '                            <input type="submit" name="submit" class="btn btn-' + THEME + '" value="Submit">\n' +
            '                        </div>'
    }
    else if(type === 'bind'){
        const success = response => {
            const streams = response.result;
            let options = '<option value="">Select Stream Key</option>'
            for(let i=0; i < streams.length; i++){
                options += '<option value="' + streams[i].id + '">' + streams[i].title + ' - ' + streams[i].streamKey + '</option>'
            }
            document.getElementById('eventModalLabel').innerText = 'Bind Stream'
            document.getElementById('event-form').innerHTML = '' +
                '<input type="hidden" value="bind" id="eventOperation" required>\n' +
                '                        <input type="hidden" value="' + event_id + '" id="eventId" name="pk" required>\n' +
                '                        <div class="form-group">\n' +
                '                            <select name="stream" class="form-control" id="stream" required>' + options + '</select>\n' +
                '                        </div>\n' +
                '                        <div class="form-group text-right">\n' +
                '                            <input type="submit" name="submit" class="btn btn-' + THEME + '" value="Bind">\n' +
                '                        </div>'
        }
        make_ajax_call('', 'api.php/streams?ak='+user_id, '', success, 'GET')
    }
    else if(type === 'user'){
        const success = response => {
            const users = response.result;
            let options = '<option value="">Select User</option>'
            for(let i=0; i < users.length; i++){
                options += '<option value="' + users[i].id + '">' + users[i].name + '</option>'
            }
            document.getElementById('eventModalLabel').innerText = 'Change User'
            document.getElementById('event-form').innerHTML = '' +
                '<input type="hidden" value="user" id="eventOperation" required>\n' +
                '                        <div class="form-group">\n' +
                '                        <input type="hidden" value="' + event_id + '" id="eventId" name="pk" required>\n' +
                '                            <select name="created_by" class="form-control" id="created_by" required>' + options + '</select>\n' +
                '                        </div>\n' +
                '                        <div class="form-group text-right">\n' +
                '                            <input type="submit" name="submit" class="btn btn-' + THEME + '" value="Bind">\n' +
                '                        </div>'
        }
        make_ajax_call('', 'api.php/users?cols=id,name&ak='+channel_id, '', success, 'GET')
    }
    else if(type === 'payment'){
        document.getElementById('eventModalLabel').innerText = 'Add Payment Details'
        document.getElementById('event-form').innerHTML = '' +
            '<input type="hidden" value="payment" id="eventOperation" required>\n' +
            '                        <input type="hidden" value="' + event_id + '" id="eventId" name="pk" required>\n' +
            '                        <div class="form-group">\n' +
            '                            <input type="number" step="any" name="charge" class="form-control" id="charge" placeholder="Price Charged" required>\n' +
            '                        </div>\n' +
            '                        <div class="form-group">\n' +
            '                            <input type="number" step="any" name="contribution" class="form-control" id="contribution" placeholder="Contribution to be paid" required>\n' +
            '                        </div>\n' +
            '                        <div class="form-group text-right">\n' +
            '                            <input type="submit" name="submit" class="btn btn-' + THEME + '" value="Save">\n' +
            '                        </div>'
    }else if(type === 'pay'){
        document.getElementById('eventModalLabel').innerText = 'Mark Payment Status'
        document.getElementById('event-form').innerHTML = '' +
            '<input type="hidden" value="pay" id="eventOperation" required>\n' +
            '                        <input type="hidden" value="' + event_id + '" id="eventId" name="pk" required>\n' +
            '                        <div class="form-group">\n' +
            '                            <select name="payment_status" id="payment_status" required class="form-control">\n' +
            '                                <option value="" selected>Mark Payment Status</option>\n' +
            '                                <option value="pending">Pending</option>\n' +
            '                                <option value="success">Success</option>\n' +
            '                                <option value="dispute">Dispute</option>\n' +
            '                            </select>\n' +
            '                        </div>\n' +
            '                        <div class="form-group">\n' +
            '                            <select name="method" id="method" class="form-control">\n' +
            '                                <option value="" selected>Payment Method</option>\n' +
            '                                <option value="GPay">GPay</option>\n' +
            '                                <option value="PhonePe">PhonePe</option>\n' +
            '                                <option value="Amazon Pay">Amazon Pay</option>\n' +
            '                                <option value="PayTM">PayTM</option>\n' +
            '                                <option value="Other UPI">Other UPI</option>\n' +
            '                                <option value="Bank Transfer">Bank Transfer</option>\n' +
            '                                <option value="Credit Card">Credit Card</option>\n' +
            '                                <option value="Debit Card">Debit Card</option>\n' +
            '                                <option value="Others">Others</option>\n' +
            '                            </select>\n' +
            '                        </div>\n' +
            '                        <div class="form-group text-right">\n' +
            '                            <input type="submit" name="submit" class="btn btn-' + THEME + '" value="Save">\n' +
            '                        </div>'
    }
    else if(type === 'edit'){
        document.getElementById('eventModalLabel').innerText = 'Edit Event'
        document.getElementById('event-form').innerHTML = '' +
            '                        <input type="hidden" value="edit" id="eventOperation" required>\n' +
            '                        <input type="hidden" value="' + event_id + '" id="eventId" name="pk" required>\n' +
            '                        <div class="form-group">\n' +
            '                            <input type="text" name="title" class="form-control" id="title" placeholder="Title" value="' + title + '" required>\n' +
            '                        </div>\n' +
            '                        <div class="form-group">\n' +
            '                            <textarea name="description" class="form-control" id="description" placeholder="Description" required>' + description + '</textarea>\n' +
            '                        </div>\n' +
            '                        <div class="form-group">\n' +
            '                            <label for="scheduled_start_time">Scheduled Start Time</label>\n'+
            '                            <input type="datetime-local" name="scheduled_start_time" class="form-control" id="scheduled_start_time" value="' + scheduled_start_time + '" required>\n' +
            '                        </div>\n' +
            '                        <div class="form-group">\n' +
            '                            <select name="privacy_status" id="privacy_status" required class="form-control">\n' +
            '                                <option value="">Set Privacy Status</option>\n' +
            '                                <option value="public">Public</option>\n' +
            '                                <option value="private">Private</option>\n' +
            '                                <option value="unlisteed">Unlisted</option>\n' +
            '                            </select>\n' +
            '                        </div>\n' +
            '                        <div class="form-group">\n' +
            '                            <label for="enableMonitorStream">Enable Monitor Stream</label> &nbsp; &nbsp;'+
            '                            <input type="checkbox" name="enableMonitorStream" id="enableMonitorStream" value="1" checked>\n' +
            '                        </div>\n' +
            '                        <div class="form-group">\n' +
            '                            <label for="enableMonitorStream">Broadcast Stream Delay in Ms</label>'+
            '                            <input type="text" name="broadcastStreamDelayMs" class="form-control" id="broadcastStreamDelayMs" placeholder="Broadcast Stream Delay" value="0">\n' +
            '                        </div>\n' +
            '                        <div class="form-group text-right">\n' +
            '                            <input type="submit" name="submit" class="btn btn-' + THEME + '" value="Submit">\n' +
            '                        </div>'
        document.getElementById('privacy_status').value = privacy_status
    }
    else if(type === 'delete'){
        document.getElementById('eventModalLabel').innerText = 'Are you sure to delete?'
        document.getElementById('event-form').innerHTML = '<div class="alert alert-danger">\n' +
            '                            This is an irreversible action.\n' +
            '                        </div>\n' +
            '                        <input type="hidden" value="delete" id="eventOperation" required>\n' +
            '                        <input type="hidden" value="' + event_id + '" id="eventId" name="pk" required>\n' +
            '                        <div class="form-group text-right">\n' +
            '                            <input type="submit" name="submit" class="btn btn-' + THEME + '" value="Delete">\n' +
            '                        </div>'
        const rr = $(object).closest('tr');
        const dt = $('#dataTable').dataTable();
        console.log(rr.index())
    }
    $('#eventModal').modal('show');
}

function triggerFinanceRecordForm(object, type, record_id='', description='', amount='', method='', user_id='', channel_id='') {
    if (type === 'add') {
        document.getElementById('financeModalLabel').innerText = 'Add Record'
        document.getElementById('finance-form').innerHTML = '' +
            '<input type="hidden" value="add" id="financeOperation" required>\n' +
            '                        <div class="form-group">\n' +
            '                            <input type="text" name="description" class="form-control" id="description" placeholder="Description: add `:::event_id` , if related to an event" required>\n' +
            '                        </div>\n' +
            '                        <div class="form-group">\n' +
            '                            <input type="number" name="amount" class="form-control" id="amount" placeholder="Amount (negative for expense)" step="any" required>\n' +
            '                        </div>\n' +
            '                        <div class="form-group">\n' +
            '                            <input type="hidden" name="created_by" class="form-control" id="created_by" value="' + user_id + '" required>\n' +
            '                            <input type="hidden" name="channel_id" class="form-control" id="channel_id" value="' + channel_id + '" required>\n' +
            '                            <input type="text" name="counterparty" class="form-control" id="counterparty" placeholder="Counterparty : User ID if user" required>\n' +
            '                        </div>\n' +
            '                        <div class="form-group">\n' +
            '                            <select name="method" id="method" class="form-control">\n' +
            '                                <option value="">Select Payment Method</option>\n' +
            '                                <option value="GPay" >GPay</option>\n' +
            '                                <option value="PhonePe">PhonePe</option>\n' +
            '                                <option value="Amazon Pay">Amazon Pay</option>\n' +
            '                                <option value="PayTM">PayTM</option>\n' +
            '                                <option value="Other UPI">Other UPI</option>\n' +
            '                                <option value="Bank Transfer">Bank Transfer</option>\n' +
            '                                <option value="Credit Card">Credit Card</option>\n' +
            '                                <option value="Debit Card">Debit Card</option>\n' +
            '                                <option value="Others">Others</option>\n' +
            '                            </select>\n' +
            '                        </div>\n' +
            '                        <div class="form-group text-right">\n' +
            '                            <input type="submit" name="submit" class="btn btn-' + THEME + '" value="Submit">\n' +
            '                        </div>'
    }
    else if(type==='edit'){
        let disable = ''
        const r = new RegExp(':::')
        if (r.test(description)){
            disable = 'readonly';
        }
        const methods = ['GPay', 'PhonePe', 'Amazon Pay', 'PayTM', 'Other UPI', 'Bank Transfer', 'Credit Card', 'Debit Card', 'Others']
        let options = '';
        for(i = 0; i < methods.length; i++){
            if (method === methods[i]){
                options += '<option value="' + methods[i] + '" selected>' + methods[i] + '</option>'
            }else{
                options += '<option value="' + methods[i] + '">' + methods[i] + '</option>'
            }
        }

        document.getElementById('financeModalLabel').innerText = 'Edit Record'
        document.getElementById('finance-form').innerHTML = '<input type="hidden" value="edit" id="financeOperation" required>\n' +
            '                        <input type="hidden" value="' + record_id + '" id="recordId" name="pk" required>\n' +
            '                        <div class="form-group">\n' +
            '                            <input type="text" name="description" class="form-control" id="description" value="' + description + '" placeholder="Description" required ' + disable + ' >\n' +
            '                        </div>\n' +
            '                        <div class="form-group">\n' +
            '                            <input type="number" name="amount" class="form-control" id="amount" step="any" value="' + amount + '" placeholder="Amount" required>\n' +
            '                        </div>\n' +
            '                        <div class="form-group">\n' +
            '                            <select name="method" id="method" class="form-control">\n' +
            '                                <option value="">Select Payment Method</option>\n' +
            options +
            '                            </select>\n' +
            '                        </div>\n' +
            '                        <div class="form-group text-right">\n' +
            '                            <input type="submit" name="submit" class="btn btn-' + THEME + '" value="Save">\n' +
            '                        </div>'
    }
    else if(type === 'delete'){
        document.getElementById('financeModalLabel').innerText = 'Are you sure to delete?'
        document.getElementById('finance-form').innerHTML = '<div class="alert alert-danger">\n' +
            '                            This is an irreversible action.\n' +
            '                        </div>\n' +
            '                        <input type="hidden" value="delete" id="financeOperation" required>\n' +
            '                        <input type="hidden" value="' + record_id + '" id="recordId" name="pk" required>\n' +
            '                        <div class="form-group text-right">\n' +
            '                            <input type="submit" name="submit" class="btn btn-' + THEME + '" value="Delete">\n' +
            '                        </div>'
        const rr = $(object).closest('tr');
        const dt = $('#dataTable').dataTable();
        console.log(rr.index())
    }
    $('#financeModal').modal('show');
}

