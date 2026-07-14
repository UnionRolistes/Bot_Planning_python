<?php
/*UR_Bot © 2020 by "Association Union des Rôlistes & co" is licensed under Attribution-NonCommercial-ShareAlike 4.0 International (CC BY-NC-SA)
To view a copy of this license, visit http://creativecommons.org/licenses/by-nc-sa/4.0/
Ask a derogation at Contact.unionrolistes@gmail.com*/

//Devra enregistrer les données dans le local storage, comme notre système actuel, puis renvoyer vers le formulaire. Les données préremplies vont alors s'afficher
//Faire ici un formulaire aux name identique à l'original, puis appeler la fonction JS record_form

//TODO : Les valeurs de PJ mineur et des checkboxes des plateformes ne se sauvegarde pas bien

/*
$emot_twitch = ' <:custom_emoji_name:434370263518412820> ';
$emot_roll20 = ' <:custom_emoji_name:493783713243725844> ';
$emot_discord = ' <:custom_emoji_name:434370093627998208> ';
$emot_autre = ' :space_invader: '; Mis de côté car restauration des plateformes non fonctionnelle sur le formulaire de partie*/




if (!isset($_GET['ID']) || !is_numeric($_GET['ID'])){echo 'Pas de partie sélectionnée';}
else{


$ID=$_GET['ID'];


//On ouvre le Xml :        
if (!file_exists("../../data/events.xml")) {
    echo 'Echec lors de la récupération des parties';
    exit();
}

$xml = simplexml_load_file('../../data/events.xml');

//(Pas trouvé de fonction "find by id" qui fonctionne bien. Et dans les 2 cas ca revient à un parcours de xml, donc on ne perd pas en optimisation)
$trouve=false;
foreach ($xml->partie as $partie) {

    try{  
        if ($partie->attributes()==$ID){         
            
            $heure=explode("h", "$partie->heure");
            if ($heure[1]==""){$heure[1]="00";}

            $type=$partie->type;
            $dateString=$partie->date;
            $date=$dateString.' '.$heure[0].':'.$heure[1];

            $titre=$partie->titre;
            $duree=$partie->duree;

            $duree=explode("h", $duree);
            if ($duree[1]=="00"){$duree[1]="";} 
            $fullDuree=$duree[0].'h'.$duree[1];

            $JDR=$partie->systeme;
            $description=$partie->details;


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
}
?>


<form method=post id="URform">
    
    <!--Type de partie-->           
    <select name="jdr_type" id="type" hidden>
        <option value="" disabled hidden></option>
        <option <?php if($type=="Initiation"){echo 'selected';}?>> Initiation </option>
        <option <?php if($type=="One shoot"){echo 'selected';}?>> One shoot </option>
        <option <?php if($type=="Scénario"){echo 'selected';}?>> Scénario </option>
        <option <?php if($type=="Campagne"){echo 'selected';}?>> Campagne </option>
    </select> 
            
    <!--Date-->
    <input id="date" name="jdr_date" type="text" value="<?=$date?>" hidden/>
                
    <!-- Nom campagne -->         
    <input type="text" value="<?=$titre?>" name="jdr_title" id="titre" max="70" hidden> 									
            

    <!-- Durée -->           
    <select name="jdr_length" id="selectorTime" hidden>
        <option value="1h" <?php if($fullDuree=="1h"){echo 'selected';}?>>1h</option>
        <option value="1h30" <?php if($fullDuree=="1h30"){echo 'selected';}?>>1h30</option>
        <option value="2h" <?php if($fullDuree=="2h"){echo 'selected';}?>>2h</option>
        <option value="2h30" <?php if($fullDuree=="2h30"){echo 'selected';}?>>2h30</option>
        <option value="3h" <?php if($fullDuree=="3h"){echo 'selected';}?>>3h</option>
        <option value="3h30" <?php if($fullDuree=="3h30"){echo 'selected';}?>>3h30</option>
        <option value="4h" <?php if($fullDuree=="4h"){echo 'selected';}?>>4h</option>
        <option value="4h30" <?php if($fullDuree=="4h30"){echo 'selected';}?>>4h30</option>
        <option value="5h" <?php if($fullDuree=="5h"){echo 'selected';}?>>5h</option>
        <option value="5h30" <?php if($fullDuree=="5h30"){echo 'selected';}?>>5h30</option>
</select> <!--Sauvegarde non fonctionnelle côté formulaire -->								
                


    <!-- Sélection du système jdr -->       
    
    <select name ="jdr_system" id="system" hidden>
        <option hidden disabled value="">Liste des JdR proposés</option>
                    <?php
                    $trouve=false; //Si le Jdr n'est pas dans la liste, on le sauvegardera en tant qu'hors liste

                        if (!file_exists('../../../data/jdr_systems.xml')) {
                            echo 'Echec lors de la récupération des parties';
                            exit();
                        }
                        else{
                            # Generates all the options from an xml file
                            $systems = simplexml_load_file("../../../data/jdr_systems.xml");
                            foreach ($systems as $optgroup) {
                                echo '<optgroup label ="' . $optgroup['label'] .'">';
                                foreach ($optgroup as $option) {
                                    if((string)$option==$JDR){echo '<option selected>' . $option . '</option>'; $trouve=true;}
                                    else{echo '<option>' . $option . '</option>'; }                 
                                }
                                echo '</optgroup>';
                            }
                        }
                    ?>
    </select>	

    <!-- JDR hors liste si besoin -->
    <input type="text" <?php if (!$trouve){ echo 'value="'.$JDR.'"';}?> name="jdr_system_other" id="system2" max="37" hidden> 									         
        
    <!-- Outils -->   
    <!--   <input name="platform" type="checkbox" value="<=$emot_twitch?>">
        <input name="platform" type="checkbox" value="<=$emot_roll20?>">
        <input name="platform" type="checkbox" value="<=$emot_discord?>" checked>
        <input name="platform" type="checkbox" value=":space_invader:"> Mis de côté car restauration des plateformes non fonctionnelle sur le formulaire de partie -->


    <!-- PJ mineurs -->       
      <!--  <input type="radio" name="jdr_pj" checked value="0" >
        <input type="radio" name="jdr_pj" value="1">
        <input type="radio" name="jdr_pj" value="2"> Mis de côté car restauration des PJ mineurs non fonctionnelle sur le formulaire de partie-->

        
    <!-- Description -->          
    <textarea rows="5" name ="jdr_details" id="desc" hidden><?=$description?></textarea>	

</form>


<script src="../js/record_form_custom.js"></script>
<script type='text/javascript'>
    window.location.href='../../../index.php'
</script> <!--Mettre en commentaire ce script et enlever les "hidden" pour débugger-->

Formulaire sauvegardé : <?=$titre?> <br>
Veuillez vous diriger vers http://planning.unionrolistes.fr
<!-- (Message d'attente au cas où la redirection ne serait pas immédiate) -->
<?php } ?>