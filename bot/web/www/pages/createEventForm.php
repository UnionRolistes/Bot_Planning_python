<!--UR_Bot © 2020 by "Association Union des Rôlistes & co" is licensed under Attribution-NonCommercial-ShareAlike 4.0 International (CC BY-NC-SA)
To view a copy of this license, visit http://creativecommons.org/licenses/by-nc-sa/4.0/
Ask a derogation at Contact.unionrolistes@gmail.com-->

<!--Dépendances : tous les tags "name" ont une orthographe importante, et sont repris dans plusieurs codes python (cgi/create_post; Bot_Planning_python/.../cog.py; Bot_Base/urpy/xml.py dans la fonction add_event)  -->

<form method=post action="http://api.unionrolistes.fr/planning/jdr" id="URform" onsubmit="sendForm(event)">

    <!-- Connexion discord. Les 3 inputs suivant seront inutiles si l'écriture du webhook dans le fichier fonctionne, car ils seront justement écrits dans le fichier (Voir Bot_Planning_python/cog_planning/cog.py fonction jdr) -->
    <input type=hidden name="webhook_url" value="<?= isset($_SESSION['webhook']) ? $_SESSION['webhook'] : "" ?>">
    <input type=hidden name="user_id" value="<?= isset($_SESSION['user_id']) ? $_SESSION['user_id'] : "" ?>">
    <input type=hidden name="pseudo" value="<?= isset($_SESSION['pseudo']) ? $_SESSION['pseudo'] : "" ?>">

    <fieldset id="connectField">
        <legend>Maître du jeu 👑</legend>
        <?php
        if (isset($_SESSION['avatar_url']) and isset($_SESSION['username'])) {
            echo '<div>';
            echo "<img src=\"" . $_SESSION['avatar_url'] . "\"/>";
            echo $_SESSION['username'];
            echo '<input type="button" value="Deconnexion" id="deconnexion" onclick="window.location.href=\'php/logout.php\'"/>';
            echo '</div>';
        } else
            echo '<div><input type="button" value="Me connecter" id="connexion" onclick="window.location.href=\'php/get_authorization_code.php\'"/></div>'
        ?>
    </fieldset>

    <label>Nombre de joueurs</label>
    <div id="range" style="color:black !important" aria-describedby="nbTxt">
        <script>
            var range = document.getElementById('range');

            noUiSlider.create(range, {
                start: [2, 7],
                step: 1,
                range: {
                    'min': 1,
                    'max': 16
                },
                padding: [0, 0],
                connect: true

            });
        </script>
    </div>
    <small id="nbTxt" name="nbJoueurs" class="annotation">Moins de 5 joueurs</small>

    <input type="hidden" value="1" name="minJoueurs" id="minJoueurs" />
    <input type="hidden" value="5" name="maxJoueurs" id="maxJoueurs" />


    <label>Type <span class="rouge">*</span></label>
    <select name="jdr_type" id="type" required>
        <option value="" disabled hidden selected></option> <!--Cette "option" force l'utilisateur à sélectionner une option-->
        <option> Initiation </option>
        <option> One shot </option>
        <option> Scénario </option>
        <option> Campagne </option>
    </select>

    <label>Fuseau horaire 🌎 <span class="rouge">*</span></label>

    <select name="jdr_horaire" id="horaire" required>
        <option value="" disabled hidden selected></option> <!--Cette "option" force l'utilisateur à sélectionner une option-->
        <option> GMT +1 FRANCE </option>
        <option> GMT -6 QUEBEC</option>
    </select>

    <label>Date 📅 et heure ⌚ <span class="rouge">*</span></label>

    <input autocomplete="off" id="date" name="jdr_date" type="text" class="tail-datetime-field" style="border-radius: 0px !important; height:40px; width:100%" required />

    <script type="text/javascript">
        document.addEventListener("DOMContentLoaded", function() {
            tail.DateTime(".tail-datetime-field", {
                dateFormat: "dd/mm/YYYY",
                timeFormat: "HH:ii",
                locale: "fr",
                timeSeconds: false,
                viewDecades: false,
                dateStart: new Date().toISOString().slice(0, 10)
            });
        });
    </script> <!--L'attribut required force un champ à être rempli pour envoyer le formulaire-->





    <!-- Nom campagne -->
    <label> Titre : <span class="rouge">*</span></label>
    <input type="text" placeholder="nom de la campagne ou du scenario" name="jdr_title" id="titre" max="70" required>


    <!-- Durée -->
    <label> Durée ⏱ <span class="rouge">*</span></label>
    <select name="jdr_length" id="selectorTime" required>
        <option value="" disabled hidden selected></option>
    </select>

    <div></div> <!--Pour faire de la place entre Durée et Jdr-->

    <!-- Sélection du système jdr -->
    <label>JDR 🎲 <span class="rouge">*</span> (Hors liste <input type="checkbox" id="checkJDR" unchecked onclick="chgJdrList()">)</label>

    <select name="jdr_system" id="system" required>
        <option hidden disabled selected value="">Liste des JdR proposés</option>
        <?php

        if (!file_exists('data/jdr_systems.xml')) {
            echo ('Echec lors de la récupération des parties');
        } else {
            # Generates all the options from an xml file
            $systems = simplexml_load_file("data/jdr_systems.xml");
            foreach ($systems as $optgroup) {
                echo '<optgroup label ="' . $optgroup['label'] . '">';
                foreach ($optgroup as $option) {
                    echo '<option>' . $option . '</option>';
                }
                echo '</optgroup>';
            }
        }
        ?>
    </select>

    <!-- N'apparait qu'en cochant la case hors liste -->
    <input type="text" style="display: none" placeholder="nom du jeu si hors liste" name="jdr_system_other" id="system2" max="37">

    <!-- Outils -->
    <label> Outils 🛠 </label>
    <div class="right">
        <input name="platform" type="checkbox" value="<?= $emot_twitch ?>"> Partie diffusée sur Twitch <img src="img/iconTwitch.png"><br>
        <input name="platform" type="checkbox" value="<?= $emot_roll20 ?>"> Partie jouée sur Roll20 <img src="img/iconRoll20.png"><br>
        <input name="platform" type="checkbox" value="<?= $emot_discord ?>"> Partie jouée sur Discord <img src="img/iconDiscord.png"><br>
        <input name="platform" type="checkbox" value=":space_invader:"> Partie jouée sur Autre <img src="img/iconAutre.png"><br>
    </div>

    <!-- PJ mineurs -->
    <label>PJ mineur 👶 <span class="rouge">*</span></label>
    <div class="right">
        <input type="radio" name="jdr_pj" required value="0"> &nbspOui
        <input type="radio" name="jdr_pj" value="1"> &nbspNon préférable
        <input type="radio" name="jdr_pj" value="2"> &nbspNon <!-- Attention : Changer les value ici implique de devoir les changer aussi dans cgi/const.py -->
    </div>

    <!-- Description -->
    <label>Description (optionnelle) 📄<br><br>
        <small class="annotation">Entrée pour revenir à la ligne</small>
    </label>
    <textarea rows="5" name="jdr_details" id="desc" style="resize: vertical;" autocomplete="on"></textarea>

    <div class="right">
        <button type="reset" onclick="document.getElementById('range').noUiSlider.set([2,7]);">Réinitialiser 🔄</button>
        <br><br>
        <button type="submit" name="submit" id="submit" <?php if (!isset($_SESSION['avatar_url']) or !isset($_SESSION['username'])) {
                                                            echo 'disabled ><b>Veuillez vous connecter';
                                                        } else {
                                                            echo 'style="background-color:#169719;"' ?>><b>Valider ✔<?php } ?></b></button>
        <!--Bloque le bouton si on s'est pas connecté-->
    </div>


    <span class="beta"><b>Attention cet outil est en beta-test</b><br>
        <a href="https://github.com/UnionRolistes/Web_Planning" uk-icon="icon: github; ratio:1.5">GitHub</a></span>

</form>

<script>
    function sendForm(e) {
        // Empêche le formulaire de soumettre normalement
        e.preventDefault()

        // Récupère les données du formulaire
        var formData = new FormData(document.getElementById("URform"))
        let data = {
            ...Object.fromEntries(formData)
        }
        // let data = {...Object.fromEntries(formData), platform: platforms}
        // get all the values checked of platfrom in a array
        let platforms = []
        for (let i = 0; i < document.getElementsByName("platform").length; i++) {
            if (document.getElementsByName("platform")[i].checked) {
                platforms.push(document.getElementsByName("platform")[i].value)
            }
        }
        if (platforms.length > 0) {
            data.platform = platforms
        }


        // Récupère l'URL du formulaire
        var url = document.getElementById("URform").getAttribute("action")

        // Envoie les données du formulaire au serveur
        fetch(url, {
                method: "POST",
                headers: {
                    "Content-type": "application/json",
                    Authorization: "Bearer " + "<?= $_SESSION['access_token'] ?>",
                },
                body: JSON.stringify(data),
            })
            .then(async (response) => {
                data = {
                    status: response.status,
                    statusText: response.statusText,
                    headers: response.headers,
                    url: response.url,
                    ok: response.ok,
                    data: await response.json()
                }
                return data
            })
            .then((data) => {
                console.log(data)
                if (data.ok) {
                    // Si la réponse est bonne, ont dit que c'est ok
                    document.getElementById("URform").innerHTML =
                        `<h3 class="center-all" '>Votre fiche a bien été envoyée</h3>
                                <button class="center-all" onClick="window.location.reload();">retour</button>`
                } else {
                    // Sinon on dit que c'est pas bon et on affiche l'erreur example ```{"detail": [{"loc": ["string",0],"msg": "string","type": "string"}]}```
                    document.getElementById("URform").innerHTML =
                        `<h3 class="center-all">Erreur : ${data.data.detail[0].msg == "field required" ? "entré requis" : data.data.detail}</h3>
                                <p class="center-all" >Erreur : ${data.data.detail[0].loc[1]}</p>
                                <button class="center-all" onClick="window.location.reload();">retour</button>

                                `
                }
            })
    }
</script>

<!-- TODO : La restauration de la durée ne fonctionne pas, je pense que c'est parce que le select est fait en JavaScript-->