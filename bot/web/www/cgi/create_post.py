#!/opt/virtualenv/URBot/bin/python
from urpy import utils
#utils.html_header_webhook_not_supplied()
import cgi
import cgitb
import pickle
cgitb.enable()

from os import path

from discord import Webhook, RequestsWebhookAdapter, AsyncWebhookAdapter
import discord
import settings
from const import *

from urpy.xml import Calendar
from urpy import utils

# Logging
cgitb.enable(display= 1)  # , logdir="/usr/local/log/cgi")

#UR_Bot © 2020 by "Association Union des Rôlistes & co" is licensed under Attribution-NonCommercial-ShareAlike 4.0 International (CC BY-NC-SA)
#To view a copy of this license, visit http://creativecommons.org/licenses/by-nc-sa/4.0/
#Ask a derogation at Contact.unionrolistes@gmail.com

def get_payload(form) -> str:
    """ Process form data to create the webhook payload. """
    with open("modele_fiche_planning.txt", 'r', encoding="utf8") as f:
        model = f.read()

    # determines which values to show for the number of players
    maxP = form.getvalue('maxJoueurs')
    minP = form.getvalue('minJoueurs')
    if maxP == minP:
        players = maxP
    else:
        players = f"{maxP} (min {minP})"

    info, reactions = model.split("[")
    payload = info.format(
        type=form.getvalue('jdr_type'),
        title=form.getvalue('jdr_title'),
        date=form.getvalue('jdr_date'),
        horaire=form.getvalue('jdr_horaire'),
        players=players,
        length=form.getvalue('jdr_length'),
        pseudoMJ=f"<@{form.getvalue('user_id')}> [{form.getvalue('pseudo')}]",  # TODO handle server nicknames
        system=form.getvalue('jdr_system') if form.getvalue('jdr_system') else form.getvalue('jdr_system_other'),
        minors_allowed=minorsAllowed_to_str[int(form.getvalue('jdr_pj'))],
        platforms=" ".join(form.getlist('platform')),
        details=form.getvalue('jdr_details')
    )

    return payload


def get_webhook_url(form) -> str:
    #with open(f'{settings.tmp_wh_location}/wh', "rb") as f:
    #    array = pickle.load(f)
    #    wh_url, guild_id, channel_id = array[int(form.getvalue('user_id'))]
    #    return str(wh_url)
    return form.getvalue('webhook_url')

async def main():
    form = cgi.FieldStorage()        
    embed=discord.Embed(description=get_payload(form), type="rich").set_thumbnail(url=settings.logo_url)

    #Envoi sur Discord :
    webhook = Webhook.from_url(get_webhook_url(form), adapter=RequestsWebhookAdapter())
    webhook.send(
        "",
       allowed_mentions=discord.AllowedMentions(users=True),
       embed=discord.Embed(description=get_payload(form), type="rich").set_thumbnail(url=settings.logo_url),
       )

    #calendar = Calendar(path.abspath('../Calendar/data/events.xml'))
    #await calendar.add_event(form, embed)
    #calendar.save()


    # Redirects to main page
    utils.html_header_relocate(f"{settings.creation_form_url}?error=isPosted")



try:
    #utils.html_header_content_type()
    import os
    #print(os.listdir("../Calendar"))
    import asyncio
    asyncio.run(main())
except Exception as e:
    print("Content-Type: text/html")
    print()
    utils.html_header_relocate(f"{settings.creation_form_url}?error=envoi") #Debug : Mettre cette ligne en commentaire pour voir le détail des erreurs
    raise e
