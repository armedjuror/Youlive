<?php

/**
 * User Controller
 */

const TABLE = 'finance';
const PK = 'id';
const AK = ['channel_id', 'counterparty', 'created_by'];
const SELECTABLE = ['channel_id', 'id', 'description', 'amount', 'created_at', 'created_by', 'method', 'counterparty', 'updated_at'];
const REQUIRED_TO_INSERT = ['channel_id', 'description', 'amount', 'created_by', 'method', 'counterparty'];
const INSERT_FIELDS = ['channel_id', 'id', 'description', 'amount', 'created_by', 'method', 'counterparty'];
// If PK is in INSERT_FIELDS then it should be VARCHAR of size 13 or more and uniqid will be generated while creation
const CREATED_AT = 'created_at';
const GENERATE_PK = true;
const PASSWORDS = [];
const UPDATABLE = ['description', 'amount', 'created_by', 'method', 'counterparty'];
const UPDATED_AT = 'updated_at';

//if ($_SESSION['type']!='admin'){
//    echo json_encode(array('status_code'=>401, 'error_code'=>'UNAUTHORISED', 'error_desc'=>'Unauthorised access!', 'error_msg'=>'Oops, you seems less privileged!'));
//    exit();
//}


/**
 * @param Request $request
 * select channel_id, id, type, email, `name`, joined_at, last_login_at
 * update channel_id, type, email, `name`, `password`, last_login_at
 * key id
 * @return void
 */
function main(Request $request)
{
    $allowed_methods = [
        'GET', // index, show (if key is passed)
        'POST', //create
        'PUT', // update
        'DELETE', // delete
    ];

    $check = $request->verify_method($allowed_methods);

    if ($check['status_code']) {

        if ($request->method === 'GET') {

            if (isset($request->query['cols'])){
                $selectables = explode(',' , $request->query['cols']);
            }else{
                $selectables = SELECTABLE;
            }

            // Show
            if (isset($request->query['pk'])) {
                $select = $request->connection->prepare("SELECT " . '`' . implode('`, `', $selectables) . '`' . " FROM " . TABLE . " WHERE " . PK . "=?");
                $select->bind_param(Database::get_type_char($request->query['pk']), $request->query['pk']);
            } elseif (isset($request->query['ak']) && isset($request->query['key']) && in_array($request->query['key'], AK)) {
                $select = $request->connection->prepare("SELECT " . '`' . implode('`, `', $selectables) . '`' . " FROM " . TABLE . " WHERE " . $request->query['key'] . "=?");
                $select->bind_param(Database::get_type_char($request->query['ak']), $request->query['ak']);
            } // Index
            else {
                $select_query = "SELECT " . '`' . implode('`, `', $selectables) . '`' . " FROM " . TABLE;
                $offset = $request->query['offset'] ?? null;
                $limit = $request->query['limit'] ?? null;
                if ($offset && $limit && is_int($offset) && is_int($limit)) {
                    $select = $request->connection->prepare($select_query . " LIMIT ? OFFSET ?");
                    $select->bind_param('ii', $limit, $offset);
                } elseif ($limit && is_int($limit)) {
                    $select = $request->connection->prepare($select_query . " LIMIT ?");
                    $select->bind_param('i', $limit);
                } else {
                    $select = $request->connection->prepare($select_query);
                }
            }

            if ($select->execute()) {
                $result = $select->get_result();
                $data = $result->fetch_all(MYSQLI_ASSOC);
                echo json_encode(array(
                    'status_code' => 1,
                    'result' => $data
                ));
            } else {
                log_error($request->connection, 'DB_SELECT', new Exception($request->connection->error));
                echo json_encode(array(
                    'status_code' => 0,
                    'error_code' => 'DB_SELECT',
                    'error_desc' => $request->connection->error,
                    'error_msg' => 'Oops, something went wrong!'
                ));
            }

            exit();
        } // Index, Show
        else if ($request->method === 'POST') {
            foreach (REQUIRED_TO_INSERT as $var) {
                if (!isset($request->body[$var])) {
                    echo json_encode(array(
                        'status_code' => 0,
                        'error_code' => 'INVALID_REQUEST',
                        'error_desc' => "Required parameter/s missing!",
                        'error_msg' => "Required parameter/s missing! Required Parameters: " . (implode(", ", REQUIRED_TO_INSERT))
                    ));
                    exit();
                }
                if (in_array($var, PASSWORDS)) {
                    $request->body[$var] = password_hash($request->body[$var], PASSWORD_DEFAULT);
                } else {
                    $request->body[$var] = trim($request->body[$var]);
                }
            }

            $create_data = [];
            $type_string = '';
            foreach (INSERT_FIELDS as $var) {
                if (GENERATE_PK && $var == PK) {
                    $pk = uniqid();
                    $create_data[] = &$pk;
                    $type_string .= 's';
                } else {
                    $create_data[] = &$request->body[$var];
                    $type_string .= Database::get_type_char($request->body[$var]);
                }
            }


            $insert = $request->connection->prepare('INSERT IGNORE INTO ' . TABLE . ' 
                    ( ' . '`' . implode('`, `', INSERT_FIELDS) . '`' . ', `' . CREATED_AT . '`) 
                    VALUE (
                        '. implode(', ', array_fill(0, count(INSERT_FIELDS), '?')) .', NOW()
                    )
                    ');
            call_user_func_array(array($insert, "bind_param"), array_merge(array($type_string), $create_data));
            if ($insert->execute()) {
                echo json_encode(array('status_code' => 1));
            } else {
                log_error($request->connection, 'DB_INSERT', new Exception($request->connection->error));
                echo json_encode(array(
                    'status_code' => 0,
                    'error_code' => 'DB_INSERT',
                    'error_desc' => $request->connection->error,
                    'error_msg' => 'Oops, something went wrong!'
                ));
            }
            exit();
        } // Create
        else if ($request->method === 'PUT') {

            if (!isset($request->body['pk'])){
                echo json_encode(array(
                    'status_code' => 0,
                    'error_code' => 'INVALID_REQUEST',
                    'error_desc' => "Required Key :pk missing",
                    'error_msg' => 'Oops, pk required!'
                ));
                exit();
            }

            $update_keys = [];
            $update_values = [];
            $type_string = '';
            foreach (UPDATABLE as $var) {
                if (isset($request->body[$var]) && $request->body[$var]) {
                    $update_keys[] = $var;

                    if (in_array($var, PASSWORDS)) {
                        $hash = password_hash($request->body[$var], PASSWORD_DEFAULT);
                        $update_values[] = &$hash;
                        $type_string .= 's';
                    }else{
                        $update_values[] = &$request->body[$var];
                        $type_string .= Database::get_type_char($request->body[$var]);
                    }

                }

            }
            if (!empty($update_keys)){
                $update = $request->connection->prepare('UPDATE ' . TABLE . ' SET
                    ' . '`' . implode('`=?, `', $update_keys) . '`=?' . ', `'. UPDATED_AT .'`=NOW()
                    WHERE '.PK.'=?
                    ');
                $type_string.=Database::get_type_char($request->body['pk']);
                call_user_func_array(array($update, "bind_param"), array_merge(array($type_string), $update_values, array(&$request->body['pk'])));

                if ($update->execute()) {
                    echo json_encode(array('status_code' => 1));
                }
                else {
                    log_error($request->connection, 'DB_UPDATE', new Exception($request->connection->error));
                    echo json_encode(array(
                        'status_code' => 0,
                        'error_code' => 'DB_UPDATE',
                        'error_desc' => $request->connection->error,
                        'error_msg' => 'Oops, something went wrong!'
                    ));
                }
            }
            else{
                echo json_encode(array('status_code' => 1));
            }
            exit();
        } // Update
        else if ($request->method === 'DELETE') {
            if (isset($request->body['pk']) || isset($request->query['pk'])) {
                if (isset($request->body['pk']))$request->query['pk'] = $request->body['pk'];
                $delete = $request->connection->prepare("DELETE FROM " . TABLE . " WHERE " . PK . "=?");
                $delete->bind_param(Database::get_type_char($request->query['pk']), $request->query['pk']);
                if ($delete->execute()) {
                    echo json_encode(array('status_code' => 1));
                } else {
                    log_error($request->connection, 'DB_DELETE', new Exception($request->connection->error));
                    echo json_encode(array(
                        'status_code' => 0,
                        'error_code' => 'DB_DELETE',
                        'error_desc' => $request->connection->error,
                        'error_msg' => 'Oops, something went wrong!'
                    ));
                }
            } else {
                echo json_encode(array(
                    'status_code' => 0,
                    'error_code' => 'INVALID_REQUEST',
                    'error_desc' => "Required Key :PK missing",
                    'error_msg' => 'Oops, PK required!'
                ));
            }
            exit();
        } // Delete
    }
    else {
        echo json_encode($check);
        exit();
    }

}


/**
 * import using csv
 * @param Request $request
 * @return void
 */
function import(Request $request){
    $allowed_methods = [
        'POST',
    ];

    $check = $request->verify_method($allowed_methods);
    if ($check['status_code']) {
        $start_time = microtime(TRUE);
        $limit_reached = FALSE;
        $limit = $_SESSION['max_users'];
        $file_to_import = $_FILES['file'];
        $file_types = array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'text/plain');
        if (!empty($file_to_import['name']) && in_array($file_to_import['type'], $file_types)) {
            $reading_csv_file = fopen($file_to_import['tmp_name'], 'r');
            $user_count = $request->connection->query("SELECT COUNT(*) as `current` FROM users WHERE channel_id='" . $_SESSION['channel_id'] . "'" )->fetch_assoc();
            $allowable_quota = $limit-$user_count['current'];
            fgetcsv($reading_csv_file); // skipping header
            $imported_count = 0;
            $skipped_count = 0;
            $total = 0;
            $user_imports = [];
            $errors = [];
            $dummy = 'Pass'.date('Dyd');
            $dummy_password = password_hash(hash('sha256', $dummy), PASSWORD_DEFAULT);
            while (($user_data = fgetcsv($reading_csv_file)) !== FALSE){
                if ($limit!=-1 && $allowable_quota<=0){
                    $limit_reached = TRUE;
                    $skipped_count++;
                    $total++;
                    continue;
                }

                $name_check = User_Input::filtered_input($user_data[0], '', 255, 'Name of '.($total+1).'th row');
                $email_check = User_Input::filtered_input($user_data[1], 'email', 255, 'Email of '.($total+1).'th row');

                if (!$name_check['status_code']){
                    $errors[] = $name_check['error_msg'];
                    $skipped_count++;
                    $total++;
                    continue;
                }

                if (!$email_check['status_code']){
                    $errors[] = $email_check['error_msg'];
                    $skipped_count++;
                    $total++;
                    continue;
                }

                $user_id = uniqid();
                $user_imports[] = "('".$_SESSION['channel_id']."','".$user_id."', 'operator', '".$email_check['result']."', '".$name_check['result']."', '".$dummy_password."')";
                $total++;
                $allowable_quota--;
            }

            $user_import_chunks = array_chunk($user_imports, 100);

            foreach ($user_import_chunks as $arr){
                if (!mysqli_query($request->connection, "INSERT IGNORE INTO users (channel_id, id, type, email, `name`, `password`) VALUES ".implode(',', $arr))){
                    log_error($request->connection, 'DB_INSERT', new Exception($request->connection->error));
                    echo json_encode(array(
                        'status_code'=>0,
                        'error_code'=>'DB_INSERT',
                        'error_desc' => $request->connection->error,
                        'error_msg' => 'Oops, something went wrong!'
                    ));
                    exit();
                }
                $imported_count += count($arr);
            }

            $time_spend = microtime(TRUE) - $start_time;

            if ($limit_reached){
                $error = "Imported maximum number of Users!";
                echo json_encode(array('message'=> $error,
                    'imported' => $imported_count,
                    'skipped'=>$skipped_count,
                    "time_used"=>$time_spend,
                    'password'=>$dummy,
                    'errors'=>$errors,
                    'status_code'=>1,
                    'error_code'=>'LIMIT_EXCEEDED'
                ));
                exit();
            }else{
                echo json_encode(array(
                    'status_code'=>1,
                    'message'=>"Successfully completed the import",
                    'imported' => $imported_count,
                    'skipped'=>$skipped_count,
                    "time_used"=>$time_spend,
                    'password'=>$dummy,
                    'errors'=>$errors
                ));
                exit();
            }
        }else{
            $error = "File is either not selected or not csv.";
            echo json_encode(array(
                'status_code'=>0,
                'error_code'=>'FILE_ERROR',
                'error_desc'=>$error,
                'error_msg'=>$error
            ));
            exit();
        }
    }else{
        echo json_encode($check);
        exit();
    }
}

