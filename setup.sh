#!/bin/sh

cp bin/* /usr/local/bin/

cp botplanning.service /etc/systemd/system/

echo "Entrez le token du bot (ctrl-shift-v): "
read key
echo "Entrez le salt utilisé par la page web: "
read salt

echo $key > /usr/local/etc/BotPlanning/key.txt
echo $salt > /usr/local/etc/BotPlanning/salt.txt

cp -r ./ /usr/local/src/BotPlanning/
cd /usr/local/src/BotPlanning

systemctl enable botplanning.service --now
