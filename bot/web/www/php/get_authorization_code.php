<?php
require('config.php');
$params = array(
    'response_type' => 'code',
    'client_id' => CLIENT_ID,
    #str of the redirect uri
    'redirect_uri' => URL_SITE . REDIRECT_URI,
    'scope' => 'identify'
);
header('Location: https://discordapp.com/api/oauth2/authorize?' . http_build_query($params));
die();
?>