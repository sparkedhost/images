#!/bin/bash
sleep 1

cd /home/container

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

ulimit -n 2048
export LD_LIBRARY_PATH=$LD_LIBRARY_PATH:$HOME/Unturned_Headless_Data/Plugins/x86_64/

MODIFIED_STARTUP=$(eval echo $(echo ${STARTUP} | sed -e 's/{{/${/g' -e 's/}}/}/g'))
# Print startup command to console
echo -e "\033[1;33mcustomer@modernhosting:~\$\033[0m ${MODIFIED_STARTUP}"

${MODIFIED_STARTUP}
