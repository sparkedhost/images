#!/bin/bash
sleep 1

cd /home/container

# Fixing Steam updating.
if [ -d "./steamcmd" ]; then
    mv steamcmd steam
fi

if [ "${GAME_AUTOUPDATE}" == "1" ]; then
    ./steam/steamcmd.sh +@sSteamCmdForcePlatformBitness 64 +force_install_dir /home/container +login anonymous +app_update ${SRCDS_APPID} $( [[ -z ${SRCDS_BETAID} ]] || printf %s "-beta ${SRCDS_BETAID}" ) +quit
else
    echo -e "Not updating game server as auto update is off. Starting Server"
fi

if [ "${OPENMOD_AUTOUPDATE}" == "1" ]; then
    curl -s https://api.github.com/repos/openmod/OpenMod/releases/latest | jq -r ".assets[] | select(.name | contains(\"OpenMod.Unturned.Module\")) | .browser_download_url" | wget -i -
	unzip -o -q OpenMod.Unturned.Module*.zip -d Modules && rm OpenMod.Unturned.Module*.zip
fi


if [ "${ROCKET_AUTOUPDATE}" == "1" ]; then
    cd /home/container
    cp -r Extras/Rocket.Unturned Modules/
fi

if [ "${USCRIPT_AUTOUPDATE}" == "1" ]; then
    wget https://s3-modpacks.sparkedhost.us/uScript.Unturned.zip
    cd /home/container
    unzip -o -q uScript.Unturned.zip && rm uScript.Unturned.zip
fi

if [ "${UPANEL}" == "Vanilla" ]; then
    cd /home/container/Modules
    mkdir uPanelLoader
    cd uPanelLoader
    wget https://upanel.one/api/plugin/loader/Module
    unzip -o Module
    rm -r Module
    cd /home/container
fi

if [ "${UPANEL}" == "RocketMod" ]; then
    cd /home/container/Servers/unturned/Rocket
    wget https://upanel.one/api/plugin/loader/Rocket
    unzip -o Rocket
    mv uPanelLoader.dll Plugins
    rm -r Rocket
    cd /home/container
fi

if [ "${UPANEL}" == "OpenMod" ]; then
    cd /home/container/Servers/unturned/OpenMod/plugins
    wget https://upanel.one/api/plugin/loader/Openmod
    unzip -o Openmod
    mv Libraries/* .
    rm -r Openmod
    rm -r Libraries
    cd /home/container
fi

mkdir -p Unturned_Headless_Data/Plugins/x86_64
cp -f steam/linux64/steamclient.so Unturned_Headless_Data/Plugins/x86_64/steamclient.so

ulimit -n 2048
export LD_LIBRARY_PATH=$LD_LIBRARY_PATH:$HOME/Unturned_Headless_Data/Plugins/x86_64/

MODIFIED_STARTUP=$(eval echo $(echo ${STARTUP} | sed -e 's/{{/${/g' -e 's/}}/}/g'))
# Print startup command to console
echo -e "\033[1;33mcustomer@apollopanel:~\$\033[0m ${MODIFIED_STARTUP}"

${MODIFIED_STARTUP}
