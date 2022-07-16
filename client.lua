local key = "7rJhyZfp9hKZ"

local http_request = http_request
if syn then 
    http_request = syn.request
elseif SENTINEL_V2 then
    http_request = request 
end

local getservice = game.GetService
local httpservice = getservice(game, "HttpService")

local function http_request_get(url, headers) 
    return http_request({Url=url,Method="GET",Headers=headers or nil}).Body 
end

local function jsondecode(json)
	local jsonTable = {}
	 pcall(function() jsonTable = httpservice.JSONDecode(httpservice,json) end)
	return jsonTable
end

local chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'
local length = 50
local randomString = ''

charTable = {}
for c in chars:gmatch"." do
    table.insert(charTable, c)
end

for i = 1, length do
    randomString = randomString .. charTable[math.random(1, #charTable)]
end

local body = http_request_get('https://httpbin.org/get')
local decoded = jsondecode(body)
local hwid_list = {"Syn-Fingerprint", "Exploit-Guid", "Proto-User-Identifier", "Sentinel-Fingerprint"};
local hwid = "";

for i, v in next, hwid_list do
    if decoded.headers[v] then
        hwid = decoded.headers[v];
        break
    end
end

local random = randomString

local data = jsondecode(http_request_get("https://whitelist.com/server.php?key=".. key .."&random="..random))

if data.Key == key then
    if data.Blacklist == "False" then
        if data.Hwid == Hwid or "Unknown" then
            if data.random == random then
                if data.Hwid == "Unknown" then
                    -- update hwid
                    print("Whitelist !!!")
                    http_request_get("https://whitelist.com/changehwid.php?key=".. key .."&hwid="..hwid)
                    print("Update Hwid")
                else
                    -- no update hwid
                    print("Whitelist !!!")
                end
            else
                warn("Invalid Random")
            end
        else
            warn("Invalid Hwid")
        end
    else
        warn("Blacklist Reason : "..data.Reason)
    end
else
    warn("Invalid Key")
end