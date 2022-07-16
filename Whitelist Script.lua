getgenv().key = "your key goes here"

local url_to_use = "https://www.linktoloadstring.click/" -- Put your url here (if it's in a folder make sure u put /YOURFOLDERNAME/ at the end)

local random_number = math.random(500,2000)
local current_time = os.time()
local response = game:HttpGet(url_to_use.."main.php?condition=checkwhitelist&key="..key.."&time="..tostring(current_time).."&n="..tostring(random_number))

if tonumber(response) == current_time*random_number then
    game:GetService("Players").LocalPlayer:Kick("Invalid Key")
    return
end
if tonumber(response) ~= current_time-random_number then
    game:GetService("Players").LocalPlayer:Kick("You must wait "..response.." seconds before using on a new ip!")
    return
end

loadstring(game:HttpGet("https://raw.githubusercontent.com/ScriptsByJoel/Galaxy-Hub-Scripts/main/Hub-Loadstring-Places",true))()
