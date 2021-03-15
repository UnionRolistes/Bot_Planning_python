Bot Planning Python
===================
Description
Le BotPlanning(Python3.7) et FormulaireJdR (HTML CSS PHP)  est un projet lancé a l'initiative de l'Union des Rôlistes (http://unionrolistes.fr)
un bot discord capable de généré des messages correctement mis en forme, annoncant de prochaine partie de JdR, quel soit physique ou a distance.
actuellement les message finaux sont visible sur le discord de l'union des Rôlistes via un Webhook dans la section #Planning-JdR
.

Il est tout à fait possible qu'un autre groupe, association, club utilise ce bot Opensource pour son propre usage non commercial. (en citant/creditant l'union des Rôlistes )


**how to install**

> sudo apt install git ranger pm2 apache python3.7

Le service à eté dévloppé pour tourné sur un serveur débian10 ou plus.
il nessecite un serveur apache pour la partie web (ainsi qu'un serveur discord avec un webhook)
une fois connecter sur votre serveur en SSH avec votre terminal favori, suivre les instruction suivante pour l'installation.

**A partir du paquet DEB.**
cd /usr/locale/src/
gitclone https://github.com/UnionRolistes/Bot_Planning_python ./Bot_Planning
sudo dpkg -i Bot_Planning.deb          
sudo nano /usr/local/etc/key.txt
 ouvrir https://discord.com/developers/applications et copier le token et le coller dans le fichier txt ouvert précédement.
 sauvegardé avec Ctrl S et quitter avec Ctrl X

**A, partir du code sources**
systemctl enable Bot_Planning.service --now 
systemctl status Bot_Planning.service 

   - dependance


**how to start**

cd /usr/locale/src/Bot_Planning/
systemctl status Bot_Planning.service 
si aucun service ne fonctionne.
systemctl enable Bot_Planning.service --now 

**how to stop**
systemctl status Bot_Planning.service 
pour verifier que le service fonctionne
systemctl desable Bot_Planning.service --now 

**how to manage /control**



**How to update**
git pull 
systemctl desable Bot_Planning.service --now 
systemctl enable Bot_Planning.service --now 

**how to use (in discord)**
une fois sur votre serveur discord, et apres avoir vérifier que le bot (ou role des bot) pouvais ecrire dans le canal où vous vous trouvez
ecrivez $cal 
la commande s'effacera, puis vous receverez un message privé avec les instruction.

**Futur Update**

    Reaction automatique par le bot

    Mp au MJ lors d'une inscription.

    Pouvoir éditer un message

    Rapport d'erreur par email et sur discord

    Rapport de plantage serveur/service.

    Partage directe vers twitter et facebook avec proposition de #tag

    Affichage horizontal en ligne pour inscription en convention, classement par jours / semaines

    $ping "pong ! (BotPlanning)"

    $credit "Ceux qui m'ont créer !"

    $don "soutenez le JdR, Soutenez L'UR !"

    changelog.txt

    $changelogs $logs $devlogs



**credit / contributeur**
Dae#5125
Tonitch#2192
scribble#8876 

**donation link**


> Discord Bot Create by tonitch (d.tontich@gmail.com) for "UnionRolistes.fr"

Installation
------------

I created an .deb package that you can install with the command 
`dpkg -i botplanning.deb`
But i'm a archlinux user and i'm not familiar with deb packages system.
There might be some stuff missing like python dependencies

The bot run on python3 with all the lib in `requirements.txt`
If you know how to do better deb, feel free to submit a commit

**Important** You have to put the [discord's bot key](https://discord.com/developers/applications)
In `/usr/local/etc/key.txt` for the bot to work

Basic Usage
-----------

the bot run with systemd

- `systemctl enable botplanning.service --now` **Enable the bot to run at startup**
- `systemctl status botplanning.service` **See Bot Status**
- `systemctl restart botplanning.service` **To restart the bot**


