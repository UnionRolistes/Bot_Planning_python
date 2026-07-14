<?php
session_start();
require('curl_utils.php');

if (!isset($_SESSION['access_token']))
    die("Access_token not available.");

$header[] = 'Authorization: Bearer ' . $_SESSION['access_token'];
$ch = curl_init("https://discord.com/api/users/@me");

if (
    !curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4) or
    !curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE) or
    !curl_setopt($ch, CURLOPT_HTTPHEADER, $header)
)
    die("Set curl opt failure");

$response = json_decode(curl_exec($ch));
assert_curl_success($ch);
$_SESSION['user_id'] = $response->id;
$_SESSION['pseudo'] = $response->username . '#' . $response->discriminator;
$_SESSION['username'] = $response->username;
$_SESSION['avatar_url'] = 'https://cdn.discordapp.com/avatars/' . $response->id . '/' . $response->avatar . '.png' . '?size=32';
header('Location: /');
?>
