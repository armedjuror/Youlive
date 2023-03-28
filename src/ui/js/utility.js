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