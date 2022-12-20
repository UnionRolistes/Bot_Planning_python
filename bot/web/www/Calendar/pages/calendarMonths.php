<!-- Génère le calendrier d'un mois -->
<!--UR_Bot © 2020 by "Association Union des Rôlistes & co" is licensed under Attribution-NonCommercial-ShareAlike 4.0 International (CC BY-NC-SA)
To view a copy of this license, visit http://creativecommons.org/licenses/by-nc-sa/4.0/
Ask a derogation at Contact.unionrolistes@gmail.com-->

<?php
    if (session_status() != PHP_SESSION_ACTIVE)
        session_start();
        
    $daysOfTheWeek = array('Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche');
    $months = array('Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre');
    //Tableau pour les mois francais

    if (!isset($_SESSION['monday']))
        $_SESSION['monday'] = new DateTimeImmutable('monday this week');

    if (isset($_POST['timeInterval']))
        if($_POST['timeInterval']=="reset")
            $_SESSION['monday'] = new DateTimeImmutable('monday this week');
        else
            $_SESSION['monday'] = $_SESSION['monday']->add(date_interval_create_from_date_string($_POST['timeInterval']));
    

    $monday = $_SESSION['monday']; 
?>
<form method="post" class="flex-horizontal" action="php/downloadAll-ics.php"> 
    <input type="hidden" name="downloadAll-ics" value="OK">
    <input type="submit" value="Exporter le calendrier">
</form>

<h2 class="titleCenter"><?=$months[$monday->format("m")-1].' '.$monday->format("Y")?></h2>
<section class="calendar">
    
    <div></div> <!--Pour la case vide en haut à gauche du calendrier -->
    <div class="header">
        <ul class="weekDays">
            <?php
                # Jours de la semaine
                foreach ($daysOfTheWeek as $day)
                    echo '<li>' . $day . '</li>';                
            ?>
        </ul>

        <?php 
            $lundiDebutMois=new DateTimeImmutable(date("Y-m-d", strtotime('monday this week', strtotime($monday->format('Y').'-'.$monday->format('m').'-01')))); 
            $debutMois=new DateTimeImmutable(date("Y-m-d", strtotime($monday->format('Y').'-'.$monday->format('m').'-01')));       
        ?>
    </div>
   
    <div class="timeslots-containers">
        <ul class="timeslots">
            <?php
                # Créneaux horaires
                for ($i = 1; $i < 6; $i++)
                    echo '<li>S'.$i.'</li>'
            ?>
        </ul>
    </div>


    <!-- Affichage des parties -->
    <div class="event-container">       

        <?php 
        $path="data/events.xml";
        if (isset($_POST['ajax'])){$path="../data/events.xml";} //Car les liens absolus ne marchent pas, et apres un appel Ajax c'est le fichier php/calendarWeeks qui est appelé, et plus index.php

        # Get the events from an xml file / From Discord
        if (!file_exists($path)) {
            exit('Echec lors de la récupération des parties');
        }
        $xml = simplexml_load_file($path);

        //Xml to array
        $tmp = json_encode($xml);
        $arrayXml = json_decode($tmp,TRUE);
        //var_dump($arrayXml['partie']);

        //Pour compter le nombre de parties le même jour :
        $nbDates = array_count_values(array_column($arrayXml['partie'], 'date'));

        //var_dump($nbDates);
        //Sort un tableau sous la forme $nbDates['2021-07-24'][0]=X (nombre de parties prévues cette date). 


        foreach ($xml->partie as $partie) { //Parcourt tout le xml

            try{                         
                $date=new DateTimeImmutable($partie->date);

                if ($date>=$debutMois && $date< $debutMois->add(date_interval_create_from_date_string('1 month')) ){ //Si la date est dans le mois actuellement simulé

                    //Jour :
                    $column=date('N', strtotime($partie->date)); //Sort l'index du jour dans sa semaine. 7 pour dimanche, 1 pour lundi, etc.

                    //S1 :
                    if($date>=$debutMois && $date<$debutMois->add(date_interval_create_from_date_string('next monday'))){
                        $row=2;
                    } //S2 :
                    else if ($date>=$lundiDebutMois->add(date_interval_create_from_date_string('next monday')) && $date<$lundiDebutMois->add(date_interval_create_from_date_string('2 weeks'))){
                        $row=11;
                    } //S3 :
                    else if ($date>=$lundiDebutMois->add(date_interval_create_from_date_string('2 weeks')) && $date<$lundiDebutMois->add(date_interval_create_from_date_string('3 weeks'))){
                        $row=21;
                    } //S4 :
                    else if ($date>=$lundiDebutMois->add(date_interval_create_from_date_string('3 weeks')) && $date<$lundiDebutMois->add(date_interval_create_from_date_string('4 weeks'))){
                        $row=31;
                    } //S5 :
                    else if ($date>=$lundiDebutMois->add(date_interval_create_from_date_string('4 weeks')) && $date<$lundiDebutMois->add(date_interval_create_from_date_string('5 weeks'))){
                        $row=40;
                    }

                    //row +5 si on veut en caser un 2eme. +10 si on veut changer de semaine (a quelques exceptions près)

                    //Pour afficher plusieurs parties cote à cote, ou bien juste le nombre si on a plus de 4 parties un meme jour
                    $str_date=(string)$partie->date;
                    
                    if(!isset($nbDates[$str_date])){
                        $nbDates[$str_date]=1;
                    }

                    if (!isset($nbDates[$str_date]['affichage'])){
                        $nbDates[$str_date]=array($nbDates[$str_date]);
                        $nbDates[$str_date]['affichage']="not done";
                    }
                                

                    $heure=explode("h", "$partie->heure");
                    if ($heure[1]=="" || !isset($heure[1])){$heure[1]="00";}
                    //Code couleur :
                    $color="green";//Par défaut, places disponibles
                    if (new DateTime($partie->date.' '.$heure[0].':'.$heure[1].":00") < new DateTime()){$color="gray";} //Si la date est passée 

                    //Si on doit afficher que le nombre de parties, et que ça a pas encore été fait :
                    $affichageMax=2; //Nombre de parties max qu'on peut afficher par jour. Au dessus de ce nombre, affichera juste "X parties prévues"
                    //$nbDates[$str_date][0] contient le nombre de parties prevues le jour $str_date

                    //$nbDates['2021-07-24']['affichage']="done" ou "not done". Comme ça, si une des 3+ parties prévues un jour a dejà affiché "X parties prévues", les autres de la même date n'auront pas besoin de le faire
                    if($nbDates[$str_date][0]>$affichageMax){ 

                        if ($nbDates[$str_date]['affichage']=="not done"){ ?>
                            <a href="php/monthsToWeeks.php?date=<?=$str_date?>" class="slot slotMonth" style="text-align: center; height: 140px; grid-row: <?=$row?>; grid-column: <?=$column?>; background: <?=$color?>">
                                <strong><?=$nbDates[$str_date][0]?> parties prévues</strong><br>
                                le <strong><?=$date->format("d/m")?></strong>
                            </a>  

                    <?php 
                            $nbDates[$str_date]['affichage']=="done";
                        }
                    }
                    else{
                        //Si on a pas besoin d'afficher que le nombre parties, on fait l'affichage classique en mettant les vignettes cote à cote:
                
                        if($nbDates[$str_date][0]>1){
                            $row=$row+($nbDates[$str_date][0]-1)*4;
                            $nbDates[$str_date][0]--;
                        }
                    

                        //Code couleur :
                        $color="green";//Par défaut, places disponibles

                      //  if (intval($partie->inscrits) >= intval($partie->minimum)){$color="rgb(194, 194, 21)";}//Si on a le nombre de joueurs minimum    
                        if (intval($partie->inscrits) >= intval($partie->capacite)){$color="rgb(255, 17, 17)";} //Si c'est complet
                        if (new DateTime($partie->date.' '.$heure[0].':'.$heure[1].":00") < new DateTime()){$color="gray";} //Si la date est passée                   
                        ?>
                    
                        
                        <a href="php/monthsToWeeks.php?date=<?=$partie->date?>" class="slot slotMonth" style="height: 70px; grid-row: <?=$row?>; grid-column: <?=$column?>; background: <?=$color?>">
                            <strong><?=$partie->titre?></strong><br>
                            <strong>Systeme : </strong><?=$partie->systeme?><br>
                            <strong>Capacité : </strong>
                            <?php if ((int)$partie->minimum==(int)$partie->capacite){echo $partie->minimum;}
                            else{echo $partie->minimum.'~'.$partie->capacite.'<br>';}?>
                            
                            <!--Affichage du nombre d'inscrits mis de côté car non fonctionnel côté Python -->

                            <strong>Durée : </strong><?=$partie->duree?><br>
                            Le <strong><?=$date->format("d/m")?></strong>
                        </a>           
            <?php   }
                }
            } catch (Exception $e) { //Si une partie a une date ou une autre info essentielle illisible, on zappe juste cette partie
                //echo 'Debug : erreur ',  $e->getMessage(), "\n";
            }  
        } ?>         
            
    </div>
</section>