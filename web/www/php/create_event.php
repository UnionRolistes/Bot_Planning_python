<?php
// UR_Bot © 2020 by "Association Union des Rôlistes & co" is licensed under Attribution-NonCommercial-ShareAlike 4.0 International (CC BY-NC-SA)
// To view a copy of this license, visit http://creativecommons.org/licenses/by-nc-sa/4.0/
// Ask a derogation at Contact.unionrolistes@gmail.com

// Remplace l'ancien CGI Python (cgi/create_post.py) — celui-ci n'était déjà
// plus qu'un relais vers planning-api (POST /events), pas de raison de
// garder un interpréteur CGI pour ça. planning-api se charge de l'envoi de
// l'annonce sur Discord (via le webhook enregistré par $jdr) et de la
// persistance dans events.xml.

require_once __DIR__ . "/settings.php";

function jdr_system_value() {
    $system = $_POST['jdr_system'] ?? '';
    return $system !== '' ? $system : ($_POST['jdr_system_other'] ?? '');
}

function redirect_and_exit($status) {
    global $siteURL;
    header("Location: {$siteURL}?error={$status}", true, 303);
    exit;
}

$payload = [
    'user_id' => intval($_POST['user_id'] ?? ''),
    'pseudo' => $_POST['pseudo'] ?? '',
    'jdr_type' => $_POST['jdr_type'] ?? '',
    'jdr_title' => $_POST['jdr_title'] ?? '',
    'jdr_date' => $_POST['jdr_date'] ?? '',
    'jdr_horaire' => $_POST['jdr_horaire'] ?? '',
    'jdr_length' => $_POST['jdr_length'] ?? '',
    'jdr_system' => jdr_system_value(),
    'jdr_pj' => intval($_POST['jdr_pj'] ?? ''),
    'platform' => $_POST['platform'] ?? [],
    'jdr_details' => $_POST['jdr_details'] ?? '',
    'min_joueurs' => intval($_POST['minJoueurs'] ?? ''),
    'max_joueurs' => intval($_POST['maxJoueurs'] ?? ''),
];

$ch = curl_init("{$planningApiBaseUrl}/events");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_TIMEOUT => 10,
]);
$response = curl_exec($ch);
$curlErrored = curl_errno($ch) !== 0;
$statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($curlErrored || $statusCode >= 400) {
    error_log("Échec de la création de l'événement : status={$statusCode} response={$response}");
    redirect_and_exit('envoi');
}

redirect_and_exit('isPosted');
