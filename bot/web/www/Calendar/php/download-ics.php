<?php //Exporte une partie unique
if (!isset($_POST['summary']) || !isset($_POST['location']) || !isset($_POST['description']) || !isset($_POST['date_start']) || !isset($_POST['date_end']) || !isset($_POST['lien'])){
  header('Location:../index.php');
}

$summary = explode(",", $_POST['summary']);//Enleve les , du nom du fichier, car elles ne sont pas acceptées (fait planter le script)
$summary=implode(" ",$summary);
$description=$_POST['lien']."\\n".$_POST['description']; //On rajoute le lien du message discord à la description

include 'ICS.php';

header('Content-Type: text/calendar; charset=utf-8');
header('Content-Disposition: attachment; filename='.$summary.'.ics');

$ics = new ICS(array(
  'location' => $_POST['location'],
  'description' => $description,
  'dtstart' => $_POST['date_start'],
  'dtend' => $_POST['date_end'],
  'summary' => $_POST['summary']
));

echo $ics->to_string(); ?>