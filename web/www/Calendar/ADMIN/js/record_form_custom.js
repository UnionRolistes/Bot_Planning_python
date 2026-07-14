//Version custom de  /Web_Planning/js/record_form.js : Elle se contente de sauvegarder les données remplies et non de les afficher. 
//Car posait des problèmes quand on passait du formulaire de partie à ce formulaire : les données originales remplies écrasaient les nouvelles d'ici

for (input of document.querySelectorAll("form input, form select, form textarea")) {
    if (input.type == "text" && input.name != "subject" || input.type == 'date' || input.type == 'textarea') {
        
        localStorage.setItem(input.name, input.value);
        //console.log(localStorage);      
    } 

    else if (input.type == 'radio') {

            localStorage.setItem(input.name, input.value);
            //console.log(localStorage);
    }
    else if (input.tagName == 'SELECT') { 
        localStorage.setItem(input.name, input.selectedIndex);       
    }
    else if (input.type == 'checkbox') {
            localStorage.setItem(input.name + "_" + input.value, input.checked);     
    }
    //else
    //    console.error(`This type of input isn't handled :\n${input.tagName} ${input.type}`)
}