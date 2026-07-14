<?php
/*UR_Bot © 2020 by "Association Union des Rôlistes & co" is licensed under Attribution-NonCommercial-ShareAlike 4.0 International (CC BY-NC-SA)
To view a copy of this license, visit http://creativecommons.org/licenses/by-nc-sa/4.0/
Ask a derogation at Contact.unionrolistes@gmail.com*/

//Fenetre popup sur un clic d'une partie. Affiche tous les détails de la partie ainsi que 2 liens vers le message Discord et pour télécharger le Ics de l'événement

if (!isset($_GET['ID']) || !is_numeric($_GET['ID'])){echo 'Pas de partie sélectionnée';}
else{
session_start();

$ID=$_GET['ID'];


//On ouvre le Xml :        
if (!file_exists("../data/events.xml")) {
    echo 'Echec lors de la récupération des parties';
    exit();
}

$xml = simplexml_load_file('../data/events.xml');

//(Pas trouvé de fonction "find by id" qui fonctionne bien. Et dans les 2 cas ca revient à un parcours de xml, donc on ne perd pas en optimisation)
$trouve=false;
foreach ($xml->partie as $partie) {

    try{  
        if (intval($partie->attributes())==$ID){         

            $titre=$partie->titre;
            $capacite=$partie->capacite;
            $minimum=$partie->minimum;
            $inscrits=$partie->inscrits;
            $date=new DateTime($partie->date); //On choisit DateTime face à DateTimeImmutable pour un ajout des heures plus simples
            $heure=$partie->heure;
            $duree=$partie->duree;
            $type=$partie->type;
            $MJ=$partie->mj;
            $systeme=$partie->systeme;
            $pjMineur=$partie->pjMineur;
            $plateformes=$partie->plateformes; 
            if($plateformes==""){$plateformes="Autre";}
            $details=$partie->details;
            $lienWeb=$partie->lien;
        
            $splitLienWeb = explode("/channels", $lienWeb); //Sépare dans un tableau la partie avant et après /channels 
            $splitLienWeb[0]="discord://discordapp.com";//On remplace le début pour avoir un lien vers l'appli de bureau
            $lienDesktop=$splitLienWeb[0]."/channels".$splitLienWeb[1];

            $trouve=true;
            break;
        }
    } catch (Exception $e) { //Si une partie a une date ou une autre info essentielle illisible, on zappe juste cette partie
    //echo 'Debug : erreur ',  $e->getMessage(), "\n";
    } 
}
if(!$trouve){
    echo 'Erreur, partie introuvable. Veuillez contacter un administrateur';
    exit;
} ?>


<!--Partie affichage : -->
<!DOCTYPE html>
<html lang="fr" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?=$titre?></title>

    <link rel="stylesheet" href="../../css/master.css">
    <link rel="stylesheet" href="../../css/styleDark.css">
    <link rel="stylesheet" href="../css/stylePopup.css">

    <link rel="icon" type="image/png" href="../../img/ur-bl2.png">

    <script src="../js/copy.js"></script>
</head>
<body>
    
    <header>
        <h1 class="titleCenter"><?=$titre?></h2> 
    </header>
    <img src="../../img/secured.png" style="width: 20px; height: 20px; float: right" title="section ADMIN" onclick="location.href='../ADMIN/index.php'"/>
    <section id="URform">
   
        <label><strong>Type : </strong></label>
        <div class="right"><?=$type?></div>

        <label><strong>Systeme : </strong></label>
        <div class="right"><?=$systeme?></div>
              
        <label><strong>Date et heure : </strong></label>
        <div class="right">Le <?=$date->format("d/m")?> à <?=$heure?></div>

        <label><strong>Durée : </strong></label>
        <div class="right"><?=$duree?></div>

        <label><strong>Capacité : </strong></label>
        <div class="right">Entre <strong><?=$minimum?> et <?=$capacite?></strong> joueurs <!--- <=$inscrits?> joueurs inscrits  Pas fonctionnel côté Python--></div>

        <label><strong>Mineurs : </strong></label>
        <div class="right"><?=$pjMineur?></div>

        <label><strong>MJ de la partie : </strong></label>
        <div class="right"><?=$MJ?></div>

        <label><strong>Description : </strong></label>
        <div class="right"><?=$details?></div>

        <label><strong>Plateformes : </strong></label>
        <div class="right"><?=$plateformes?></div>


        <fieldset>
            <legend>Inscriptions</legend>

<!--Optimisable plus tard en faisant des jolis boutons au lieu de passer par 2 form inutiles ici (les boutons héritant du css du formulaire, ils ont design qui ne convient pas ici. Il faudrait que cette page ait son propre css unique-->
            <form method="post" id="firstButton" action="<?=$lienWeb?>">
                <input type="submit" value="M'inscrire (Discord Web)">
            </form>

          <!--  <a id="firstButton" href="<=$lienWeb?>">M'inscrire (Discord Web)</a>
            <a href="<=$lienDesktop?>">M'inscrire (Discord Bureau)</a> -->
            
            <form method="post" action="<?=$lienDesktop?>">
                <input type="submit" value="M'inscrire (Discord Bureau)">
            </form> 
            
        <?php  
            $heure=explode("h", "$partie->heure");
            if($heure[1]==""){
                $heure[1]=0;
            }
            $duree=explode("h", "$partie->duree");
            if($duree[1]==""){
                $duree[1]=0;
            }
            $date->setTime($heure[0], $heure[1]);

            //GMT -2 pour la France. Autres GMT à gérer
            $date->add(date_interval_create_from_date_string('-2 hours'));

            //echo $date->format("Y-m-d H:i"); ?>

            <form method="post" action="../php/download-ics.php">
                <input type="hidden" name="date_start" value="<?=$date->format("Y-m-d H:i")?>">
                <?php $dateFin=$date->add(date_interval_create_from_date_string('+'.$duree[0].' hours '.$duree[1].' minutes'));//echo $dateFin->format("Y-m-d H:i");?>
                
                <input type="hidden" name="date_end" value="<?=$dateFin->format("Y-m-d H:i")?>">
                <input type="hidden" name="location" value="http://unionrolistes.fr/">
                <input type="hidden" name="description" value="<?=$partie->details?>">
                <input type="hidden" name="summary" value="<?=$partie->titre?>">
                <input type="hidden" name="lien" value="<?=$lienWeb?>">
                <input type="submit" value="Ajouter à mon agenda">
            </form><br>
<?php
            if(isset($_SESSION['securedURadmin'])){
                if ($_SESSION['securedURadmin']=="securedID"){ ?>
                        
                <input type="button" onclick="window.location.href='../ADMIN/modules/gameFormSaving.php?ID=<?=$partie->attributes()?>'" value="Pré-remplir le formulaire"/>  
                <input type="button" onclick="copyToClipboard(<?=(string)$partie->attributes()?>)" value="Copy to clipboard"/><img src="../../img/copy2.jpg" style="width: 20px; height: 20px;"/>

                <?php 
                $heure=$partie->heure;
                $duree=$partie->duree;//On rétablit les dates sous le bon format. Notamment la heure et durée qui ont été explode
                ?>

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
                }
            } 
       ?>
        </fieldset>
    </section>

</body>
<?php include('../../pages/footer.html'); ?>
</html>
<?php } ?>