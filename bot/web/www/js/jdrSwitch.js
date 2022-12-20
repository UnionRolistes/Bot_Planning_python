
function chgJdrList(){ //Change le select de JDR en un champd de texte libre

	if(document.getElementById('checkJDR').checked){ //On affiche les tranches

		document.getElementById('system').value=""; //Pour pas que le texte rentré avant de cocher soit compté à l'envoi
		document.getElementById('system').style.display="none";
		document.getElementById('system').required = false;

		document.getElementById('system2').style.display="initial";
		document.getElementById('system2').required = true;
	}
	else{ //On réaffiche l'age

		document.getElementById('system2').value="";
		document.getElementById('system2').style.display="none";
		document.getElementById('system2').required = false;

		document.getElementById('system').style.display="initial";
		document.getElementById('system').required = true;
	}	
}