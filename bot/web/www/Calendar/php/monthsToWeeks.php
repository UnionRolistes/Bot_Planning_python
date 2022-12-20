<?php
//Sert à passer des mois à la semaine d'une partie cliquée. J'ai fait un fichier externe pour ne pas avoir la date dans l'Url de index.php
//Optimisable plus tard si on veut tout passer par l'Ajax (mais risque d'etre complexe)

session_start();

if(!isset($_GET['date'])){header('Location:../index.php?view=months');}

$date=explode("-", $_GET['date']);

if (checkdate($date[1], $date[2], $date[0])){
    $_SESSION['monday']=new DateTimeImmutable(date("Y-m-d", strtotime('monday this week', strtotime($_GET['date'])))); //Sort le lundi de la semaine correspondant au string donné
    header('Location:../index.php');
}
else{
    header('Location:../index.php?view=months');
}

//checkdate($month, $day, $year) verfie si la date est valide, cad si année à 4 chiffres, mois entre 1 et 12, et jour entre 1 et 31 en fonction des mois (le detecte automatiquement)
?>