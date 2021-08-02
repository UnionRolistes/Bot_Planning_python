from urpy import lcl

on_join = lcl("🎉 Congratulations ! {user} has joined the game !")
on_leave = lcl("😭 Despair and loneliness... {user} has left us. {user} is a meanie.")
on_edit_without_reply = lcl("You must answer to a message in order to edit it !")
on_edit_wrong_channel = lcl("You cannot edit in this channel !")
on_edit_start = lcl("Here we go ! Join me in your DMs.")
on_edit_not_editable = lcl("This message is not editable.")
on_edit_not_mj = lcl("You are not the owner of this announce ! Thus, you cannot edit it.")
on_edit_prompt = lcl("\|~ Whichs pieces of information do you wish to edit ? (**$done** to validate, **$cancel** to undo) ~|")
on_edit_unrecognised = lcl("\|~ Unrecognised information. Please try again. (**$done** to validate, **$cancel** to undo) ~|")
on_edit_content_prompt = lcl("\|~ New content for {info} ? ~|")
on_edit_invalid = ""
on_edit_success = lcl("\|~ Congratulations, the message has been successfully edited. ~|")
on_edit_cancel = lcl("\|~ Canceled. ~|")

jdr_brief = lcl("Sends a link to create a game")
jdr_help = jdr_brief
on_jdr = lcl("A link has been sent into your DMs to create a new event !")
on_jdr_link = lcl("Here is the link :\n{link}")
on_jdr_dm_channel = lcl("This command is only usable in a server!")
on_jdr_channel_not_found = lcl("*Impossible.* The ** {channel} ** channel does not exist.")
on_jdr_webhook_not_found = lcl("*Impossible.* The ** {channel} ** channel does not have a webhook.")

cal_brief = lcl("Sends a link to the calendar")
cal_help = cal_brief
on_cal = lcl("The link has been sent into your DMs to see the calendar !")
on_cal_link = on_jdr_link # Si on veut personnaliser : = lcl("Here is the link :\n{link}")
on_cal_dm_channel = on_jdr_dm_channel # Si on veut personnaliser : = lcl("This command is only usable in a server!")

on_permission_error = lcl("Error, the bot doesn't have the required permissions. Contact the admin.")
