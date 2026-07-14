
<?php
require('config.php');
require('settings.php'); //Pour le lien du site
$params = array(
    'response_type' => 'code',
    'client_id' => CLIENT_ID,
    'redirect_uri' => $siteURL.'/php/get_token.php',
    'scope' => 'identify'
);
header('Location: https://discordapp.com/api/oauth2/authorize?' . http_build_query($params));
die();	
?>
