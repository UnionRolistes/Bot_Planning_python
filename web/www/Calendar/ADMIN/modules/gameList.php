<?php
session_start();

/*UR_Bot © 2020 by "Association Union des Rôlistes & co" is licensed under Attribution-NonCommercial-ShareAlike 4.0 International (CC BY-NC-SA)
To view a copy of this license, visit http://creativecommons.org/licenses/by-nc-sa/4.0/
Ask a derogation at Contact.unionrolistes@gmail.com*/


//On ouvre le Xml :        
if (!file_exists("../../data/events.xml")) {
    exit('Echec lors de la récupération des parties');
}
$xml = simplexml_load_file('../../data/events.xml'); ?>


<!--Partie affichage : -->
<!DOCTYPE html>
<html lang="fr" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Liste des parties - Admin</title>

    <link rel="stylesheet" href="../../../css/master.css">
    <link rel="stylesheet" href="../../../css/styleDark.css">
    <link rel="stylesheet" href="../../css/styleEventsList.css">

    <link rel="icon" type="image/png" href="../../../img/ur-bl2.png">

    <script src="../../js/copy.js"></script>
</head>
<body>
    
    <header>
        <h1 class="titleCenter">Liste des parties - ADMIN</h1>
        <input class="titleCenter" type="button" onclick="window.location.href='../index.php'" value="Accueil admin"/>
    </header>
    <section id="URform">
                


<?php
$trouve=false;
$i=0;
foreach ($xml->partie as $partie) { 

    try{  
            $titre=$partie->titre;
            $capacite=$partie->capacite;
            $minimum=$partie->minimum;
            $date=new DateTime($partie->date); //On choisit DateTime face à DateTimeImmutable pour un ajout des heures plus simples
            $heure=$partie->heure;
            $duree=$partie->duree;
            $type=$partie->type;
            $MJ=$partie->mj;
            $systeme=$partie->systeme;
            $pjMineur=$partie->pjMineur;
            $plateformes=$partie->plateformes; 
            $details=$partie->details;
            $lien=$partie->lien;
            ?>

        <fieldset>
            Le <?=$date->format('d/m/Y')?>, <strong><?=$titre?></strong><br><br>
        
            <strong>Type : </strong><?=$type?><br>
            <strong>Système : </strong><?=$systeme?><br>
            <strong>Mineurs : </strong><?=$pjMineur?><br><br>
            <strong>Heure : </strong><?=$heure?><br><br>
      
            <input type="button" onclick="window.location.href='gameFormSaving.php?ID=<?=$partie->attributes()?>'" value="Pré-remplir le formulaire"/>
            <input type="button" onclick="copyToClipboard(<?=(string)$partie->attributes()?>)" value="Copy to clipboard"/><img src="../../../img/copy2.jpg" style="width: 20px; height: 20px;"/>      
            
        </fieldset>

        <textarea id="copyText<?=$partie->attributes()?>" cols="30" rows="10" hidden>
**Titre : **<?=$titre?>

**Type : **<?=$type?>

**Date : **Le <?=$date->format('d/m')?> à <?=$heure?>

**Durée moyenne : **<?=$duree?>

**Nombre de joueurs : **<?php if (intval($capacite)==intval($minimum)){echo $capacite;}else{echo $minimum.'~'.$capacite;}?>

**MJ : ** <?=$MJ?>

**Système : **<?=$systeme?>

**PJ mineur : **<?=$pjMineur?>

**Plateformes : **<?=$plateformes?>

**Détails : **<?=$details?>
        </textarea>



    <?php
    } catch (Exception $e) { //Si une partie a une date ou une autre info essentielle illisible, on zappe juste cette partie
    //echo 'Debug : erreur ',  $e->getMessage(), "\n";
    }    
} ?>
        
    </section>

</body>
<?php include('../../../pages/footer.html'); ?>
</html>