/*UR_Bot © 2020 by "Association Union des Rôlistes & co" is licensed under Attribution-NonCommercial-ShareAlike 4.0 International (CC BY-NC-SA)
To view a copy of this license, visit http://creativecommons.org/licenses/by-nc-sa/4.0/
Ask a derogation at Contact.unionrolistes@gmail.com*/

function chgMode(){ //Change le Css Sombre <--> Clair

	if (document.getElementById("mode").innerHTML == "Clair ☀")
	{
		document.getElementById("mode").innerHTML = "Sombre 🌙";
		var oldlink1 = document.getElementsByTagName("link").item(1);
		var newlink1 =  document.createElement("link");
		newlink1.setAttribute("rel", "stylesheet");
		newlink1.setAttribute("href", "css/styleDark.css"); //CSS pour l'affichage sombre. Nom et contenu personnalisable

		document.getElementsByTagName("head").item(0).replaceChild(newlink1, oldlink1);
	}
	else {
		
		document.getElementById("mode").innerHTML = "Clair ☀";
		var oldlink1 = document.getElementsByTagName("link").item(1);
		var newlink1 =  document.createElement("link");
		newlink1.setAttribute("rel", "stylesheet");
		newlink1.setAttribute("href", "css/styleLight.css"); //CSS pour l'affichage clair. Nom et contenu personnalisable

		document.getElementsByTagName("head").item(0).replaceChild(newlink1, oldlink1);	
	}
}