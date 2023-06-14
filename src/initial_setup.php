<?php
require('session.php');
require_once('requirements.php');
require_once(SETUP_FILE);
require 'oauth-helper.php';

$client = buildClient();
$channel_added = false;

if (isset($_GET['code'])){
    try{
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
        $client->setAccessToken($token);
        $_SESSION['access_token'] = $token['access_token'];
        $_SESSION['refresh_token'] = $token['refresh_token'];
        $auth = new Google_Service_Oauth2($client);
        $user = $auth->userinfo->get();
        $channel_id = uniqid();
        $_SESSION['channel_id'] = $channel_id;
        $add_channel = $db_connection->prepare("
            INSERT IGNORE INTO channels (id, `name`, picture, access_token, refresh_token) VALUE (
                ?, ?, ?, ?, ?
            )
        ");
        $add_channel->bind_param('sssss', $channel_id, $user->name, $user->picture, $_SESSION['access_token'], $_SESSION['refresh_token']);
        if ($add_channel->execute()){
            $channel_added = true;
            $_SESSION['email'] = $user->email;
            $_SESSION['name'] = $user->name;
        }else{
            log_error($db_connection, 'DB_INSERT', $db_connection->error);
            print_error($db_connection->error, 'Oops, something went wrong!', 'This can be a runtime error. Try restarting the Auth. If the error persist, contact server admin. (Error Code: DB_INSERT)');
            exit();
        }
    }catch (Exception $e){
        log_error($db_connection, 'Google_OAuth2', $e);
        print_error($e, 'Authentication Failed!', 'This can be a runtime error. Try restarting the Auth. If the error persist, contact server admin.');
        exit();
    }
}
else{
    if (isset($_POST['addChannel'])) {
        $auth_url = $client->createAuthUrl();
        header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
    }else if(isset($_POST['addAdmin'])){
        $admin_id = uniqid();
        $_SESSION['type'] = 'admin';
        $_SESSION['user_id'] = $admin_id;
        $email = $_POST['admin_email'];
        $name = $_POST['admin_name'];
        $password = $_POST['password'];
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $add_admin = $db_connection->prepare('
            INSERT IGNORE INTO users (channel_id, id, type, email, `name`, `password`) VALUE (
                ?, ?, ?, ?, ?, ?
            )
        ');
        $type = 'admin';
        $add_admin->bind_param('ssssss', $_POST['channel_id'], $admin_id,  $type, $email, $name, $password_hash);
        if ($add_admin->execute()){
            header('Location: dashboard.php');
        }else{
            log_error($db_connection, 'DB_INSERT', $db_connection->error);
            print_error($db_connection->error, 'Oops, something went wrong!', 'This can be a runtime error. Try restarting the Auth. If the error persist, contact server admin. (Error Code: DB_INSERT)');
            exit();
        }
    }
}

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?=$channel_added?"Please add your admin user":"Welcome to ".APP_NAME?></title>
    <link href="ui/extras/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
            href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
            rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="ui/css/sb-admin-2.min.css" rel="stylesheet">
</head>
<body class="bg-gradient-<?=THEME?>">

<div class="container">

    <div class="row justify-content-center">

        <div class="col-xl-5 col-lg-5 col-md-5">

            <div class="card o-hidden border-0 shadow-lg" style="margin-top: 200px;">
                <div class="card-body p-0">
                    <!-- Nested Row within Card Body -->
                    <div class="row">
<!--                        <div class="col-lg-6 d-none d-lg-block bg-login-image"></div>-->
                        <div class="col-lg-12">
                            <div class="p-5">
                                <div class="text-center">
                                    <h1 class="h4 text-gray-900 mb-4"><?=$channel_added?"Add Admin User":"Welcome to ".APP_NAME?></h1>
                                </div>
                                <?php
                                if ($channel_added){
                                    ?>
                                    <form method="post" id="addAdminForm" action="initial_setup.php" onsubmit="return validate()" class="user">
                                        <input type="hidden" id="channel_id" name="channel_id" value="<?=$_SESSION['channel_id']?>" readonly required/>

                                        <div class="form-group">
                                            <input type="email" class="form-control"
                                                   id="admin_email" name="admin_email"
                                                   value="<?=$_SESSION['email']?>" readonly required>
                                        </div>

                                        <div class="form-group">
                                            <input type="text" id="admin_name" name="admin_name" class="form-control" value="<?=$_SESSION['name']?>" placeholder="Name" required/>
                                        </div>

                                        <div class="form-group">
                                            <input type="password" id="password" name="password" class="form-control" placeholder="Password" required/>
                                        </div>

                                        <div class="form-group">
                                            <input type="password" id="confirm_password" class="form-control" placeholder="Confirm Password" required/>
                                        </div>

                                        <div class="form-outline mb-4" id="errorDiv" style="display: none"></div>

                                        <button type="submit" name="addAdmin" class="btn btn-<?=THEME?> btn-block mb-4">Create Admin User</button>
                                    </form>
                                    <?php
                                }
                                else{
                                    ?>
                                    <form method="post" action="" class="user">
                                        <button type="submit" name="addChannel" class="btn btn-google btn-user btn-block"><i class="fab fa-youtube fa-fw"></i> Connect Youtube Channel</button>
                                    </form>
                                    <?php
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>

<script src="ui/extras/jquery/jquery.min.js"></script>
<script src="ui/extras/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="ui/extras/jquery-easing/jquery.easing.min.js"></script>
<script src="ui/js/sb-admin-2.min.js"></script>
<?php
if ($channel_added){
    ?>
    <script>
        function validate(){
            let password = document.getElementById('password').value
            let confirm_password = document.getElementById('confirm_password').value
            if (password!==confirm_password){
                document.getElementById('errorDiv').innerHTML = '<p style="color:red">Password is not matching</p>'
                document.getElementById('errorDiv').style.display = 'inline'
                return false
            }else{
                document.getElementById('errorDiv').style.display = 'none'
                return true
            }
        }

        document.getElementById('password').addEventListener('keyup', ()=>{
            validate()
        })
        document.getElementById('confirm_password').addEventListener('keyup', ()=>{
            validate()
        })
    </script>
    <?php
}
?>
</body>
</html>