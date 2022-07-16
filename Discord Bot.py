#Made by Formula#6704 aka ComoEsteban on v3rmillion

import requests;
import discord;
import json;
import random;

url_to_use = "https://your_website_here/folder_if_you_have_one"; #Change to your webhosting url
game_id_for_purchase = 1337; #Change to your game ID
developer_product_id = 1337; #Change to your dev product ID once you create it

discord_bot_token = "YOUR BOT TOKEN GOES HERE";

client = discord.Client();

transactions = {};

@client.event
async def on_connect():
  print("READY XD");

def is_buying(id):
  for i in transactions:
    data = transactions[i]
    if transactions[i]["user"] == id:
      return True,transactions[i];
  return False;

def exists(file_name):
  data = json.loads(requests.get(url_to_use+"main.php?condition=exists&file="+file_name).text);
  return data["exists"];

def create_file(name,extension):
  requests.get(url_to_use+"main.php?condition=create&name="+name+"&extension="+extension);
  return;

def update_asset_id(id):
  data = json.loads(requests.get(url_to_use+"main.php?condition=updateid&id="+str(id)).text);
  return data["old"];

if exists("asset_data.json") == False:
  create_file("asset_data",".json");
  update_asset_id(developer_product_id);

def has_purchased(id):
  data = json.loads(requests.get(url_to_use+"data.json").text)
  for i in data:
   if i["discord"] == id and i["complete"] == True and i["purchase id"] != None:
     return True;
  return False;

def end_transaction(id):
  for i in transactions:
    if transactions[i]["user"] == id:
      transactions.pop(i);
      return;
  return;

def generate_key():
  letters = ["a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z"];
  numbers = ["1","2","3","4","5","6","7","8","9","0"];
  key = "";
  for i in range(32):
    length = len(key)
    if length == 8 or length == 13 or length == 18 or length == 23:
      key = key+"-"
    if length == 0 or length == 9 or length == 12 or length == 13 or length == 17 or length == 22 or length == 30 or length == 31 or length == 32:
      key = key+letters[random.randint(0,len(letters)-1)];
    else:
      key = key+numbers[random.randint(0,len(numbers)-1)]
  return key;

@client.event
async def on_message(msg):
  if msg.content[0:4] == "$buy" and is_buying(msg.author.id) == False:
    purchase_array = {"user":msg.author.id,"robloxid":False,"completed":False};
    transactions[len(transactions)] = purchase_array;
    await msg.author.send("Please say your roblox user id with $id <userid>");
    return;

  if msg.content[0:4] == "$id ":
    id = int(msg.content[4:len(msg.content)]);
    buying,info = is_buying(msg.author.id);
    if buying == True:
      info["robloxid"] = id;
      requests.get(url_to_use+"/main.php?condition=newpurchase&discord="+str(msg.author.id)+"&userid="+str(info["robloxid"]));
      await msg.author.send("Now please join this game and purchase the dev product that is prompted on join. Afterward say $purchased");
      await msg.author.send("https://roblox.com/games/"+str(game_id_for_purchase))
    return;

  if msg.content == "$purchased" and is_buying(msg.author.id):
    buying,info = is_buying(msg.author.id);
    if buying != True or info["robloxid"] == False:
      return;
    if not has_purchased(msg.author.id):
      await msg.author.send("Payment Not Received");
      return;
    key = generate_key()
    url = url_to_use+"main.php?condition=purchasecomplete&discord="+str(msg.author.id)+"&key="+key
    print(url)
    requests.get(url_to_use+"main.php?condition=purchasecomplete&discord="+str(msg.author.id)+"&key="+key);
    end_transaction(msg.author.id);
    await msg.author.send("Payment Received");
    await msg.author.send(embed=discord.Embed(title="Thank You For Purchasing",description="Your whitelist key is "+key));
    return

  if msg.content == "$help" or msg.content == "$cmds":
    await msg.channel.send(embed=discord.Embed(title="Commands",description="$buy --> starts transaction\n$help --> gives help\n\nMade by Formula#6704 aka ComoEsteban on v3rmillion"))
    return;

  if msg.content == "$cancel" and is_buying(msg.author.id):
    end_transaction(msg.author.id);
    requests.get(url_to_use+"main.php?condition=cancelled&discord="+str(msg.author.id))
    await msg.author.send("Transaction cancelled, if you want to start a new one say $buy")
    return;

    
client.run(discord_bot_token);