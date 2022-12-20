<?php if ( (!isset($_GET['view'])) || ($_GET['view']!="weeks" && $_GET['view']!="months")){$_GET['view']="weeks";} //Type de vue par défaut 
/*UR_Bot © 2020 by "Association Union des Rôlistes & co" is licensed under Attribution-NonCommercial-ShareAlike 4.0 International (CC BY-NC-SA)
To view a copy of this license, visit http://creativecommons.org/licenses/by-nc-sa/4.0/
Ask a derogation at Contact.unionrolistes@gmail.com*/
?>

<!DOCTYPE html>
<html lang="fr" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Le calendrier</title>

   <?php if ($_GET['view']=="months"){$style="Months";}
   else{$style="Weeks";} ?>
    <link rel="stylesheet" href="css/styleCalendar<?=$style?>.css">
    <link rel="icon" type="image/png" href="../img/ur-bl2.png">
    <script type="text/javascript" src="js/requestCalendar.js"></script>
</head>
<body>
    <header class="flex-horizontal">
        <nav class="flex-horizontal">
            <button id="btn_Weeks" class="btn_Switch" <?php if ($_GET['view']=="weeks"){echo "disabled";}?> onclick="window.location.href='index.php?view=weeks'">Semaine</button>
            <button id="btn_Months" class="btn_Switch" <?php if ($_GET['view']=="months"){echo "disabled";}?> onclick="window.location.href='index.php?view=months'">Mois</button>
        </nav>

        <h1> Calendrier JDR <h1>

        <input type="hidden" id="viewType" value=<?=$_GET['view'];?>>
        <nav>
            <?php if ($_GET['view']=="months"){
                $valuePrevious="-1 month";
                $valueNext="+1 month";
            } else{
                $valuePrevious="-1 week";
                $valueNext="+1 week";
            }?>
            <button class="btn-change btn_previous" value="<?=$valuePrevious;?>"><--</button>
            <button class="btn-change btn_current" value="reset">Aujourd'hui</button>
            <button class="btn-change btn_next" value="<?=$valueNext;?>">--></button>              
        </nav>
    </header>
    
    <div id="calendarFrame">
        <?php 
            if($_GET['view']=="months"){include('pages/calendarMonths.php');}
            else{include('pages/calendarWeeks.php');}
        ?>
        
    </div>

    <script src="js/requestCalendar.js"></script>
</body>
<?php include('../pages/footer.html'); ?>
</html>