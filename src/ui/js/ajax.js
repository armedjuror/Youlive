$('#user-import-form').submit(function (e){
    e.preventDefault()

    const success = response => {
        if (response.error_msg){
            triggerAlert('fa-list', '<span style="font-size: large;font-weight: bold;" >'+response.message+'</span><br>ERROR CODE: ' + response.error_code + '<ul><li>IMPORTED: '+ response.imported +'</li><li>SKIPPED: '+ response.skipped +'</li><li>TIME USED: '+ response.time_used +'</li><li> PASSWORD: '+ response.password +'</li></ul>', 'warning', true);
        }else{
            triggerAlert('fa-check-circle', '<span style="font-size: large;font-weight: bold;" >'+response.message+'</span><ul><li>IMPORTED: '+ response.imported +'</li><li>SKIPPED: '+ response.skipped +'</li><li>TIME USED: '+ response.time_used +'</li><li> PASSWORD: '+ response.password +'</li></ul>', 'success', true);
        }
    }

    make_ajax_call(this, $(this).attr('action'), '', success);
    $('#importModal').modal('hide');
})

$('#user-form').submit( function(e){
    e.preventDefault()
    const type = document.getElementById('userOperation').value
    const success = response => {

        let message = ''
        if (type==='add'){
            message = 'User Created!'
        }else{
            const userId = document.getElementById('userId').value
            if (type==='modify'){
                document.getElementById(userId + '-name').innerText = document.getElementById('name').value
                document.getElementById(userId + '-email').innerText = document.getElementById('email').value
                message = 'Successfully modified user!'
            }else if (type==='password'){
                message = 'Password changed successfully!'
            }else if (type==='delete'){
                document.getElementById(userId + '-row').remove()
                message = 'User deleted!'
            }
        }

        triggerAlert('fa-check-circle', message, 'success', type==='add')
    }

    if (type==='add'){
        make_ajax_call(this, $(this).attr('action'), '', success)
    }else if(type==='modify' || type==='password'){
        make_ajax_call(this, $(this).attr('action'), '', success, 'PUT')
    }else if(type==='delete'){
        make_ajax_call(this, $(this).attr('action'), '', success, 'DELETE')
    }
    $('#userModal').modal('hide')
    $('#AjaxLoader').fadeIn()
})

$('#stream-form').submit( function(e){
    e.preventDefault()
    const type = document.getElementById('streamOperation').value
    const success = response => {
        const dataTable = $('#dataTable').dataTable()
        let message = ''
        if (type==='add'){
            message = 'Stream Created!<br>Stream Key: ' + response.result.key
            // dataTable.row.add(['0', ])
        }else if (type==='delete'){
            id = document.getElementById('streamId').value
            document.getElementById(id + '-row').remove()
            message = 'Stream deleted!'
        }
        triggerAlert('fa-check-circle', message, 'success', type==='add')
    }

    if (type==='add') {
        make_ajax_call(this, $(this).attr('action'), '', success)
    }else if(type==='delete'){
        make_ajax_call(this, $(this).attr('action'), '', success, 'DELETE')
    }
    $('#streamModal').modal('hide')
    $('#AjaxLoader').fadeIn()
})

function sync_streams(){
    const success = response => {
        triggerAlert('fa-check-circle', 'Successfully synced!', 'success', true)
    }
    make_ajax_call('', 'api.php/streams/sync', 'Please wait...', success, 'GET')
}

function sync_events(){

    const success_0 = response => {
        const success = response => {
            triggerAlert('fa-check-circle', 'Successfully synced!', 'success', true)
        }
        make_ajax_call('', 'api.php/events/sync', 'Please wait...', success, 'GET')
    }

    make_ajax_call('', 'api.php/streams/sync', 'Please wait...', success_0, 'GET')

}

$('#event-form').submit( function(e){
    let file;
    const type = document.getElementById('eventOperation').value // Prevent form submission
    e.preventDefault()

    const success = response => {

        let message = ''
        if (type==='add'){
            message = 'Event Created!'
        }
        else {
            eventId = document.getElementById('eventId').value

            if (type==='edit'){
                document.getElementById(eventId + '-title').innerText = document.getElementById('title').value
                document.getElementById(eventId + '-scheduled_start_time').innerText = document.getElementById('scheduled_start_time').value
                document.getElementById(eventId + '-privacy_status').innerText = document.getElementById('privacy_status').value
                message = 'Successfully updated!'
            }else if (type==='user'){
                const created_by = document.getElementById('created_by')
                document.getElementById(eventId + '-name').innerText = created_by.options[created_by.selectedIndex].text
                message = 'User changed!'
            }else if (type==='payment'){
                document.getElementById(eventId + '-charge').innerText = document.getElementById('charge').value
                document.getElementById(eventId + '-contribution').innerText = document.getElementById('contribution').value
                message = 'Payment Details Updated!'
            }else if (type==='pay'){
                let status = document.getElementById('payment_status').value
                document.getElementById(eventId + '-payment_status').innerText = status
                document.getElementById(eventId + '-row').classList = ''
                if (status === 'pending'){
                    document.getElementById(eventId + '-row').classList.add('text-warning')
                }else if (status === 'dispute'){
                    document.getElementById(eventId + '-row').classList.add('text-danger')
                }
                message = 'Payment Status Updated!'
            }else if (type==='bind'){
                const stream = document.getElementById('stream')
                document.getElementById(eventId + '-stream').innerText = stream.options[stream.selectedIndex].text
                message = 'Stream bound to event!'
            }else if (type==='delete'){
                document.getElementById(eventId + '-row').remove()
                message = 'Event Deleted!'

            }
        }


        triggerAlert('fa-check-circle', message, 'success', type==='add');
    }

    if (type==='add'){
        file = $('#thumbnail')[0].files[0];
        checkUploadedFile(file, 'api.php/events')
    }
    else if (type==='edit' || type==='user' || type==='bind' || type==='payment'){
        make_ajax_call(this, 'api.php/events', '', success, 'PUT')
        $('#eventModal').modal('hide')
    }else if(type==='pay'){
        let status = document.getElementById('payment_status').value
        if (status === 'success'){
            if (confirm("Are you sure? Once marked success can't be reverted!")){
                make_ajax_call(this, 'api.php/events', '', success, 'PUT')
                $('#eventModal').modal('hide')
            }else{
                $('#AjaxLoader').fadeOut()
                return
            }
        }else{
            make_ajax_call(this, 'api.php/events', '', success, 'PUT')
            $('#eventModal').modal('hide')
        }
    }
    else if (type==='delete'){
        make_ajax_call(this, 'api.php/events', '', success, 'DELETE')
        $('#eventModal').modal('hide')
    }
    function checkUploadedFile(file, success_url) {
        let return_val = false
        const fileReader = new FileReader();
        fileReader.onload = function(e) {
            const image = new Image();
            image.onload = function() {
                const width = this.width;
                const height = this.height;
                const aspectRatio = width / height;
                const expectedAspectRatio = 16 / 9;
                const expectedWidth = 1280;
                const expectedHeight = 720;

                if (
                    (file.type === 'image/png' || file.type === 'image/jpeg') &&
                    aspectRatio === expectedAspectRatio &&
                    width === expectedWidth &&
                    height === expectedHeight
                ) {
                    make_ajax_call(document.getElementById('event-form'), success_url, 'Please Wait, Uploading might take time...', success, 'POST')
                    $('#eventModal').modal('hide')
                } else {
                    // File does not meet the requirements
                    $('#AjaxLoader').fadeOut()
                    document.getElementById('thumbnail-error').innerText = 'Please upload a PNG or JPEG image with a resolution of 1280 x 720 and an aspect ratio of 16:9'
                    document.getElementById('thumbnail-error').style.display = 'inline'
                    return_val = false
                    // alert('File is not valid. Please upload a PNG or JPEG image with a resolution of 1280 x 720 and an aspect ratio of 16:9.');
                }
            };
            image.src = e.target.result;
        };
        fileReader.readAsDataURL(file);
        return return_val
    }
    $('#AjaxLoader').fadeIn()
});

$('#finance-form').submit( function(e){
    e.preventDefault()
    const type = document.getElementById('financeOperation').value
    const success = response => {
        const dataTable = $('#dataTable').dataTable()
        let message = ''
        if (type==='add'){
            message = 'Record Created!'
        }else if (type==='edit'){
            message = 'Changes Saved!'
            id = document.getElementById('recordId').value
            document.getElementById(id + '-description').innerText = document.getElementById('description').value
            amount = parseFloat(document.getElementById('amount').value)
            if (amount >= 0 ){
                document.getElementById(id + '-amount').innerText = amount.toString()
                document.getElementById(id + '-amount').classList.remove('text-danger')
                document.getElementById(id + '-amount').classList.add('text-success')
            }else{
                document.getElementById(id + '-amount').innerText = (-1*amount).toString()
                document.getElementById(id + '-amount').classList.add('text-danger')
                document.getElementById(id + '-amount').classList.remove('text-success')
            }
            document.getElementById(id + '-method').innerText = document.getElementById('method').value
        }else if (type==='delete'){
            message = 'Record deleted!'
            id = document.getElementById('recordId').value
            document.getElementById(id + '-row').remove()
        }
        triggerAlert('fa-check-circle', message, 'success', type==='add')
    }

    if (type==='add') {
        make_ajax_call(this, $(this).attr('action'), '', success)
    }else if(type==='edit'){
        make_ajax_call(this, $(this).attr('action'), '', success, 'PUT')
    }else if(type==='delete'){
        make_ajax_call(this, $(this).attr('action'), '', success, 'DELETE')
    }
    $('#financeModal').modal('hide')
    $('#AjaxLoader').fadeIn()
})