<?php
require 'imports.php';
session_check($db_connection);
require 'head.php';
require 'navigation.php';
$is_admin = false;

if ($_SESSION['type']=='admin'){
    $is_admin = true;
    $events = $db_connection->query("SELECT u.id as user_id, e.id as id, e.title, s.streamKey, s.title as streamName, u.name, e.scheduled_start_time, e.privacy_status, e.payment_status, e.thumbnail, e.description, e.charge, e.contribution FROM events e LEFT JOIN users u on u.id=e.created_by LEFT JOIN streams s on s.id = e.stream")->fetch_all(MYSQLI_ASSOC);
}else{
    $events = $db_connection->query("SELECT e.id as id, e.title as title, s.streamKey, s.title as streamName, e.scheduled_start_time, e.privacy_status, e.payment_status, e.thumbnail, e.description, e.charge, e.contribution FROM events e LEFT JOIN streams s on s.id = e.stream WHERE e.created_by='".$_SESSION['id']."'")->fetch_all(MYSQLI_ASSOC);
}
?>

    <!-- Begin Page Content -->
    <div class="container-fluid">

        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h6 mb-0 text-gray-800">EVENTS</h1>
            <?=$is_admin?'<a href="#" class="d-none d-sm-inline-block btn btn-sm btn-'.THEME.' shadow-sm" onclick="sync_events()"><i
                    class="fa fa-sync fa-sm text-white-50"></i> Sync Events</a>':''?>
            <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-<?=THEME?> shadow-sm" onclick="triggerEventForm(this, 'add', '', '<?=$_SESSION['id']?>')"><i
                    class="fa fa-stream fa-sm text-white-50"></i> Create an event</a>
        </div>

        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTable" class="table table-hover">
                        <thead>
                        <tr>
                            <th>#</th>
                            <?=$is_admin?'<th>User</th>':''?>
                            <th>Thumbnail</th>
                            <th>Title</th>
                            <th>Start Time</th>
                            <th>Privacy Status</th>
                            <th>Stream</th>
                            <th>Charged</th>
                            <th>Contribution</th>
                            <th>Payment Status</th>
                            <th>Operation</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $index=1;
                        foreach ($events as $event){
                            if ($event['payment_status']=='pending'){
                                echo '<tr class="text-warning" id="'.$event['id'].'-row">';
                            }elseif ($event['payment_status']=='dispute'){
                                echo '<tr class="text-danger" id="'.$event['id'].'-row">';
                            }else{
                                echo '<tr id="'.$event['id'].'-row">';
                            }
                            echo    '<td id="'.$event['id'].'-index">'.$index.'</td>';
                            if ($is_admin){
                                echo '<td id="'.$event['id'].'-name">'.$event['name'].'</td>';
                            }

                            echo            '<td id="'.$event['id'].'-thumbnail"><img src="'.$event['thumbnail'].'" alt="" style="max-width: 70px">';
                            echo            '<td id="'.$event['id'].'-title">'.$event['title'].'</td>
                                            <td id="'.$event['id'].'-scheduled_start_time">'.$event['scheduled_start_time'].'</td>
                                            <td id="'.$event['id'].'-privacy_status">'.$event['privacy_status'].'</td>
                                            <td id="'.$event['id'].'-stream">'.$event['streamName'].' - '.$event['streamKey'].'</td>
                                            <td id="'.$event['id'].'-charge">'.$event['charge'].'</td>
                                            <td id="'.$event['id'].'-contribution">'.$event['contribution'].'</td>
                                            <td id="'.$event['id'].'-payment_status">'.$event['payment_status'].'</td>
                                            <td id="'.$event['id'].'-operation">
                                                <i class="fa fa-edit" title="Edit Event"  onclick="triggerEventForm(this, \'edit\', \''. $event['id'] .'\', \'\', \'\', \'' . addslashes($event['title']) . '\', \'' . addslashes($event['description']) . '\', \'' . addslashes($event['scheduled_start_time']) . '\', \'' . addslashes($event['privacy_status']) . '\')"></i>';
                            echo                '<i class="fa fa-credit-card" title="Make payment"  onclick="triggerEventForm(this, \'payment\', \''. $event['id'] .'\')"></i>
                                                <i class="fa fa-link" title="Bind Stream"  onclick="triggerEventForm(this, \'bind\', \''. $event['id'] .'\', \''.$_SESSION['id'].'\')"></i>
                                                <i class="fa fa-trash" title="Delete Stream"  onclick="triggerEventForm(this, \'delete\', \''. $event['id'] .'\')"></i>';
                            echo '<i class="fa fa-share" title="Get Link"  onclick="navigator.clipboard.writeText(\'https://youtube.com/live/'.$event['id'].'?feature=share\')"></i>';

                            echo $is_admin?'<br><i class="fa fa-user-edit" title="Change User"  onclick="triggerEventForm(this, \'user\', \''. $event['id'] .'\', \'\', \''. $_SESSION['channel_id'] .'\')"></i>&nbsp;':'';
                            echo $is_admin&&$event['payment_status']!='success'?'<i class="fa fa-check-square" title="Approve Payment"  onclick="triggerEventForm(this, \'pay\', \''. $event['id'] .'\')"></i>':'';

                            echo '

                                            </td>
                               </tr>';
                            $index++;
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- /.container-fluid -->


    <div class="modal fade" id="eventModal" tabindex="-1" role="dialog" aria-labelledby="eventModalLabel"
         aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="eventModalLabel">Add/Edit Event</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="api.php/events" id="event-form" method="post" enctype="multipart/form-data">
                    </form>
                </div>
            </div>
        </div>
    </div>



    </div>
<?php require_once 'foot.php'; ?>
</body>

</html>