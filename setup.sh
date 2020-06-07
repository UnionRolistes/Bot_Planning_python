#!/bin/sh

cp bin/BotPlanning /usr/local/bin/
cp bin/updateBotPlanning /usr/local/bin/

cp botplanning.service /etc/systemd/system/

echo "Entrez le token du bot (ctrl-shift-v): "
read key

echo $key > key.txt

cp -r ./ /usr/local/src/BotPlanning/
cd /usr/local/src/BotPlanning
git config pull.rebase false

systemctl enable botplanning.service --now
