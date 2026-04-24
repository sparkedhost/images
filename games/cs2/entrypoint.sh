sleep 1

cd /home/container

if [ "${AUTO_UPDATE}" == "1" ]; then 
    ./steamcmd/steamcmd.sh +@sSteamCmdForcePlatformBitness 64 +force_install_dir /home/container +login anonymous +app_update ${SRCDS_APPID} $( [[ -z ${SRCDS_BETAID} ]] || printf %s "-beta ${SRCDS_BETAID}" ) $( [[ -z ${SRCDS_BETAPASS} ]] || printf %s "-betapassword ${SRCDS_BETAPASS}" ) $( [[ -z ${VALIDATE} ]] || printf %s "validate" ) +quit
else
    echo -e "Not updating game server as auto update was set to 0. Starting Server"
fi

if [ "${METAMOD}" == "1" ]; then
echo "Installing/Updating Metamod..."

curl -sL https://github.com/alliedmodders/metamod-source/releases/download/2.0.0.1396/mmsource-2.0.0-git1396-linux.tar.gz -o metamod.tar.gz
tar -xzf metamod.tar.gz -C /home/container/game/csgo
rm metamod.tar.gz

if ! grep -q "metamod" "/home/container/game/csgo/gameinfo.gi"; then
    sed -i '/Game_LowViolence/a\ \ \ \ \ \ \ \ \ \ \ \ Game\t\t\t csgo/addons/metamod' /home/container/game/csgo/gameinfo.gi
fi

else
    echo "Not installing/updating Metamod was set to 0."
fi

if [ "${COUNTERSTRIKESHARP}" == "1" ]; then
echo "Installing/Updating CounterStrikeSharp..."

CSS_URL=$(curl -s https://api.github.com/repos/roflmuffin/CounterStrikeSharp/releases/latest \
  | grep browser_download_url \
  | grep counterstrikesharp-linux \
  | cut -d '"' -f 4)

curl -L "$CSS_URL" -o cssharp.zip

unzip -o cssharp.zip -d /home/container/game/csgo

rm cssharp.zip
else
    echo "Not installing/updating CounterStrikeSharp was set to 0."
fi 

MODIFIED_STARTUP=$(echo ${STARTUP} | sed -e 's/{{/${/g' -e 's/}}/}/g')

echo -e "\033[1;33mcustomer@apollopanel:~\$\033[0m ${MODIFIED_STARTUP}"

eval ${MODIFIED_STARTUP}