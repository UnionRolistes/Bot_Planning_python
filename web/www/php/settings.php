<?php //Fichier contenant les variables communes à plusieurs pages, comme le lien urplanning.unionrolistes.fr par exemple
$siteURL="http://planning.unionrolistes.fr";
// Le conteneur web-planning et planning-api partagent le réseau Docker de la
// stack base_base : appel par nom de service.
$planningApiBaseUrl = getenv('PLANNING_API_BASE_URL') ?: 'http://planning-api:3000';
?>