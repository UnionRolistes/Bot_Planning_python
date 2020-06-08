#!/env/bin/python
import discord
import datetime
import hashlib
import os


class Bot_Planing(discord.Client):
    async def on_ready(self):
        print(f"{self.user} is ready!")

    async def on_message(self, msg):
        if not msg.author.bot:
            if msg.content.startswith("$cal"):

                def getToken(user):
                    day = ("0" + str(datetime.date.day))[-2:]
                    salt = open("/usr/local/etc/BotPlanning/salt.txt").read()
                    token = user + salt + day
                    hash = hashlib.md5(token.encode()).hexdigest()
                    return f"https://urplanning.unionrolistes.fr/?token={hash}"

                await msg.delete()
                await msg.channel.send(f"{msg.author.mention}, Veuillez suivre le lien suivvant qui va vous être envoyé en MP")
                userToken = getToken(str(msg.author))
                await msg.author.send(f"{msg.author.mention} utilise le lien suivant : {userToken}")


def main():
    app = Bot_Planing()
    app.run(open("/usr/local/etc/BotPlanning/key.txt").read())

if __name__ == "__main__":
    main()
