from enum import Enum
import json
from fastapi import APIRouter, Header, HTTPException, Depends
from fastapi.security import HTTPBearer
from pydantic import BaseModel, Field
from ..base._discord import get_discord_client  # in base api repo
import requests
import re
from dotenv import load_dotenv
import os
from discord import Client

load_dotenv()
PREZ_CHANNEL = int(os.getenv("PLANING_CHANNEL"))
