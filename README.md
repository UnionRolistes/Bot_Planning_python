Bot Planning Python
===================

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


