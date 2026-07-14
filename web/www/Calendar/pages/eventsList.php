<?php //Génère la liste des parties prévues à une date donnée. Est utilisée dans le cas où plus de 2 parties sont prévues le même jour dans l'affichage par semaine

/*UR_Bot © 2020 by "Association Union des Rôlistes & co" is licensed under Attribution-NonCommercial-ShareAlike 4.0 International (CC BY-NC-SA)
To view a copy of this license, visit http://creativecommons.org/licenses/by-nc-sa/4.0/
Ask a derogation at Contact.unionrolistes@gmail.com*/

if (!isset($_GET['date']) ){header('Location:../index.php');}
$date=$_GET['date'];

$dateObject=new DateTimeImmutable($date); 

//On ouvre le Xml :        
if (!file_exists("../data/events.xml")) {
    exit('Echec lors de la récupération des parties');
}
$xml = simplexml_load_file('../data/events.xml'); ?>


<!--Partie affichage : -->
<!DOCTYPE html>
<html lang="fr" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>L'agenda du rôliste - <?=$dateObject->format('d/m')?></title>

    <link rel="stylesheet" href="../../css/master.css">
    <link rel="stylesheet" href="../../css/styleDark.css">
    <link rel="stylesheet" href="../css/styleEventsList.css">

    <link rel="icon" type="image/png" href="../../img/ur-bl2.png">
</head>
<body>
    
    <header>
        <h1 class="titleCenter">Parties prévues le <?=$dateObject->format('d/m')?></h2>
    </header>
    <section id="URform">
    <div class="bloc">

        <div class="blocParties">
                


<?php
$trouve=false;
$i=0;
foreach ($xml->partie as $partie) { 

    try{  
        if ($partie->date==$date){ //On va afficher toutes les parties ayant la date donnée     

            $titre=$partie->titre;
            $heure=$partie->heure;
            $type=$partie->type;       
            $systeme=$partie->systeme;
            $pjMineur=$partie->pjMineur; 
            
            $color="green";//Par défaut, places disponibles
            if (new DateTime($partie->date) < new DateTime()){$color="gray";}
            ?>

    <div class="blocPartie" onmouseover="this.style.background='<?=$color?>'" onmouseout="this.style.background='';this.style.color='';">

            <strong>Titre : </strong><?=$titre?><br><br>
            <strong>Type : </strong><?=$type?><br>
            <strong>Système : </strong><?=$systeme?><br>
            <strong>Mineurs : </strong><?=$pjMineur?><br><br>
            <strong>Heure : </strong><?=$heure?><br><br>

            <input type="button" onclick="window.location.href='popupEvent.php?ID=<?=$partie->attributes()?>'" value="Détails"/>
        
    </div>

    <?php
        $trouve=true;
        $i++;
        }
    } catch (Exception $e) { //Si une partie a une date ou une autre info essentielle illisible, on zappe juste cette partie
    //echo 'Debug : erreur ',  $e->getMessage(), "\n";
    } 

    
    if($i%4==0){ //Correspond au nb de parties qu'on veut par ligne
    ?>
    </div>

    <div class="blocParties">      

    <?php
    }
} 

if(!$trouve){ //Si on a trouvé aucune partie ce jour là (cad si on triche par l'URL)
    echo '<h2 class="titleCenter">Aucune partie ce jour-ci</h2>';
}
?>
        </div>
    </section>

</body>
<?php include('../../pages/footer.html'); ?>
</html>