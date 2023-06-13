<?php
/**
 * User Controller
 */

const TABLE = 'events';
const PK = 'id';
const AK = ['created_by'];
const SELECTABLE = [ 'id', 'stream', 'created_by', 'title', 'description', 'scheduled_start_time', 'privacy_status', 'thumbnail', 'etag', 'created_at', 'last_updated_at', 'charge', 'contribution', 'payment_status'];
const REQUIRED_TO_INSERT = ['stream', 'created_by', 'title', 'description', 'scheduled_start_time', 'privacy_status', 'thumbnail'];
const INSERT_FIELDS = ['id', 'stream', 'created_by', 'title', 'description', 'scheduled_start_time', 'privacy_status', 'thumbnail', 'etag'];
const CREATED_AT = 'created_at';
const GENERATE_PK = false;
const FILES = ['thumbnail'];
const PASSWORDS = [];
const UPDATABLE = ['created_by', 'stream', 'title', 'description', 'scheduled_start_time', 'privacy_status', 'thumbnail', 'etag', 'last_updated_at', 'charge', 'contribution', 'payment_status'];
const UPDATED_AT = 'last_updated_at';

function main(Request $request): void
{
    $allowed_methods = [
        'GET', // index, show (if key is passed)
        'POST', //create
        'PUT', // update
        'DELETE', // delete
    ];

    $check = $request->verify_method($allowed_methods);

    if ($check['status_code']) {

        /*****CUSTOM CONFIG: YOUTUBE : START****/
        $client = session_check($request->connection);
        $youtube = new Google_Service_YouTube($client);
        /*****CUSTOM CONFIG: YOUTUBE : END****/
        $errors = [];

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
            }
            elseif (isset($request->query['ak']) && isset($request->query['key']) && in_array($request->query['key'], AK)) {
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
                    'result' => $data,
                    'errors' => $errors
                ));
            }
            else {
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
                if (in_array($var, FILES)){
                    if (!isset($request->files[$var])) {
                        echo json_encode(array(
                            'status_code' => 0,
                            'error_code' => 'INVALID_REQUEST',
                            'error_desc' => "Required parameter/s missing!",
                            'error_msg' => "Required parameter/s missing! Required Parameters: " . (implode(", ", REQUIRED_TO_INSERT))
                        ));
                        exit();
                    }
                    continue;
                }

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

            /*****CUSTOM FUNCTION: YOUTUBE INSERT : START****/
            $props = create_events(
                $request->connection,
                $client,
                $request->body['title'],
                $request->body['description'],
                $request->body['scheduled_start_time'],
                $request->body['privacy_status'],
                $request->files['thumbnail']['tmp_name'],
                $request->body['stream']
            );
            $request->body['id'] = $props[0];
            $request->body['stream'] = $props[1];
            $request->body['etag'] = $props[2];
            $request->body['thumbnail'] = $props[3];
            /*****CUSTOM FUNCTION: YOUTUBE INSERT : END****/

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
            $update_details = false;


            foreach (UPDATABLE as $var) {

                if (isset($request->body[$var]) && $request->body[$var]) {

                    $update_keys[] = $var;

                    if ($var == 'stream'){
                        $bindResponse = bind_stream($request->connection, $youtube, $request->body['pk'], $request->body[$var]);
                    }else if ($var == 'created_by'){
                        if ($_SESSION['type']!='admin'){
                            echo json_encode(array('status_code'=>401, 'error_code'=>'UNAUTHORISED', 'error_desc'=>'Unauthorised access!', 'error_msg'=>'Oops, you seems less privileged!'));
                            exit();
                        }
//                    }else if ($var == 'thumbnail'){
//                        $thumbnailResponse = set_thumbnail($request->connection, $youtube, $request->body['pk'], $request->files['thumbnail']['tmp_name']);
//                        $request->body['thumbnail'] = $thumbnailResponse->items[0]->maxres->url;
//                        if (isset($request->files['thumbnail'])){
//                            echo $var;
//
//                        }
                    }elseif($var == 'charge' || $var == 'contribution' || $var=='thumbnail' || $var=='payment_status'){
                        if ($var == 'payment_status'){
                            if ($_SESSION['type']!='admin'){
                                echo json_encode(array('status_code'=>401, 'error_code'=>'UNAUTHORISED', 'error_desc'=>'Unauthorised access!', 'error_msg'=>'Oops, you seems less privileged!'));
                                exit();
                            }
                            if ($request->body['payment_status']=='success'){
                                if (!isset($request->body['method'])){
                                    echo json_encode(array(
                                        'status_code' => 0,
                                        'error_code' => 'INVALID_REQUEST',
                                        'error_desc' => "Required :method missing",
                                        'error_msg' => 'Oops, payment method required!'
                                    ));
                                    exit();
                                }
                                else{
                                    try {
                                        $channel_id = $_SESSION['channel_id'];
                                        $id = uniqid();
                                        $description = 'Contribution of Event :::' . $request->body['pk'];
                                        $created_by = $_SESSION['id'];
                                        $counterpart = $request->connection->query("SELECT created_by FROM events WHERE id='" . addslashes($request->body['pk']). "'")->fetch_assoc()['created_by'];
                                        $amount = $request->connection->query("SELECT contribution FROM events WHERE id='" . addslashes($request->body['pk']). "'")->fetch_assoc()['contribution'];
                                        if (!$amount){
                                            echo json_encode(array(
                                                'status_code' => 0,
                                                'error_code' => 'INVALID_REQUEST',
                                                'error_desc' => "Contribution not added!",
                                                'error_msg' => 'Oops, Contribution not added!'
                                            ));
                                            exit();
                                        }
                                        $insert_payment = $request->connection->prepare('INSERT INTO finance (channel_id, id, description, amount, created_by, method, counterparty) VALUES (?, ?, ?, ?, ?, ?, ?)');
                                        $insert_payment->bind_param('sssdsss', $channel_id, $id, $description, $amount, $created_by, $request->body['method'], $counterpart);
                                        if (!$insert_payment->execute()){
                                            log_error($request->connection, 'DB_INSERT', new Exception($request->connection->error));
                                            echo json_encode(array(
                                                'status_code' => 0,
                                                'error_code' => 'DB_INSERT',
                                                'error_desc' => $request->connection->error,
                                                'error_msg' => 'Oops, something went wrong!'
                                            ));
                                            exit();
                                        }
                                    }
                                    catch (Exception $e){
                                        log_error($request->connection, 'DB_INSERT', $e);
                                        echo json_encode(array(
                                            'status_code' => 0,
                                            'error_code' => 'DB_INSERT',
                                            'error_desc' => (string)$e,
                                            'error_msg' => 'Oops, something went wrong!'
                                        ));
                                        exit();
                                    }
                                }
                            }
                            else{
                                $current_status = $request->connection->query("SELECT payment_status FROM events WHERE id='" . addslashes($request->body['pk']). "'")->fetch_assoc()['payment_status'];
                                if ($current_status=='success'){
                                    echo json_encode(array(
                                        'status_code' => 0,
                                        'error_code' => 'INVALID_REQUEST',
                                        'error_desc' => 'Once marked success cannot be reverted back!',
                                        'error_msg' => 'Oops, Once marked success cannot be reverted back!'
                                    ));
                                    exit();
                                }
                            }


                        }
                    }
                    else{
                        $update_details = true;
                    }


                    if ($update_details){
                        if (isset($request->body['enableMonitorStream']) && !empty($request->body['broadcastStreamDelayMs'])){
                            $request->body['enableMonitorStream'] = (bool)$request->body['enableMonitorStream'];
                            $request->body['broadcastStreamDelayMs'] = $request->body['broadcastStreamDelayMs'] ?? 0;
                        }

                        update_event(
                            $request->connection,
                            $youtube,
                            $request->body['pk'],
                            $request->body['title'],
                            $request->body['description'],
                            $request->body['scheduled_start_time'],
                            $request->body['privacy_status'],
                            $request->body['enableMonitorStream'],
                            $request->body['broadcastStreamDelayMs'],
                        );
                    }


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

                delete_event($request->connection, $client, $request->body['pk']);

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

function sync($request): void
{
    /*****CUSTOM CONFIG: YOUTUBE : START****/
    $client = session_check($request->connection);
    $service = new Google_Service_YouTube($client);
    /*****CUSTOM CONFIG: YOUTUBE : END****/

    $allowed_methods = [
        'GET', // index, show (if key is passed)
    ];

    $check = $request->verify_method($allowed_methods);

    if ($check['status_code']) {

        $admin_id = $request->connection->query("SELECT id FROM users WHERE type='admin'")->fetch_assoc()['id'];
        do {
            $pageToken = '';
            $queryParams = [
                'broadcastStatus' => 'all',
                'pageToken'=>$pageToken,
                'maxResults'=>50,
            ];
            $response = $service->liveBroadcasts->listLiveBroadcasts('id,snippet,status,contentDetails', $queryParams);

            foreach ($response['items'] as $item) {
                $kind = $item->kind;
                if ($kind!='youtube#liveBroadcast'){
                    continue;
                }

                $id = $item->id;
                $title = $item->snippet->title;
                $description = $item->snippet->description;
                $scheduledStartTime = $item->snippet->scheduledStartTime?trim(str_replace(array("T", "Z"), ' ', $item->snippet->scheduledStartTime)):null;
                $publishedAt = trim(str_replace(array("T", "Z"), ' ', $item->snippet->publishedAt));
                $privacyStatus = $item->status->privacyStatus;
                $thumbnail = $item->snippet->thumbnails->default->url;
                $etag = $item->etag;
                $stream = $item->contentDetails->boundStreamId;

                $upsert = $request->connection->prepare("INSERT INTO `events` 
        (id, created_by, stream, title, `description`, scheduled_start_time, privacy_status, thumbnail, etag, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?) 
        ON DUPLICATE KEY UPDATE 
                             stream = VALUES(stream),
                             title=VALUES(title),
                             `description` = VALUES(`description`),
                             scheduled_start_time = VALUES(scheduled_start_time),
                             privacy_status = VALUES(privacy_status),
                             thumbnail = VALUES(thumbnail),
                             etag = VALUES(etag),
                             created_at = VALUES(created_at)
                             ");

                $upsert->bind_param('ssssssssss', $id, $admin_id, $stream, $title, $description, $scheduledStartTime, $privacyStatus, $thumbnail, $etag, $publishedAt);
                if (!$upsert->execute()){
                    $errors[] = ['error_code'=>'DB_UPSERT', 'error_desc'=>$request->connection->error, 'error_msg'=>"Importing of video '".$title."' failed!"];
                }
            }
            $pageToken = $response['nextPageToken'];
        }
        while ($pageToken);

        if (empty($errors)) echo json_encode(array('status_code'=>1));
        else echo json_encode(array('status_code'=>0, 'error_code'=>'UNKNOWN','error_msg'=>'Oops, something went wrong!', 'error_desc'=>$errors));

    }else{
        echo json_encode($check);
        exit();
    }
}

function thumbnail($request): void{
    /*****CUSTOM CONFIG: YOUTUBE : START****/
    $client = session_check($request->connection);
    $youtube = new Google_Service_YouTube($client);
    /*****CUSTOM CONFIG: YOUTUBE : END****/

    $allowed_methods = [
        'POST', //
    ];

    $check = $request->verify_method($allowed_methods);

    if ($check['status_code']) {
        if (!isset($request->files['thumbnail']) || !isset($request->body['pk'])){
            echo json_encode(array(
                'status_code' => 0,
                'error_code' => 'INVALID_REQUEST',
                'error_desc' => "Required parameter/s missing!",
                'error_msg' => "Required parameter/s missing! Required Parameters: thumbnail"
            ));
            exit();
        }

        $thumbnailResponse = set_thumbnail($request->connection, $youtube, $request->body['pk'], $request->files['thumbnail']['tmp_name']);

        $update = $request->connection->prepare("UPDATE ". TABLE ." SET thumbnail=? WHERE ".PK."=?");
        $update->bind_param('ss', $thumbnailResponse->items[0]->default->url, $request->body['pk']);

        if ($update->execute()){
            echo json_encode(array('status_code'=>1, 'results'=>$thumbnailResponse->items[0]->default->url));
        }else{
            log_error($request->connection, 'DB_UPDATE', new Exception($request->connection->error));
            echo json_encode(array(
                'status_code' => 0,
                'error_code' => 'DB_UPDATE',
                'error_desc' => $request->connection->error,
                'error_msg' => 'Oops, something went wrong!'
            ));
            exit();
        }

    }
    else{
        echo json_encode($check);
        exit();
    }
}


/***
 * Non routing functions
 */
function create_events($connection, $client, $title, $description, $scheduledStartTime, $status, $thumbnail_tmp_file, $streamId){
    try{
        $youtube = new Google_Service_YouTube($client);

// Create a new LiveBroadcast resource
        $broadcast = new Google_Service_YouTube_LiveBroadcast();

// Set the broadcast title, description, scheduled start time, and privacy status
        $broadcastSnippet = new Google_Service_YouTube_LiveBroadcastSnippet();
        $broadcastSnippet->setTitle($title);
        $broadcastSnippet->setDescription($description);
        $broadcastSnippet->setScheduledStartTime($scheduledStartTime); // Replace with your scheduled start time
        $broadcastStatus = new Google_Service_YouTube_LiveBroadcastStatus();
        $broadcastStatus->setPrivacyStatus($status); // Adjust privacy status as needed (public, unlisted, or private)
        $broadcast->setSnippet($broadcastSnippet);
        $broadcast->setStatus($broadcastStatus);
        $broadcast->setKind('youtube#liveBroadcast');
        $broadcastResponse = $youtube->liveBroadcasts->insert('snippet,status', $broadcast);

        $thumbnailSetResponse = set_thumbnail($connection, $youtube, $broadcastResponse->getId(), $thumbnail_tmp_file);
        $bindResponse = bind_stream($connection, $youtube, $broadcastResponse->getId(), $streamId);

    }catch (Exception $e){
        log_error($connection, 'YOUTUBE_BROADCAST', $e);
        echo json_encode(array('status_code'=>0, 'error_code'=>'YOUTUBE_BROADCAST', 'error_desc'=>$e->getMessage(), 'error_msg'=>json_decode($e->getMessage(), true)['error']['message']));
        exit();
    }
    return [$broadcastResponse->getId(), $streamId, $broadcastResponse->getEtag(), $thumbnailSetResponse->items[0]->default->url];
}
function set_thumbnail($connection, $youtube, $broadcastId, $thumbnail_tmp_file)
{
    try{

        return $youtube->thumbnails->set(
            $broadcastId,
            array(
                'data' => file_get_contents($thumbnail_tmp_file),
                'mimeType' => 'application/octet-stream',
                'uploadType' => 'multipart'
            )
        );
    }catch (Exception $e){
        log_error($connection, 'YOUTUBE_THUMBNAIL', $e);
        echo json_encode(array('status_code'=>0, 'error_code'=>'YOUTUBE_THUMBNAIL', 'error_desc'=>$e->getMessage(), 'error_msg'=>json_decode($e->getMessage(), true)['error']['message']));
        exit();
    }

}
function bind_stream($connection, $youtube, $broadcastId, $streamId)
{
    try{
        // Insert the broadcast and bind it to a stream
        $queryParams = [
            'streamId' => $streamId
        ];

        return $youtube->liveBroadcasts->bind($broadcastId, 'contentDetails', $queryParams);
    }catch (Exception $e){
        log_error($connection, 'YOUTUBE_STREAM_BROADCAST_BIND', $e);
        echo json_encode(array('status_code'=>0, 'error_code'=>'YOUTUBE_STREAM_BROADCAST_BIND', 'error_desc'=>$e->getMessage(), 'error_msg'=>json_decode($e->getMessage(), true)['error']['message']));
        exit();
    }

}
function update_event($connection, $youtube, $broadcastId, $title, $description, $scheduled_start_time, $privacy_status, $enable_monitor_stream=false, $broadcastStreamDelayMs=0){
    try{

// Define the $liveBroadcast object, which will be uploaded as the request body.
    $liveBroadcast = new Google_Service_YouTube_LiveBroadcast();

// Add 'contentDetails' object to the $liveBroadcast object.
    $liveBroadcastContentDetails = new Google_Service_YouTube_LiveBroadcastContentDetails();
    $monitorStreamInfo = new Google_Service_YouTube_MonitorStreamInfo();
    $monitorStreamInfo->setBroadcastStreamDelayMs($broadcastStreamDelayMs);
    $monitorStreamInfo->setEnableMonitorStream($enable_monitor_stream);
    $liveBroadcastContentDetails->setMonitorStream($monitorStreamInfo);
    $liveBroadcast->setContentDetails($liveBroadcastContentDetails);

// Add 'id' string to the $liveBroadcast object.
    $liveBroadcast->setId($broadcastId);

// Add 'snippet' object to the $liveBroadcast object.
    $liveBroadcastSnippet = new Google_Service_YouTube_LiveBroadcastSnippet();
    $liveBroadcastSnippet->setDescription($description);
    $liveBroadcastSnippet->setScheduledStartTime($scheduled_start_time);
    $liveBroadcastSnippet->setTitle($title);
    $liveBroadcast->setSnippet($liveBroadcastSnippet);

// Add 'status' object to the $liveBroadcast object.
    $liveBroadcastStatus = new Google_Service_YouTube_LiveBroadcastStatus();
    $liveBroadcastStatus->setPrivacyStatus($privacy_status);
    $liveBroadcast->setStatus($liveBroadcastStatus);

    return $youtube->liveBroadcasts->update('id,snippet,contentDetails,status', $liveBroadcast);
}
    catch (Exception $e){
        log_error($connection, 'YOUTUBE_UPDATE', $e);
        echo json_encode(array('status_code'=>0, 'error_code'=>'YOUTUBE_UPDATE', 'error_desc'=>$e->getMessage(), 'error_msg'=>json_decode($e->getMessage(), true)['error']['message']));
        exit();
    }
}
function delete_event($connection, $client, $event_id): void
{
    try{
        $service = new Google_Service_YouTube($client);
        $service->liveBroadcasts->delete($event_id);
    }catch (Exception $e){
        log_error($connection, 'YOUTUBE_BROADCAST_DELETE', $e);
        echo json_encode(array('status_code'=>0, 'error_code'=>'YOUTUBE_BROADCAST_DELETE', 'error_desc'=>$e->getMessage(), 'error_msg'=>json_decode($e->getMessage(), true)['error']['message']));
        exit();
    }
}