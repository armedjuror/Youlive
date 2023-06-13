<?php
require 'imports.php';
session_check($db_connection);
require 'head.php';
require 'navigation.php';
if ($_SESSION['type']!='admin'){
    print_error(new Exception("Unauthorised Access"), 'Oops, you seems less privileged!', 'This page is only meant for admin users');
    echo '</div>';
    include 'foot.php';
    exit();
}

$users = $db_connection->query("SELECT * FROM users WHERE channel_id='".$_SESSION['channel_id']."'")->fetch_all(MYSQLI_ASSOC);

?>

            <!-- Begin Page Content -->
            <div class="container-fluid">

                <!-- Page Heading -->
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h6 mb-0 text-gray-800">USERS</h1>
                    <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-<?=THEME?> shadow-sm" onclick="triggerUserForm(this, 'add', '', '', '', '<?=$_SESSION['channel_id']?>')"><i
                                class="fas fa-user fa-sm text-white-50"></i> Add User</a>
                    <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-<?=THEME?> shadow-sm"  data-toggle="modal" data-target="#importModal"><i
                                class="fas fa-download fa-sm text-white-50"></i> Import Users</a>
                </div>

                <div class="card shadow mb-4">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="dataTable" class="table table-hover">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Id</th>
                                    <th>Type</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Operation</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                $index=1;
                                foreach ($users as $user){
                                    echo '<tr id="'.$user['id'].'-row">
                                            <td id="'.$user['id'].'-index">'.$index.'</td>
                                            <td id="'.$user['id'].'-id">'.$user['id'].'</td>
                                            <td id="'.$user['id'].'-type">'.$user['type'].'</td>
                                            <td id="'.$user['id'].'-name">'.$user['name'].'</td>
                                            <td id="'.$user['id'].'-email">'.$user['email'].'</td>
                                            <td id="'.$user['id'].'-operation">
                                                <i class="fa fa-edit" title="Modify User"  onclick="triggerUserForm(this, \'modify\', \''. $user['id'] .'\', \'' . $user['name'] . '\', \'' . $user['email'] . '\')"></i> 
                                                <i class="fa fa-key" title="Change Password" onclick="triggerUserForm(this, \'password\', \''. $user['id'] .'\', \'' . $user['name'] . '\', \'' . $user['email'] . '\')"></i> 
                                                <i class="fa fa-trash" title="Delete User"  onclick="triggerUserForm(this, \'delete\', \''. $user['id'] .'\')"></i></td>
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

    <div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-labelledby="importModalLabel"
         aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalLabel">Import Users</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-<?=THEME?>">
                        <h5>Please take note of the following:</h5>
                        <ol>
                            <li>The file importing should be a csv file ie, comma-seperated values</li>
                            <li>This CSV should contain 2 columns viz, Email and Name in the same order</li>
                            <li>First row of the CSV should be the header and won't be imported</li>
                            <li>All users imported will be assigned with a common password, which they can change.</li>
                        </ol>
                    </div>
                    <form action="api.php/users/import" id="user-import-form" method="post">
                        <div class="form-group">
                            <input type="file" name="file" class="form-control form-control-file" id="file" accept="text/csv">
                        </div>
                        <div class="form-group text-right">
                            <input type="submit" name="import" class="btn btn-<?=THEME?>">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="userModal" tabindex="-1" role="dialog" aria-labelledby="userModalLabel"
         aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="userModalLabel">Add/Edit User</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="api.php/users" id="user-form" method="post">
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

