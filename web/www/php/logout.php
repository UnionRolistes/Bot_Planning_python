<?php
session_start();
//session_destroy(); //Ferme la session, detruit les vars associées --> Detruit aussi le Webhook ! 
unset($_SESSION['user_id']);
unset($_SESSION['pseudo']);
unset($_SESSION['username']);
unset($_SESSION['avatar_url']);
header('Location:../index.php');
?>