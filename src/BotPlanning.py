#!/usr/bin/env python
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
                    day = datetime.datetime.now().strftime("%d")
                    salt = open("/usr/local/etc/BotPlanning/salt.txt").read()
                    token = user + salt + day
                    hash = hashlib.md5(token.encode()).hexdigest()
                    return f"http://urplanning.unionrolistes.fr/?token={hash}"

                await msg.delete()
                await msg.channel.send(f"{msg.author.mention}, Veuillez suivre le lien suivvant qui va vous être envoyé en MP")
                userToken = getToken(str(msg.author))
                await msg.author.send(f"{msg.author.mention} utilise le lien suivant : {userToken}")


def getKey():
    try:
        keypath = "/usr/local/etc/BotPlanning/key.txt"
        key = open(keypath).read()
        return key;
    except FileNotFoundError:
        raise Exception("Bot Key not set on {}: No files".format(keypath))

def main():
    app = Bot_Planing()
    app.run(getKey())

if __name__ == "__main__":
    main()
