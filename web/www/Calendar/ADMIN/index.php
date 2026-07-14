<?php
session_start();

/*UR_Bot © 2020 by "Association Union des Rôlistes & co" is licensed under Attribution-NonCommercial-ShareAlike 4.0 International (CC BY-NC-SA)
To view a copy of this license, visit http://creativecommons.org/licenses/by-nc-sa/4.0/
Ask a derogation at Contact.unionrolistes@gmail.com*/


$_SESSION['securedURadmin']="securedID"; //Arbitraire. Le but est de ne pas etre facilement devinable. 
//Doit correspondre au code dans Web_Planning/Calendar/pages/popupEvents.php

?>


<!--Partie affichage : -->
<!DOCTYPE html>
<html lang="fr" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Section Administrateur</title>

    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="../../css/styleDark.css">
    <link rel="stylesheet" href="../css/styleEventsList.css">

    <link rel="icon" type="image/png" href="../../img/ur-bl2.png">
</head>
<body>
    
    <header>
        <h1 class="titleCenter">Section Administrateur</h1>
    </header>
    <section id="URform">
        <h2 class="titleCenter">Modules</h2><br>
        <br>
        Vous êtes bien connecté en tant que modérateur ! Vous pouvez maintenant accéder à de nouveaux boutons sur les descriptions de partie<br>
        Si vous ne voyez plus ces boutons, veuillez revenir sur cette page<br>

  
        <div class="titleCenter">
            <input type="button" onclick="window.location.href='modules/gameList.php'" value="Liste des parties"/><br>
            <input type="button" onclick="window.location.href='modules/crypt.php'" value="Créer un nouveau login administrateur"/>  
        <div>
        
    </section>

</body>
<?php include('../../pages/footer.html'); ?>
</html>