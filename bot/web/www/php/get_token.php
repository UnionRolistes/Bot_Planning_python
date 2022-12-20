<?php
session_start();
require('config.php');
require('curl_utils.php');

if (!isset($_GET['code']))
    die("Perdu ? Vous n'êtes pas censé vous retrouver coincé ici. :( <small><br>(ou alors, les serveurs discord sont down)</small>");

$params = array(
    "grant_type" => "authorization_code",
    "client_id" => CLIENT_ID,
    "client_secret" => CLIENT_SECRET,
    "redirect_uri" => URL_SITE . REDIRECT_URI,
    "code" => $_GET['code']
);

$ch = curl_init("https://discord.com/api/oauth2/token");
if (
    !curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4) or
    !curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE) or
    !curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params))
) {
    print("Échec de l'obtention du token oauth2. Contactez un administrateur si le problème perdure.");
    error_log("OAUTH2_ERROR| Échec de l'obtention du token.");
    die();
}
$response = json_decode(curl_exec($ch));

assert_curl_success($ch);
$token = $response->access_token;

$_SESSION['access_token'] = $token;
header('Location: /php/get_user_information.php');
?>