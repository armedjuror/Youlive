<?php
//function getGoogleClient() {
//    $client = getOauth2Client();
//
//    // Refresh the token if it's expired.
//    if ($client->isAccessTokenExpired()) {
//        $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
//        file_put_contents(SETUP_DIR, json_encode($client->getAccessToken()));
//    }
//    return $client;
//}

function buildClient(){
    $client = new Google_Client();
    $client->setClientId($_ENV['GOOGLE_CLIENT_ID']);
    $client->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET']);
    $redirectUri = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . join('/', array_slice(explode('/', $_SERVER['PHP_SELF']),0 , count(explode('/', $_SERVER['PHP_SELF']))-1)) . '/initial_setup.php';
    $client->setRedirectUri($redirectUri);
    $client->setAccessType("offline");
    $client->setIncludeGrantedScopes(true);
    $client->addScope(['profile', 'email', Google_Service_YouTube::YOUTUBE_FORCE_SSL]);
	return $client;
}

function getRedirectUri(): string
{
    return (isset($_SERVER['HTTPS']) ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . join('/', array_slice(explode('/', $_SERVER['REQUEST_URI']),0 , count(explode('/', $_SERVER['REQUEST_URI']))-1)) . '/initial_setup.php';
}

function getOauth2Client() {
    try {

        $client = buildClient();

        // Set the refresh token on the client.
        if (isset($_SESSION['refresh_token']) && $_SESSION['refresh_token']) {
            $client->refreshToken($_SESSION['refresh_token']);
        }

        // If the user has already authorized this app then get an access token
        // else redirect to ask the user to authorize access to Google Analytics.
        if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {

            // Set the access token on the client.
            $client->setAccessToken($_SESSION['access_token']);

            // Refresh the access token if it's expired.
            if ($client->isAccessTokenExpired()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                $client->setAccessToken($client->getAccessToken());
                $_SESSION['access_token'] = $client->getAccessToken();
            }
            return $client;
        } else {
            // We do not have access request access.
            header('Location: ' . filter_var( $client->getRedirectUri(), FILTER_SANITIZE_URL));
        }
    } catch (Exception $e) {
        log_error($db_connection, 'TOKEN_REFRESH', $e);
        print_error($e, 'Oops, something went wrong!', 'Try reloading and if error persist please contact server admin. Error code: TOKEN_REFRESH');
    }
}