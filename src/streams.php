<?php
require 'imports.php';
session_check($db_connection);
require 'head.php';
require 'navigation.php';
$is_admin = false;

if ($_SESSION['type']=='admin'){
    $is_admin = true;
    $streams = $db_connection->query("SELECT u.id as user_id, s.id as id, s.title, s.streamKey, u.name, s.ingestionType, s.frameRate, s.resolution  FROM streams s LEFT JOIN users u on u.id=s.user_id")->fetch_all(MYSQLI_ASSOC);
}else{
    $streams = $db_connection->query("SELECT * FROM streams WHERE user_id='".$_SESSION['id']."'")->fetch_all(MYSQLI_ASSOC);
}
?>

    <!-- Begin Page Content -->
    <div class="container-fluid">

        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h6 mb-0 text-gray-800">STREAMS</h1>
            <?=$is_admin?'<a href="#" class="d-none d-sm-inline-block btn btn-sm btn-'.THEME.' shadow-sm" onclick="sync_streams()"><i
                    class="fa fa-sync fa-sm text-white-50"></i> Sync Streams</a>':''?>
            <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-<?=THEME?> shadow-sm" onclick="triggerStreamForm(this, 'add', '', '<?=$_SESSION['id']?>')"><i
                    class="fa fa-stream fa-sm text-white-50"></i> Create a stream</a>
        </div>

        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTable" class="table table-hover">
                        <thead>
                        <tr>
                            <th>#</th>
                            <?=$is_admin?'<th>User</th>':''?>
                            <th>Title</th>
                            <th>Key</th>
                            <th>Ingestion Type</th>
                            <th>Frame Rate</th>
                            <th>Resolution</th>
                            <th>Operation</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $index=1;
                        foreach ($streams as $stream){
                            echo '<tr id="' . $stream['id'] . '-row">
                                            <td>'.$index.'</td>';

                            if ($is_admin){
                                echo '<td>'.$stream['name'].'</td>';
                            }

                            echo            '<td>'.$stream['title'].'</td>
                                            <td>'.$stream['streamKey'].'</td>
                                            <td>'.$stream['ingestionType'].'</td>
                                            <td>'.$stream['frameRate'].'</td>
                                            <td>'.$stream['resolution'].'</td>
                                            <td>
                                                <i class="fa fa-trash" title="Delete Stream"  onclick="triggerStreamForm(this, \'delete\', \''. $stream['id'] .'\')"></i>
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


    <div class="modal fade" id="streamModal" tabindex="-1" role="dialog" aria-labelledby="streamModalLabel"
         aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="streamModalLabel">Add/Edit Stream</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="api.php/streams" id="stream-form" method="post">
                    </form>
                </div>
            </div>
        </div>
    </div>



    </div>
    <!-- End of Main Content -->
<?php require_once 'foot.php'; ?>
</body>

</html>