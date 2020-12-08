# bot.py
import os
import discord
import requests
import random
import string
from dotenv import load_dotenv

def randomString(stringLength=26):
    letters = string.ascii_lowercase
    return ''.join(random.choice(letters) for i in range(stringLength))

def isOfficer(member):
    roles = member.roles
    for role in roles:
        if role.id == int(os.getenv('DISCORD_OFFICER_ROLE_ID')):
            return True
    return False

async def nyalothaLootCommand(message):
    channel = message.channel
    channel.typing()
    await message.delete()
    await channel.send('Add a reaction (:loot_yes:) to the bosses where you need loot')
    bosses = ['Shriekwing', 'Huntsman Altimor', 'Hungering Destroyer', 'Lady Inerva Darkvein', 'Artificer Xy\'Mox', 'Sun King\'s Salvation', 'Council of Blood', 'Sludgefist', 'Stone Legion Generals', 'Sire Denathrius']
    for boss in bosses:
        newMessage = await channel.send(boss)
        await newMessage.add_reaction(emoji=':loot_yes:678637511442038823')

load_dotenv()
TOKEN = os.getenv('DISCORD_TOKEN')
API_KEY = os.getenv('API_KEY')

client = discord.Client()

@client.event
async def on_ready():
    print(f'{client.user} has connected to Discord!')

@client.event
async def on_message(message):
    if message.author == client.user:
        return

    if isOfficer(message.author) and message.content.lower() == "!loot":
        await nyalothaLootCommand(message)
        return

    if message.channel.id != int(os.getenv('DISCORD_SPAM_CHANNEL_ID')):
        return

    if message.content.lower() == '!mudkip':
        token = randomString()
        id = message.author.name + '#' + message.author.discriminator

        url = 'https://amusedtodeath.eu/api/bot/'
        payload = {'token': token, 'discord': id}
        headers = {'x-api-key': API_KEY}

        requests.post(url, data = payload, headers = headers)

        print(f'{id} was just serviced!')
        await message.add_reaction('\U00002705')
        await message.author.create_dm()
        await message.author.dm_channel.send(
            f'Hi, {id}. \nYou are on the list of approved users! \nLog in with this link: https://amusedtodeath.eu/api/auth/?token={token} \n\nThe link expires in 30 days, if it has expired by the time you read this, just ask for another link.'
        )

client.run(TOKEN)