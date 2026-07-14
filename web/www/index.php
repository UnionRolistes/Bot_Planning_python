<?php
if (session_status() != PHP_SESSION_ACTIVE)
    session_start();

/*UR_Bot © 2020 by "Association Union des Rôlistes & co" is licensed under Attribution-NonCommercial-ShareAlike 4.0 International (CC BY-NC-SA)
To view a copy of this license, visit http://creativecommons.org/licenses/by-nc-sa/4.0/
Ask a derogation at Contact.unionrolistes@gmail.com*/

# this is not to leak authotification information
# stored in config.php when pushing to github
if(!file_exists("php/config.php")){
    copy("php/config.php.default", "php/config.php");
}

require("php/config.php");

if (isset($_GET['webhook']))
    $_SESSION['webhook'] = $_GET['webhook'];

$emot_twitch = ' <:custom_emoji_name:434370263518412820> ';
$emot_roll20 = ' <:custom_emoji_name:493783713243725844> ';
$emot_discord = ' <:custom_emoji_name:434370093627998208> ';
$emot_autre = ' :space_invader: ';

?>


<!DOCTYPE html>
<html lang="fr" dir="ltr">
<head>
    <title>Planning</title>
    <meta charset="utf8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="css/master.css">
    <link rel="stylesheet" href="css/styleDark.css">
    
    <link rel="icon" type="image/png" href="img/ur-bl2.png">

    <script src="js/updateSliderText.js"></script><!--Met à jour le mini-texte du nombre de joueurs en fonction du slider-->
    <script src="js/durationSelect.js"></script><!--Menu déroulant affichant les durées possibles -->
    <script src="js/colorModeSwitch.js"></script><!-- Switch du mode sombre à clair-->
    <script src="js/jdrSwitch.js"></script><!-- Case à cocher pour switch de la liste de JDR à un champ libre-->

    <!--On utilise un script externe pour le slider et le calendrier de choix de date et durée-->
    <script src="js/nouislider.js"></script> <!--Pour le slider du nombre de joueurs-->
    <link rel="stylesheet" href="css/nouislider.css">

    <script src="js/tail.datetime.js"></script><!-- Pour le calendrier du choix de la date-->
    <script src="js/tail.datetime-fr.js"></script>
    <link type="text/css" rel="stylesheet" href="css/tail.datetime-default.css">


</head>
<body onload="durationSelect();updateSliderText();"> 

<!--updateSliderText met à jour le texte situé sous le slider du nombre de joueur. durationSelect remplit le select des durées de parties (30m, 1h, ...) -->
    <header>
        <h1 class="titleCenter">Création de partie</h2>
    </header>
    <section>

    <?php //Affichage des erreurs : Rajouter des lignes si on rajoute d'autres codes d'erreurs (optimisable en les mettant dans un fichier si on commence à en avoir beaucoup)
      
      if (isset($_GET['error'])){ 
        $error=$_GET['error'];
      /*  if($error=='invalidData') echo '<span class="rouge">Données invalides. Veuillez vérifier le formulaire</span>'; //--> Pas encore fonctionnel côté Python
        //TODO : faire la même vérification de données que sur Web_Presentation
        //Voir php/create_event.php*/

        if($error=='envoi') echo '<span class="rouge">Erreur lors de la création de la partie. Si le problème persiste, veuillez contacter un administrateur</span>';
        //Voir php/create_event.php

        if($error=='isPosted') echo '<span class="vert">Votre partie a bien été postée</span>';
        //Envoyée par php/create_event.php

    } ?>

    <!-- Button for changing color mode -->
        <div id="modeDiv">
        <label id="mode">Sombre 🌙</label>					
        
            <label class="switch">
                <input type="checkbox" onclick="chgMode()">
                <span class="slider round"></span>
            </label>
        </div>

    <?php include('pages/createEventForm.php'); ?>

    </section>
    <script src="js/record_form.js"></script> <!--Sauvegarde les données déjà rentrées-->
</body>

<?php include('pages/footer.html'); ?>

</html>