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
    if message.channel.id not in [692040942722482186, 433559219841531905]: #whitelist 692040942722482186
        return

    if message.content == '!mudkip':
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
            f'Hi, {id}. \nYou are on the list of approved users! \nTry logging in with this link: https://amusedtodeath.eu/api/auth/?token={token} \n\nThe link expires in 10 minutes, if it has expired by the time you read this, just ask for another link.'
        )

client.run(TOKEN)