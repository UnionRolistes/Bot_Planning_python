function copyToClipboard(id) {

  var copyText = document.getElementById("copyText"+id);

  var tempInput = document.createElement("textarea");
  tempInput.style = "position: absolute; left: -1000px; top: -1000px";
  tempInput.value = copyText.value;
  document.body.appendChild(tempInput);

  tempInput.select();
  tempInput.setSelectionRange(0, 99999); 
  document.execCommand("copy");
  document.body.removeChild(tempInput);
  alert("Copied !");

  /*On passe par un input invisible temporaire, car on ne peut pas copier d'un textarea à l'attribut hidden. Et l'avantage du textarea face au input type="text" est qu'il conserve les sauts de ligne */
}