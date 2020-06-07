#!/env/bin/python
import discord


class Bot_Planing(discord.Client):
    async def on_ready(self):
        print(f"{self.user} is ready!")


def main():
    app = Bot_Planing()
    app.run(open("key.txt").read())


if __name__ == "__main__":
    main()
