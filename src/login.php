<?php
require('session.php');
require_once('requirements.php');
require_once(SETUP_FILE);

$have_error = false;
$error = "";

if (!is_logged_out()){
    header('Location: dashboard.php');
}

if (isset($_POST['login'])){
    $email = htmlspecialchars(addslashes(trim($_POST['email'])));
    $password = trim($_POST['password']);
    $fetch_user = $db_connection->prepare("SELECT * FROM users WHERE email=?");
    $fetch_user->bind_param('s', $email);
    if ($fetch_user->execute()){
        $fetched = $fetch_user->get_result();
        if ($fetched->num_rows==1){
            $user = $fetched->fetch_assoc();
            if (password_verify($password, $user['password'])){
                $_SESSION['type'] = $user['type'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['id'] = $user['id'];
                if ($user['type']!='youlive_admin'){
                    $_SESSION['channel_id'] = $user['channel_id'];
                    $channel_id = $user['channel_id'];
                    $channel = $db_connection->query("SELECT * FROM channels WHERE id='$channel_id'")->fetch_assoc();
                    $_SESSION['channel_name'] = $channel['name'];
                    $_SESSION['refresh_token'] = $channel['refresh_token'];
                    $_SESSION['access_token'] = $channel['access_token'];
                    $_SESSION['picture'] = $channel['picture'];
                    $_SESSION['max_users'] = $channel['max_users'];
                }
                $login_now = $db_connection->prepare("UPDATE users SET last_login_at=NOW() WHERE id=?");
                $login_now->bind_param('s', $user['id']);
                $login_now->execute();
                header('Location: dashboard.php');
            }else{
                $have_error = true;
                $error = "Oops, credentials don't match!";
            }
        }else{
            $have_error = true;
            $error = "Oops, user not found!";
        }
    }
    else{
        log_error($db_connection, 'DB_SELECT', $db_connection->error);
        print_error($db_connection->error, 'Oops, something went wrong!', 'This can be a runtime error. If the error persist, contact server admin. (Error Code: DB_SELECT)');
        exit();
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
    <title>Welcome back</title>
    <link href="ui/extras/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
            href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
            rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="ui/css/sb-admin-2.min.css" rel="stylesheet">
</head>
<body class="bg-gradient-<?=THEME?>">

<div class="container">

    <!-- Outer Row -->
    <div class="row justify-content-center">

        <div class="col-xl-5 col-lg-5 col-md-5">

            <div class="card o-hidden border-0 shadow-lg" style="margin-top: 200px;">
                <div class="card-body p-0">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="p-5">
                                <div class="text-center">
                                    <h1 class="h4 text-gray-900 mb-4">Welcome Back!</h1>
                                </div>
                                <form method="post" id="addAdminForm" action="login.php" class="user">
                                    <div class="form-group p-5 text-center" id="errorDiv" style="display: <?=$have_error?'inline':'none'?>">
                                        <?=$error?>
                                    </div>
                                    <div class="form-group mt-3">
                                        <input type="email" id="email" name="email" class="form-control" placeholder="Email" required/>
                                    </div>
                                    <div class="form-group">
                                        <input type="password" class="form-control"
                                               id="password" name="password"  placeholder="Password">
                                    </div>
                                    <button type="submit" name="login" class="btn btn-<?=THEME?> btn-block">Login</button>
                                </form>
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
</body>
</html>