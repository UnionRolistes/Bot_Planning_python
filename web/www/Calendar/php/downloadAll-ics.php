<?php //Exporte toutes les parties valides présentes dans le xml
if (!isset($_POST['downloadAll-ics'])){ 
  header('Location:../index.php');
}
include 'ICS.php';


//On ouvre le Xml :        
if (!file_exists("../data/events.xml")) {
    exit('Echec lors de la récupération des parties');
}
$xml = simplexml_load_file('../data/events.xml');


$trouve=false;
$i=1;
foreach ($xml->partie as $partie) {

    try{
        //On récupère les infos
        $titre=$partie->titre;
        $dateDebut=new DateTime($partie->date); //On choisit DateTime face à DateTimeImmutable pour un ajout des heures plus simples
        $heure=$partie->heure;
        $duree=$partie->duree;
        $type=$partie->type;
        $MJ=$partie->mjName;
        $systeme=$partie->systeme;
        $plateformes=$partie->plateformes; 
        $details=$partie->details;
        $lienWeb=$partie->lien;
        

        //Puis on en crée un événement
        $titre = explode(",", $titre);//Enleve les , du nom du fichier, car elles ne sont pas acceptées (fait planter le script)
        $titre=implode(" ",$titre);
        $description=$lienWeb."\\n".$details; //On rajoute le lien du message discord à la description


        $heure=explode("h", "$partie->heure");
        if($heure[1]==""){
            $heure[1]=0;
        }
        $duree=explode("h", "$partie->duree");
        if($duree[1]==""){
            $duree[1]=0;
        }
        $dateDebut->setTime($heure[0], $heure[1]);
            
            
        $dateFin=new DateTime($partie->date);
        $dateFin->setTime($heure[0], $heure[1]);
        $dateFin=$dateFin->add(date_interval_create_from_date_string('+'.$duree[0].' hours '.$duree[1].' minutes'));

        

        //GMT -2 pour la France (L'heure est interprétée en GMT 0 par tous les logiciels que j'ai testé). Autres GMT à gérer
        $dateDebut->add(date_interval_create_from_date_string('-2 hours'));
        $dateFin->add(date_interval_create_from_date_string('-2 hours'));

            
        ${'ics'.$i} = new ICS(array(
          'location' => "http://unionrolistes.fr/",
          'description' => $details,
          'dtstart' => $dateDebut->format("Y-m-d H:i"),
          'dtend' => $dateFin->format("Y-m-d H:i"),
          'summary' => $titre
        ));

        $i++;
        $trouve=true;
    } catch (Exception $e) { //Si une partie a une date ou une autre info essentielle illisible, on zappe juste cette partie
        //echo 'Debug : erreur ',  $e->getMessage(), "\n";
    }
}

if(!$trouve){ //Si on a trouvé aucune partie à exporter
    echo 'Erreur, aucune partie exportable';
    exit;
}else{

    header('Content-Type: text/calendar; charset=utf-8');
    header('Content-Disposition: attachment; filename=URcalendar.ics');
    
    for ($x=1; $x <= $i-1; $x++) {          
        echo ${'ics'.$x}->to_string();
        echo "\n";    
    }
} ?>