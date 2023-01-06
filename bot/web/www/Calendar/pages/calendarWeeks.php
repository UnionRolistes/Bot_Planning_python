<?php
if (session_status() != PHP_SESSION_ACTIVE) {
    session_start();
}

$daysOfTheWeek = array('Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche');

if (!isset($_SESSION['monday'])) {
    $_SESSION['monday'] = new DateTimeImmutable('monday this week');
}

if (isset($_POST['timeInterval'])) {
    if ($_POST['timeInterval'] == "reset") {
        $_SESSION['monday'] = new DateTimeImmutable('monday this week');
    } else {
        $_SESSION['monday'] = $_SESSION['monday']->add(date_interval_create_from_date_string($_POST['timeInterval']));
    }
}

$monday = $_SESSION['monday'];
?>

<h2 class="titleCenter">Semaine du <?= $monday->format("d/m") ?></h2>
<section class="calendar">

    <div></div> <!--Pour la case vide en haut à gauche du calendrier -->
    <div class="header">
        <ul class="weekDays">
            <?php
            # Jours de la semaine
            foreach ($daysOfTheWeek as $day)
                echo '<li>' . $day . '</li>'
                    ?>
            </ul>

            <ul class="dayNumbers-container">
                <?php
            # Dates de la semaine
            for ($i = 0; $i < 7; $i++)
                echo '<li>' . $monday->add(date_interval_create_from_date_string($i . ' days'))->format("d/m") . '</li>';
            ?>
        </ul>
    </div>


    <div class="timeslots-containers">
        <ul class="timeslots">
            <?php
            # Créneaux horaires
            for ($i = 0; $i < 24; $i++)
                echo '<li>' . (6 + $i) % 24 . 'h </li>'
                    ?>
            </ul>
        </div>


        <!-- Affichage des parties -->
        <div class="event-container">
            <?php
            $path = "data/events.xml";
            if (isset($_POST['ajax'])) {
                $path = "../data/events.xml";
            } //Car les liens absolus ne marchent pas, et apres un appel Ajax c'est le fichier pages/calendarWeeks qui est appelé, et plus index.php

            # Get the events from an xml file / From Discord
            if (!file_exists($path)) {
                exit('Echec lors de la récupération des parties');
            }
            $xml = simplexml_load_file($path);


            //Systeme similaire à CalendarMonths, mais doit ici compter le nombre de parties qui se chevauchent et pas seulement le même jour
            //(Concession faite : 2 parties le même jour sont considérées chevauchantes. Un algorithme détectant vraiment des créneaux chevauchant serait trop complexe et pas très optimisé (parcourir le xml n^2 fois où n est le nombre de parties))

            //Xml to array
            $tmp = json_encode($xml);
            $arrayXml = json_decode($tmp, TRUE);
            //var_dump($arrayXml['partie']);

            //Pour compter le nombre de parties le même jour :
            $nbDates = array_count_values(array_column($arrayXml['partie'], 'date'));

            //var_dump($nbDates);
            //Sort un tableau sous la forme $nbDates['2021-07-24'][0]=X (nombre de parties prévues cette date).


            foreach ($xml->partie as $partie) { //Parcourt tout le xml

                try {
                    $date = new DateTimeImmutable($partie->date);
                    if ($date >= $monday && $date <= $monday->add(date_interval_create_from_date_string('6 days'))) { //Si la date est dans la semaine actuellement simulée

                        //Durée :
                        $duree = explode("h", "$partie->duree"); //Sépare X h Y en X et Y
                        if ($duree[1] == "00" || $duree[1] == "") {
                            $demieDuree = 0;
                        } else {
                            $demieDuree = 1;
                        }

                        $height = 60 * $duree[0] + 30 * $demieDuree;

                        //Heure de début :
                        $heure = explode("h", "$partie->heure");
                        if ($heure[1] == "00" || $heure[1] == "") {
                            $demieHeure = 0;
                        } else {
                            $demieHeure = 1;
                        }
                        if ($heure[1] == "" || !isset($heure[1])) {
                            $heure[1] = "00";
                        } //Utilisé plus tard pour détecter si l'heure est passée

                        $row = ($heure[0] - 5) * 2 - 1 + 1 * $demieHeure; //Formule permettant de passer de l'heure à la ligne où l'afficher dans le calendrier
                        //Permet de detecter les demies heures.
                        //NOTE : Ici je fais une précision à la demie heure près. Une partie comprise entre Xh00 et Xh30, ou entre Xh30 et X+1h00 sera affichée à Xh30 sur le calendrier (la vraie heure sera toujours visible dans les détails).
                        //Comme toutes les parties que j'ai vu sont à heure pile ou demies. A demander à Dae si il veut une precision au 1/4 heure près

                        //Jour :
                        $column = (date('N', strtotime($partie->date))) * 2 - 1; //Sort l'index du jour dans sa semaine. 7 pour dimanche, 1 pour lundi, etc. *2 car ici on sépare chaque colonne de jour en 2 sous colonnes, pour pouvoir en afficher 2 cote à cote

                        //Pour afficher plusieurs parties cote à cote, ou bien juste le nombre si on a plus de 4 parties un meme jour
                        $str_date = (string) $partie->date;

                        if (!isset($nbDates[$str_date])) {
                            $nbDates[$str_date] = 1;
                        }

                        if (!isset($nbDates[$str_date]['affichage'])) {
                            $nbDates[$str_date] = array($nbDates[$str_date]);
                            $nbDates[$str_date]['affichage'] = "not done";
                        }

                        if (!isset($nbDates[$str_date]['voisin'])) {
                            $nbDates[$str_date]['voisin'] = false; //Servira à savoir si on doit rétrécir les parties pour etre cote à cote
                        }


                        //Code couleur :
                        $color = "green"; //Par défaut, places disponibles

                        //NOTE : Une partie dans le xml ne doit avoir qu'un seul attribut, qui doit etre l'ID, sinon il faut modifier le code ci-dessous pour récupérer l'Id précisement (avec $partie->attributes()->id peut-etre ?)
                        $inscription = '<a href="pages/popupEvent.php?ID=' . $partie->attributes() . '" target="_blank">Details et inscription</a><br>';


                        //if (intval($partie->inscrits) >= intval($partie->minimum)){$color="rgb(194, 194, 21)";}//Si on a le nombre de joueurs minimum
                        if (intval($partie->inscrits) >= intval($partie->capacite)) {
                            $inscription = "COMPLET";
                            $color = "rgb(255, 17, 17)";
                        } //Si c'est complet
                        if (new DateTime($partie->date . ' ' . $heure[0] . ':' . $heure[1] . ":00") < new DateTime()) {
                            $color = "gray";
                            $inscription = "TERMINÉ";
                        } //Si la date est passée


                        //Si on doit afficher que le nombre de parties, et que ça a pas encore été fait :
                        //$nbDates[$str_date][0] contient le nombre de parties prevues le jour $str_date

                        //$nbDates['2021-07-24']['affichage']="done" ou "not done". Comme ça, si une des 3+ parties prévues un jour a dejà affiché "X parties prévues", les autres de la même date n'auront pas besoin de le faire
                        if ($nbDates[$str_date][0] > 2) {

                            if ($nbDates[$str_date]['affichage'] == "not done") { ?>
                            <a href="pages/eventsList.php?date=<?= $str_date ?>" class="slot slotWeek"
                                style="text-align: center; height: 140px; grid-row: 19; grid-column: <?= $column ?>; background: <?= $color ?>">
                                <strong>
                                    <?= $nbDates[$str_date][0] ?> parties prévues
                                </strong><br>
                                le <strong><?= $date->format("d/m") ?></strong>
                            </a>

                            <?php
                            $nbDates[$str_date]['affichage'] == "done";
                            }
                        } else {
                            //Si on a pas besoin d'afficher que le nombre parties, on fait l'affichage classique en mettant les vignettes cote à cote:

                            $width = "";

                            if ($nbDates[$str_date][0] > 1) {
                                $column = $column + 1;
                                $nbDates[$str_date][0]--;
                                $width = 'min-width: 6vw; max-width: 6vw;';
                                $nbDates[$str_date]['voisin'] = true;
                                //Après le 1er affichage sur une date, toutes les prochaines de cette meme date devront etre aussi retrecies
                            }
                            if ($nbDates[$str_date]['voisin']) {
                                $width = 'min-width: 6vw; max-width: 6vw;';
                            }
                            ?>


                        <div class="slot"
                            style="height: <?= $height ?>px; grid-row: <?= $row ?>; grid-column: <?= $column ?>; background: <?= $color ?>;<?= $width ?>">
                            <strong><?= $partie->titre ?></strong><br><br>
                            <strong>Type : </strong><?= $partie->type ?><br>
                            <strong>Systeme : </strong><?= $partie->systeme ?><br>
                            <strong>Mineurs : </strong><?= $partie->pjMineur ?><br>
                            <strong>Capacité : </strong>
                            <?php if ((int) $partie->minimum == (int) $partie->capacite) {
                                echo $partie->minimum;
                            } else {
                                echo $partie->minimum . '~' . $partie->capacite . '<br>';
                            } ?>
                            <!--<strong>MJ : </strong><=$partie->mj?><br> Les infos complètes sont affichées, du type "<@Nevrose> [Nevrose#8184]". Trop long pour un affichage de cette taille, posera des problèmes de lisibilité pour les petites vignettes et mobile-->

                            <?php
                            //<strong>Capacité : </strong><?=$partie->inscrits .>/<?=$partie->capacite .><br>
                            //$s="s"; if ($partie->minimum<=1){$s="";}
                            //echo $partie->minimum.'joueur'.$s.' minimum';
                            //Affichage du nombre d'inscrits mis de côté car non fonctionnel côté Python ?>


                            <br><br>À <strong><?= $partie->heure ?></strong><br>
                            <?= $inscription ?><br>
                        </div>
                        <?php
                        }
                    }
                } catch (Exception $e) { //Si une partie a une date ou une autre info essentielle illisible, on zappe juste cette partie
                    //echo 'Debug : erreur ',  $e->getMessage(), "\n";
                }
            } ?>

    </div>
</section>