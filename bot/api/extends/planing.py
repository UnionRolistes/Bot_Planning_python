from enum import Enum
import json
from fastapi import APIRouter, Header, HTTPException, Depends
from fastapi.security import HTTPBearer
from fastapi.responses import RedirectResponse
from pydantic import BaseModel, Field
from ..base._discord import get_discord_client  # in base api repo
import discord
import requests
import re
from dotenv import load_dotenv
import os
from discord import Client

load_dotenv()
JDR_CHANNEL = int(os.getenv("JDR_CHANNEL", 0))
LOGO_URL = os.getenv("LOGO_URL", "https://cdn.discordapp.com/attachments/652207549168484352/862020088788942868/ur-bl2.png")
URL_SITE_PLANNING = os.getenv("URL_SITE_PLANNING", "http://planning.unionrolistes.fr")


# start
security = HTTPBearer()
router = APIRouter(
    prefix="/planing",
    tags=["planing"],
    responses={404: {"description": "Not found"}},
)

# dependancie that check discord oauth token and get user info
# same as in prez.py (yep flemme de faire un fichier dans base api)
async def check_token_dep(authorization: HTTPBearer = Depends(security)):
    print('check_token_dep')
    if authorization:
        token = authorization.credentials
        try:
            print('try')
            response = requests.get("https://discord.com/api/users/@me", headers={
                "Authorization": f"Bearer {token}"
            })
            print('response')
            user = response.json()
            print(f'user : {user}')
            return user
        except Exception as e:
            print(e)
            raise HTTPException(status_code=401, detail="Invalid token")
    else:
        raise HTTPException(status_code=401, detail="No token provided")

@ router.get("/", tags=["planing"])
async def prez():
    return {"message": "Hello from planing API!"}


class JdrType(str, Enum):
    Initiation = "Initiation"
    OneShot = "One shot"
    Scenario = "Scénario"
    Campagne = "Campagne"

class jdrLore(str, Enum):
    Brigandyne = "Brigandyne"
    HomeBrew = "HomeBrew"
    DCritique = "D-Critique"
    GURPS = "GURPS"
    PbtA = "PbtA"
    SavageWolrd = "SavageWolrd"
    Tiny = "Tiny"
    Trash = "Trash"
    Agone = "Agone"
    Anima = "Anima"
    Antika = "Antika"
    ArsMagica = "Ars Magica"
    CielsCuivre = "Ciels_Cuivre"
    DD = "D&D (d&d, Ad&d, chronique, pathfinder)"
    AdD = "Ad&D"
    ChroniqueOubliees = "Chronique oubliées"
    Pathfinder = "Pathfinder"
    DefisFantastiques = "Défis Fantastiques"
    DiscWorld = "DiscWorld"
    DragonAge = "DragonAge"
    GobelinQuiSEndedit = "Gobelin qui s'en dédit"
    GOT = "GoT"
    Impertor = "Impertor"
    L5R = "L5R"
    LiberChronicle = "LiberChronicle"
    MyLittlePony = "MyLittlePony"
    Naheulbeuk = "Naheulbeuk"
    ReveDeDragon = "Rêve de Dragon"
    Ryuutama = "Ryuutama"
    Tolkien = "Tolkien"
    Shaan = "Shaan"
    SwordAndSorcery = "Sword and Sorcery"
    Yggdrasil = "Yggdrasil"
    YnnPryddein = "Ynn Pryddein"
    WarHammer = "WarHammer"
    _7eMer = "7e Mer"
    PavillionNoir = "Pavillion Noir"
    Cardinal = "Cardinal (Les lames Du)"
    Deadlands = "Deadlands"
    Cats = "Cats"
    Heroes_super_et_mutant_xmen = "Heroes (super et mutant Xmen)"
    hp = "HP"
    Nephilim = "Nephilim"
    COPS = "COPS"
    Cyberpunk = "Cyberpunk"
    Degenesis = "Dégénésis"
    EclipsePhase = "Eclipse phase"
    FallOut = "FallOut"
    Knight = "Knight"
    MetalAdv = "Metal Adv"
    Numenera = "Numenéra"
    Polaris = "Polaris"
    Starwars = "Starwars"
    TerraX = "Terra X"
    Zombie = "Zombie"
    BloodLust = "BloodLust"
    Cthulhu = "Cthulhu"
    w40kDarkHeresy = "w40k-DarkHeresy"
    INSMV = "INSMV"
    FéalsChroniqueDes = "Féals (Chronique des"
    OmbresDEsteren = "Ombres d'Esteren"
    Patient13 = "Patient 13"
    Paranoïa = "Paranoïa"
    Vampire = "Vampire"
    Scion = "Scion"
    Sombre = "Sombre"
    TalesFromTheLoop = "Tales from the loop"

class jdrPlatform(str, Enum):
#  Partie diffusée sur Twitch
# Partie jouée sur Roll20
# Partie jouée sur Discord
# Partie jouée sur Autre
    Twitch = " <:custom_emoji_name:434370263518412820> "
    Roll20 = " <:custom_emoji_name:493783713243725844> "
    Discord = " <:custom_emoji_name:434370093627998208> "
    Autre = ":space_invader:"

class jdrInput(BaseModel):
    minJoueurs: int = Field(..., title="Nombre minimum de joueur")
    maxJoueurs: int = Field(..., title="Nombre maximum de joueur")
    jdr_type: JdrType = Field(..., title="Type de JDR")
    jdr_date: str = Field(..., title="Date du JDR")
    jdr_horaire: str = Field(..., title="GMT du JDR")
    jdr_title: str = Field(..., title="Titre du JDR")
    jdr_length: str = Field(..., title="Durée du JDR")
    jdr_system: jdrLore | None = Field( title="Système du JDR")
    jdr_system_other: str | None = Field(title="Système du JDR (autre)")
    platform: list[jdrPlatform] | None = Field(title="Plateforme du JDR")
    jdr_pj: str = Field(..., title="PJ du JDR")
    jdr_details: str | None = Field(title="Détails du JDR")
    class Config:
        allow_population_by_field_name = True

@ router.post("/jdr", tags=["prez"])
async def createJdr(input: jdrInput, user: dict = Depends(check_token_dep),
                     discord_client: Client = Depends(get_discord_client)
                     ):
    # ---- formatage du message ---- (old code format)
    msg = ""
    """ Process form data to create the webhook payload. """
    #get path
    pwd = os.path.dirname(__file__)
    model =""
    with open(pwd + "/modele_fiche_planning.txt", 'r', encoding="utf8") as f:
        model += f.read()

    # determines which values to show for the number of players
    maxP = input.maxJoueurs
    minP = input.minJoueurs
    if maxP == minP:
        players = maxP
    else:
        players = f"{maxP} (min {minP})"

    info, reactions = model.split("[") # split the model into the info and the reactions (c'est de la merde mais bon)
    #if platform not exist
    # if input.platform is None:
    #     platform = []
    # else:
    #     platform = input.platform
    # print("raaaaaaa")
    # print(platform)
    # print("raaaaa")


    msg = info.format(
        type=input.jdr_type.value,
        title=input.jdr_title,
        date=input.jdr_date,
        horaire=input.jdr_horaire,
        players=players,
        length=input.jdr_length,
        pseudoMJ=f"<@{user['id']}> [{user['username']}]",  # TODO handle server nicknames
        system=input.jdr_system.value if input.jdr_system else input.jdr_system_other,
        # 0=oui, 1=non préférable, 2=non
        minors_allowed="oui" if input.jdr_pj == 0 else "non préférable" if input.jdr_pj == 1 else "non",
        platforms=", ".join([p for p in input.platform]) if input.platform else " ",
        details=input.jdr_details
    )
    print("msg: ", msg)

    # ---- envoi du message ---- (old and new code)
    print("channel id: ", JDR_CHANNEL)
    channel = discord_client.get_channel(JDR_CHANNEL)
    if not channel:
        print("channel not found")
        raise HTTPException(status_code=500, detail="Channel not found")
    res = await channel.send("", embed=discord.Embed(description=msg, type="rich").set_thumbnail(url=LOGO_URL))
    #if send worked, redirect to planning
    if res:
        # return RedirectResponse(url= URL_SITE_PLANNING+"?error=isPosted")
        return "ok"
    else:
        return RedirectResponse(url= URL_SITE_PLANNING+"?error=envoi")
