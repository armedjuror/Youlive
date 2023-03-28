$('#user-import-form').submit(function (e){
    e.preventDefault()

    const success = response => {
        console.log(response);
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
        const dataTable = $('#dataTable').dataTable()
        let message = ''
        if (type==='add'){
            message = 'User Created!'
            dataTable.row.add(['0', ])
        }else if (type==='modify'){
            message = 'Successfully modified user!'
        }else if (type==='password'){
            message = 'Password changed successfully!'
        }else if (type==='delete'){
            message = 'User deleted!'
        }
        triggerAlert('fa-check-circle', message, 'success', true)
    }

    if (type==='add'){
        make_ajax_call(this, $(this).attr('action'), '', success)
    }else if(type==='modify' || type==='password'){
        make_ajax_call(this, $(this).attr('action'), '', success, 'PUT')
    }else if(type==='delete'){
        make_ajax_call(this, $(this).attr('action'), '', success, 'DELETE')
    }
    $('#userModal').modal('hide')
    $('#AjaxLoader').show()
})