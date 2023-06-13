<?php


/**
 * User Controller
 */

const TABLE = 'streams';
const PK = 'id';
const AK = ['user_id'];
const SELECTABLE = ['id', 'user_id', 'title', 'ingestionType', 'frameRate', 'resolution', 'streamKey', 'created_at', 'updated_at'];
const REQUIRED_TO_INSERT = ['user_id', 'title', 'ingestionType', 'frameRate', 'resolution'];
const INSERT_FIELDS = ['id', 'user_id', 'title', 'ingestionType', 'frameRate', 'resolution', 'streamKey'];
// If PK is in INSERT_FIELDS then it should be VARCHAR of size 13 or more and uniqid will be generated while creation
const CREATED_AT = 'created_at';
const GENERATE_PK = false;
const PASSWORDS = [];
const UPDATABLE = [];
const UPDATED_AT = 'updated_at';


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
            // Show
            if (isset($request->query['pk'])) {
                $select = $request->connection->prepare("SELECT " . '`' . implode('`, `', SELECTABLE) . '`' . " FROM " . TABLE . " WHERE " . PK . "=?");
                $select->bind_param(Database::get_type_char($request->query['pk']), $request->query['pk']);
            } elseif (isset($request->query['ak']) && isset($request->query['key']) && in_array($request->query['key'], AK)) {
                $select = $request->connection->prepare("SELECT " . '`' . implode('`, `', $selectables) . '`' . " FROM " . TABLE . " WHERE " . $request->query['key'] . "=?");
                $select->bind_param(Database::get_type_char($request->query['ak']), $request->query['ak']);
            } // Index
            else {
                if (isset($request->query['cols'])) {
                    $selectables = explode(',', $request->query['cols']);
                    $select_query = "SELECT " . '`' . implode('`, `', $selectables) . '`' . " FROM " . TABLE;
                } else {
                    $select_query = "SELECT " . '`' . implode('`, `', SELECTABLE) . '`' . " FROM " . TABLE;
                }
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

            /**
             * CUSTOM ACTION : CREATE STREAM USING YOUTUBE API
             */
            $client = session_check($request->connection);
            $stream =  createStream(
                $request->connection,
                $client,
                $request->body['title'],
                $request->body['ingestionType'],
                $request->body['frameRate'],
                $request->body['resolution'],
            );
            $request->body['id'] = $stream['id'];
            $request->body['streamKey'] = $stream['key'];

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
                echo json_encode(array('status_code' => 1, 'result'=>['key'=>$stream['key'], 'pk'=>$stream['id']]));
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

            if (!isset($request->body['pk'])) {
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
                    } else {
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
                if (isset($request->body['pk'])) $request->query['pk'] = $request->body['pk'];
                $client = session_check($request->connection);
                delete_stream($request->connection, $client, $request->body['pk']);
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
    } else {
        echo json_encode($check);
        exit();
    }

}

function createStream($connection, $client, string $title, string $ingestionType, string $frameRate, string $resolution){
    try{
        $service = new Google_Service_YouTube($client);
        $liveStream = new Google_Service_YouTube_LiveStream();
        $cdnSettings = new Google_Service_YouTube_CdnSettings();
        $cdnSettings->setFrameRate($frameRate);
        $cdnSettings->setIngestionType($ingestionType);
        $cdnSettings->setResolution($resolution);
        $liveStream->setCdn($cdnSettings);
        $liveStreamSnippet = new Google_Service_YouTube_LiveStreamSnippet();
        $liveStreamSnippet->setTitle($title);
        $liveStream->setSnippet($liveStreamSnippet);
        $streamInsert = $service->liveStreams->insert('snippet,cdn', $liveStream);
        return ['id'=>$streamInsert->id,'key'=>$streamInsert->cdn->ingestionInfo->streamName];
    }catch (Exception $e){
        log_error($connection, 'YOUTUBE_STREAM_CREATE', $e);
        echo json_encode(array('status_code'=>0, 'error_code'=>'YOUTUBE_STREAM_CREATE', 'error_desc'=>$e->getMessage(), 'error_msg'=>json_decode($e->getMessage(), true)['error']['message']));
        exit();
    }
}

function delete_stream($connection, $client, string $id){
    try{
        $service = new Google_Service_YouTube($client);
        $service->liveStreams->delete($id);
    }catch (Exception $e){
        log_error($connection, 'YOUTUBE_STREAM_DELETE', $e);
        echo json_encode(array('status_code'=>0, 'error_code'=>'YOUTUBE_STREAM_DELETE', 'error_desc'=>$e->getMessage(), 'error_msg'=>json_decode($e->getMessage(), true)['error']['message']));
        exit();
    }
}

function sync($request){
    $allowed_methods = ['GET'];
    $check = $request->verify_method($allowed_methods);
    if ($check['status_code']) {
        if ($_SESSION['type']=='youlive_admin' || $_SESSION['type']=='admin'){
            $client = session_check($request->connection);
            $service = new Google_Service_YouTube($client);
            $queryParams = [
                'maxResults' => 50,
                'mine' => true
            ];
            do{
                $response = $service->liveStreams->listLiveStreams('id,snippet,cdn', $queryParams);
                foreach ($response->items as $stream){
                    $query = $request->connection->prepare("INSERT IGNORE INTO streams (id, user_id, title, ingestionType, frameRate, resolution, streamKey) 
                                VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $query->bind_param('sssssss', $stream->id, $_SESSION['id'], $stream->snippet->title, $stream->cdn->ingestionType, $stream->cdn->frameRate, $stream->cdn->resolution, $stream->cdn->ingestionInfo->streamName);
                    $query->execute();
                }
            }
            while (!empty($response->nextPageToken));
            echo json_encode(array('status_code'=>1));
        }else{
            echo json_encode(array(
                'status_code' => 401,
                'error_code' => 'UNAUTHORISED',
                'error_desc' => "Unauthorised to do this action",
                'error_msg' => 'Oops, you seems less privileged!'
            ));
            exit();
        }
    }else{
        echo json_encode($check);
        exit();
    }
}