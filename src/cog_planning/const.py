from enum import IntEnum


class MinorsAllowed(IntEnum):
    YES = 0
    NO = 1
    PREFFERED = 2
    NOT_RECOMMANDED = 3


minorsAllowed_to_str = {
    MinorsAllowed.YES: "oui",
    MinorsAllowed.NO: "non",
    MinorsAllowed.PREFFERED: "préférable",
    MinorsAllowed.NOT_RECOMMANDED: "non recommandé"
}


class ScenarioType(IntEnum):
    INITIATION = 0
    ONESHOT = 1
    SCENARIO = 2
    CAMPAIGN = 3


scenarioType_to_str = {
    ScenarioType.INITIATION: "Initiation",
    ScenarioType.ONESHOT: "OneShot",
    ScenarioType.SCENARIO: "Scénario",
    ScenarioType.CAMPAIGN: "Campagne"
}

emoji_to_platform = {
    '<:custom_emoji_name:434370263518412820>': 'twitch',
    '<:custom_emoji_name:493783713243725844>': 'roll20',
    '<:custom_emoji_name:434370093627998208>': 'discord',
    ':space_invader:': 'autre'
}

platform_to_emoji = {v: k for k, v in emoji_to_platform.items()}

EDIT_MODE_PROMPT = 0
EDIT_MODE_CONTENT = 1
EDIT_MODE_FINISHED = 2
