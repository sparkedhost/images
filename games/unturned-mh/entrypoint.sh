#!/bin/bash
sleep 1

cd /home/container

# Fix SteamCMD Warnings
if [ ! -f "./steam/linux32/steamservice.so" ]; then
    cd /home/container/steam/linux32
    cp steamclient.so steamservice.so
    cp steamclient.so libSDL3.so.0
    echo "SteamCMD Fix Deployed"
    cd /home/container
fi

if [ "${GAME_AUTOUPDATE}" != "0" ]; then
    ./steam/steamcmd.sh +@sSteamCmdForcePlatformBitness 64 +login anonymous +force_install_dir /home/container +app_update 1110390 +quit
else
    echo -e "Not updating game server as auto update is off. Starting Server"
fi

if [ "${ROCKET_AUTOUPDATE}" == "1" ]; then
    cp -r Extras/Rocket.Unturned Modules/
fi

mkdir -p Unturned_Headless_Data/Plugins/x86_64
cp -f steam/linux64/steamclient.so Unturned_Headless_Data/Plugins/x86_64/steamclient.so

if [ "${UPANEL}" == "Vanilla" ]; then
    cd /home/container/Modules
    mkdir uPanelLoader
    cd uPanelLoader
    wget https://upanel.one/api/data/loader/Module
    unzip -o Module
    rm -r Module
    cd /home/container
fi

if [ "${UPANEL}" == "RocketMod" ]; then
    cd /home/container/Servers/unturned/Rocket
    wget https://upanel.one/api/data/loader/Rocket
    unzip -o Rocket
    cp uPanelLoader.dll /Plugins/
    rm -r Rocket
    rm -r uPanelLoader.dll
    cd /home/container
fi

if [ "${UPANEL}" == "OpenMod" ]; then
    cd /home/container/Servers/unturned/OpenMod/plugins
    wget https://upanel.one/api/data/loader/Openmod
    unzip -o Openmod
    mv Libraries/* .
    rm -r Openmod
    rm -r Libraries
    cd /home/container
fi

ulimit -n 2048
export LD_LIBRARY_PATH=$LD_LIBRARY_PATH:$HOME/Unturned_Headless_Data/Plugins/x86_64/

MODIFIED_STARTUP=$(eval echo $(echo ${STARTUP} | sed -e 's/{{/${/g' -e 's/}}/}/g'))
# Print startup command to console
echo -e "\033[1;33mcustomer@modernhosting:~\$\033[0m ${MODIFIED_STARTUP}"

${MODIFIED_STARTUP}
