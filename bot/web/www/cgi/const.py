from enum import IntEnum


class MinorsAllowed(IntEnum):
    YES = 0
    NOT_PREFERABLE = 1
    NO = 2
    


minorsAllowed_to_str = {
    MinorsAllowed.YES: "oui",
    MinorsAllowed.NOT_PREFERABLE: "non préférable",
    MinorsAllowed.NO: "non"
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