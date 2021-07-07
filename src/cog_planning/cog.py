"""
Cog regroupant les fonctions de gestion du planning.\n
Commandes :\n
    \t$cal\n
    \t$edit\n
    \t$done\n
    \t$cancel\n
"""

import sys
import logging
import re
from importlib import resources

import discord
import discord.errors
from discord.ext import commands

import urpy
from urpy.utils import error_log, get_public_ip, get_informations

import bot.URbot
from cog_planning.const import *
from cog_planning import strings


from cog_planning import settings
import cog_planning.info


class Planning(urpy.MyCog):
    """
    A set of commands and utils functions.

    @author Lyss
    @mail <delpratflo@cy-tech.fr>
    @date 28/06/21
    """
    def __init__(self, bot: bot.URbot.URBot):
        """ Create a cog dedicated to Planning management. """
        super(Planning, self).__init__(bot)
        self.edit_mode_users = {}
        self.planning_channel: discord.TextChannel = None
        self.planning_announcement_model = urpy.get_planning_anncmnt_mdl()

        self.bot.add_to_command('edit', self.on_edit)
        self.bot.add_to_command('done', self.on_done, self.on_cancel_or_done)
        self.bot.add_to_command('cancel', self.on_cancel, self.on_cancel_or_done)

    @commands.command()
    async def cal(self, ctx: commands.Context):
        """
        Envoie un lien pour créer une partie

        Cette commande fonctionne seulement si le serveur possède un salon bien nommé et disposant d'un webhook.

        Le nom du salon est modifiable dans les paramètres.
        """
        ctx = urpy.MyContext(ctx, delete_after=6)

        # checks location of $cal call
        if isinstance(ctx.channel, discord.DMChannel):
            # DM Channel
            await ctx.send(strings.on_cal_dm_channel)
        elif isinstance(ctx.channel, discord.TextChannel):
            # Text Channel
            anncmnt_channel = discord.utils.get(ctx.guild.channels, name=settings.announcement_channel)
            if not anncmnt_channel:
                # Announcement channel not found
                await ctx.send(strings.on_cal_channel_not_found.format(channel=settings.announcement_channel))
            else:
                # Announcement channel found
                try:
                    # Try to get webhook
                    webhooks = await anncmnt_channel.webhooks()
                    webhook: discord.Webhook = webhooks[0]
                except discord.errors.Forbidden:
                    # Insufficient permissions
                    error_log("Impossible d'obtenir les webhooks.",
                              "Le bot nécessite la permission de gérer les webhooks")
                    await ctx.author.send(strings.on_permission_error)
                except IndexError:
                    # No webhooks found
                    await ctx.send(strings.on_cal_webhook_not_found.format(channel=settings.announcement_channel))
                else:
                    # Webhook found
                    await ctx.send(strings.on_cal)
                    # sends link in dm
                    await ctx.author.send(
                        strings.on_cal_link.format(link=f"http://urplanning.unionrolistes.fr?webhook={webhook.url}"))

    async def on_edit(self, ctx: commands.Context):
        """
        Édite un message

        Cette commande démarre le mode d'édition.

        Vous pouvez seulement modifiez une annonce que vous avez créée.
        L'édition se déroule en mp sous la forme d'une série de questions.
        """
        ctx = urpy.MyContext(ctx, delete_after=6)
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
                        self.edit_mode_users[msg.author] = [msg_to_edit, infos, EDIT_MODE_PROMPT, "", webhooks[0]]
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

    @staticmethod
    def get_credits():
        return resources.read_text(cog_planning.info, 'credits.txt')

    @staticmethod
    def get_version():
        """ Return version.txt of bot. """
        return resources.read_text(cog_planning.info, 'version.txt')

    @staticmethod
    def get_name():
        return resources.read_text(cog_planning.info, 'name.txt')

    @commands.Cog.listener()
    async def on_message(self, msg: discord.Message):
        author: discord.User = msg.author
        channel = msg.channel
        if isinstance(channel, discord.DMChannel) and author in self.edit_mode_users.keys():
            msg_to_edit, infos, edit_mode, info_name, webhook = self.edit_mode_users[author]

            if edit_mode == EDIT_MODE_PROMPT:
                info_to_change = msg.content.lower().strip()
                if info_to_change in infos.keys():
                    self.edit_mode_users[author][3] = info_to_change
                    self.edit_mode_users[author][2] = EDIT_MODE_CONTENT
                    await channel.send(strings.on_edit_content_prompt.format(info=f"**{info_to_change.capitalize()}**"))
                else:
                    await channel.send(strings.on_edit_unrecognized)
            elif edit_mode == EDIT_MODE_CONTENT:
                self.edit_mode_users[author][2] = EDIT_MODE_PROMPT
                infos[info_name] = msg.content
                await channel.send("", embed=discord.Embed(type='rich', description=self.create_descr(infos)))
                await channel.send(strings.on_edit_prompt)

        elif isinstance(channel, discord.TextChannel) and msg.author.bot and author.id != self.bot.user.id and channel.name==settings.announcement_channel:
            # await ctx.author.send("Création réussie, l'Union des Rôlistes vous souhaite une belle expérience !")
            await msg.add_reaction("✅")
            await msg.add_reaction("❌")

    async def on_done(self, ctx: commands.Context):
        if isinstance(ctx.channel, discord.DMChannel) and ctx.author in self.edit_mode_users.keys():
            self.edit_mode_users[ctx.author][2] = EDIT_MODE_FINISHED
            msg: discord.Message = self.edit_mode_users[ctx.author][0]
            embed = msg.embeds[0].copy()
            embed.description = self.create_descr(self.edit_mode_users[ctx.author][1], 1)
            await self.edit_mode_users[ctx.author][4].edit_message(msg.id, embed=embed)
            await ctx.send(strings.on_edit_success)

    async def on_cancel(self, ctx: commands.Context):
        if isinstance(ctx.channel, discord.DMChannel) and ctx.author in self.edit_mode_users.keys():
            self.edit_mode_users[ctx.author][2] = EDIT_MODE_FINISHED
            await ctx.send(strings.on_edit_cancel)

    async def on_cancel_or_done(self, ctx: commands.Context):
        msg = ctx.message
        author = ctx.author

        del self.edit_mode_users[author]

    @commands.Cog.listener()
    async def on_ready(self):
        self.planning_channel = discord.utils.get(self.bot.get_all_channels(), name="planning-jdr")
        print("\t| Planning started.")

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
    with open('../../../../../bot_token', 'r') as f:
        bot_token = f.read()

    ur_bot.run(bot_token)
