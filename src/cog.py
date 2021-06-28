"""
Cog regroupant les fonctions de gestion du planning.\n
Commandes :\n
    \t$cal\n
    \t$edit\n
    \t$done\n
    \t$cancel\n
"""

import logging
import sys
import asyncio

import discord
from discord.ext import commands, tasks
from URbot.planning.src import settings, strings
import re
import discord.errors
import requests
from utils import error_log
from const import *

async def get_public_ip() -> str:
    """
    Return public IP.

    Returns
    -------
        str
            IP address.
    """
    return requests.get('https://api.ipify.org').text


async def get_informations(msg: discord.Message) -> dict[str, str]:
    """
    Extract information from an announcement message.

    Parameters
    ----------
        msg : str
            Announcement message.

    Returns
    -------
        dict[str, str]
            A dict linking an information name to its value.
            Ex: " ** Type ** One Shoot  " becomes {'type': 'One Shoot'}
    """
    infos = {}

    # Matches strings of the form : ' ** {name} **  {value} ' ending on ':', '**' or '\n'
    for match in re.finditer("\*\*(.*)\*\* *(?:\n| )(.*)\n(?::|\*\*|\n)", msg.embeds[0].description):
        infos[match.group(1).strip().lower()] = match.group(2)
    return infos


class MyContext(commands.Context):
    def __init__(self, ctx: commands.Context):
        super().__init__(**ctx.__dict__)

    async def send(self, content=None, **kwargs):
        if 'delete_after' not in kwargs:
            kwargs['delete_after'] = settings.msg_delete_delay
        await self.message.delete(delay=settings.msg_delete_delay)
        await super().send(content, **kwargs)


class Planning(commands.Cog):
    """
    A set of commands and utils functions.
    """

    def __init__(self, bot: commands.Bot):
        """ Create a cog dedicated to Planning management. """
        self.users_edit_mode = {}
        self.bot: commands.Bot = bot
        self.msgs_to_delete: asyncio.Queue[list] = asyncio.Queue()

        # noinspection PyTypeChecker
        self.planning_channel: discord.TextChannel = None

        with open("../modèle_fiche_planning.txt", 'r', encoding='utf8') as f:
            self.planning_announcement_model = f.read()

    @tasks.loop(seconds=10)
    async def empty_delete_queue(self):
        if self.msgs_to_delete:
            msgs: list[discord.Message] = await self.msgs_to_delete.get()
            for msg in msgs:
                await msg.delete()

    @commands.command()
    async def cal(self, ctx: commands.Context):
        """
        <em> Bot command </em> that sends a link in dms to create a new game.
        This command only works on a guild that owns a properly named channel
        with a webhook.
        Channel name is editable in the settings module.

        Parameters
        ----------
        ctx: command context
        """
        ctx = MyContext(ctx)

        # checks that $cal is called in the right place
        if isinstance(ctx.channel, discord.DMChannel):
            await ctx.send(strings.on_cal_dm_channel)
        elif isinstance(ctx.channel, discord.TextChannel):
            anncmnt_channel = discord.utils.get(ctx.guild.channels, name=settings.announcement_channel)
            if not anncmnt_channel:
                await ctx.send(strings.on_cal_channel_not_found.format(channel=settings.announcement_channel))
            else:
                try:
                    webhooks = await anncmnt_channel.webhooks()
                    webhook: discord.Webhook = webhooks[0]
                except discord.errors.Forbidden:
                    error_log("Impossible d'obtenir les webhooks.",
                              "Le bot nécessite la permission de gérer les webhooks")
                    await ctx.author.send(strings.on_permission_error)
                except IndexError:
                    await ctx.send(strings.on_cal_webhook_not_found.format(channel=settings.announcement_channel))
                else:
                    await ctx.send(strings.on_cal)
                    await ctx.author.send(
                        strings.on_cal_link.format(link=f"http://{await get_public_ip()}.nip.io?webhook={webhook.url}"))

    @commands.command()
    async def edit(self, ctx: commands.Context):
        """
        <em> Bot command </em> starting edit mode.<br>
            You can only edit a game you've created.<br>
            Editing occurs in dms via a series of questions.

        Parameters
        ----------
        ctx: command context
        """
        ctx = MyContext(ctx)
        channel = ctx.channel
        if isinstance(channel, discord.TextChannel) and channel.name == settings.announcement_channel:
            msg: discord.Message = ctx.message
            if not msg.reference:
                await ctx.send(strings.on_edit_without_reply)
            elif not msg.reference.resolved.author.bot:
                await ctx.send(strings.on_edit_not_editable)
            else:
                msg_to_edit: discord.Message = msg.reference.resolved

                try:
                    mj = await self.get_mj(msg_to_edit)

                    infos = await get_informations(msg_to_edit)

                    old_platfs = infos['plateformes']

                    new_platfs = ", ".join(emoji_to_platform[e].capitalize() for e in old_platfs.strip().split(" ") if e)
                    infos['plateformes'] = new_platfs
                    channel: discord.TextChannel = ctx.channel
                    webhooks = await channel.webhooks()

                    # sends a copy of announce in the dms
                    await mj.send("", embed=discord.Embed(type='rich', description=self.create_descr(infos)))
                except IndexError:
                    await ctx.send(strings.on_edit_not_editable)
                else:
                    if mj != ctx.author:
                        await ctx.send(strings.on_edit_not_mj)
                    else:
                        await ctx.send(strings.on_edit_start)
                        # extracts information out of the message
                        self.users_edit_mode[msg.author] = [msg_to_edit, infos, EDIT_MODE_PROMPT, "", webhooks[0]]
                        await mj.send(strings.on_edit_prompt)
        else:
            await ctx.send(strings.on_edit_wrong_channel)

    def create_descr(self, infos, b=0):
        new_descr = self.planning_announcement_model.format(
            type=infos['type'],
            date=infos['date'],
            title=infos['titre'],
            length=infos['durée moyenne du scénario'],
            pseudoMJ=infos['mj'],
            system=infos['système'],
            platforms=infos['plateformes'] if not b else " ".join(
                [platform_to_emoji[p.lower()] for p in infos['plateformes'].split(", ")]),
            details=infos['détails'],
            minors_allowed=infos['pj mineur']
        )
        return new_descr

    @commands.Cog.listener()
    async def on_message(self, msg: discord.Message):
        author: discord.User = msg.author
        channel = msg.channel
        if isinstance(channel, discord.DMChannel) and author in self.users_edit_mode.keys():
            msg_to_edit, infos, edit_mode, info_name, webhook = self.users_edit_mode[author]
            if msg.content.startswith("$done") or msg.content.startswith("$cancel"):
                del self.users_edit_mode[author]
            elif edit_mode == EDIT_MODE_PROMPT:
                info_to_change = msg.content.lower().strip()
                if info_to_change in infos.keys():
                    self.users_edit_mode[author][3] = info_to_change
                    self.users_edit_mode[author][2] = EDIT_MODE_CONTENT
                    await channel.send(strings.on_edit_content_prompt.format(info=f"**{info_to_change.capitalize()}**"))
                else:
                    await channel.send(strings.on_edit_unrecognized)
            elif edit_mode == EDIT_MODE_CONTENT:
                self.users_edit_mode[author][2] = EDIT_MODE_PROMPT
                infos[info_name] = msg.content
                await channel.send("", embed=discord.Embed(type='rich', description=self.create_descr(infos)))
                await channel.send(strings.on_edit_prompt)

        elif msg.author.bot and author.id != self.bot.user.id:
            # await ctx.author.send("Création réussie, l'Union des Rôlistes vous souhaite une belle expérience !")
            await msg.add_reaction("✅")
            await msg.add_reaction("❌")

    @commands.command()
    async def done(self, ctx: commands.Context):
        if isinstance(ctx.channel, discord.DMChannel) and ctx.author in self.users_edit_mode.keys():
            msg: discord.Message = self.users_edit_mode[ctx.author][0]
            embed = msg.embeds[0].copy()
            embed.description = self.create_descr(self.users_edit_mode[ctx.author][1], 1)
            await self.users_edit_mode[ctx.author][4].edit_message(msg.id, embed=embed)
            await ctx.send(strings.on_edit_success)

    @commands.command()
    async def cancel(self, ctx: commands.Context):
        if isinstance(ctx.channel, discord.DMChannel) and ctx.author in self.users_edit_mode.keys():
            await ctx.send(strings.on_edit_cancel)

    @commands.Cog.listener()
    async def on_ready(self):
        self.planning_channel = discord.utils.get(self.bot.get_all_channels(), name="planning-jdr")
        print("We have logged in as {}!".format(self.bot.user))

    @commands.Cog.listener()
    async def on_raw_reaction_add(self, payload: discord.RawReactionActionEvent):
        channel: discord.TextChannel = await self.bot.fetch_channel(payload.channel_id)
        if payload.user_id != self.bot.user.id and isinstance(channel,
                                                              discord.TextChannel) and channel.name == 'planning-jdr' and payload.emoji.name in (
                "✅", "❌"):
            msg = await channel.fetch_message(payload.message_id)
            mp = ""

            try:
                if payload.emoji.name == "✅":
                    mp = strings.on_join
                    await msg.remove_reaction("❌", payload.member)
                elif payload.emoji.name == "❌":
                    check_reactions_users = await discord.utils.get(msg.reactions, emoji="✅").users().flatten()
                    if discord.utils.get(check_reactions_users, id=payload.user_id):
                        await msg.remove_reaction("✅", payload.member)

            except discord.errors.Forbidden:
                print("ERROR:bot: Impossible de mettre à jour les réactions.",
                      "Le bot nécessite la permission de gérer les messages.", file=sys.stderr)
            else:
                if mp:
                    mjs_found = re.search("<@[0-9]*>", msg.embeds[0].description)
                    await self.send_to_mj(msg, mp.format(user=f"<@{payload.user_id}> — {payload.member}"))

    async def get_mj(self, msg) -> discord.User:
        mjs_found = re.search("<@[0-9]*>", msg.embeds[0].description)
        if not mjs_found:
            print(f"ERROR:BOT | Le message {msg.id} ne mentionne pas de MJ.", file=sys.stderr)
        else:
            mj_id = int(mjs_found[0][2:-1])
            user = await self.bot.fetch_user(mj_id)
            return user

    async def send_to_mj(self, msg, mp):
        mj = await self.get_mj(msg)
        await mj.send(mp)

    @commands.Cog.listener()
    async def on_raw_reaction_remove(self, payload: discord.RawReactionActionEvent):
        channel: discord.TextChannel = await self.bot.fetch_channel(payload.channel_id)
        if isinstance(channel, discord.TextChannel) and channel.name == 'planning-jdr' and payload.emoji.name == "✅":
            msg = await channel.fetch_message(payload.message_id)
            mp = strings.on_leave.format(user=f"<@{payload.user_id}>")

            await self.send_to_mj(msg, mp)


if __name__ == '__main__':
    logging.basicConfig(level=logging.INFO)
    ur_bot = commands.Bot(command_prefix=settings.command_prefix)
    ur_bot.add_cog(Planning(ur_bot))
    with open('../../bot_token', 'r') as f:
        bot_token = f.read()

    ur_bot.run(bot_token)
