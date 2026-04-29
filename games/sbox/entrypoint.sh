#!/bin/bash
source /spark-utils.sh
cd /home/container

function migrate() {
    echo "Migrating from old sbox-public directory to new sbox directory..."
    rm -rf /home/container/data /home/container/config
    mv ./sbox-public/game/data data
    mv ./sbox-public/game/config config
    rm -rf sbox-public .dotnet .local .cache .nuget .wget-hsts Steam
    
    SERVER_HOME=/home/container
    install_steamcmd
    ./steamcmd/steamcmd.sh +@sSteamCmdForcePlatformBitness 64 +force_install_dir /home/container +login anonymous +app_update ${SRCDS_APPID} $( [[ -z ${SRCDS_BETAID} ]] || printf %s "-beta ${SRCDS_BETAID}" ) $( [[ -z ${SRCDS_BETAPASS} ]] || printf %s "-betapassword ${SRCDS_BETAPASS}" ) $( [[ "${VALIDATE}" == "1" ]] && printf %s "validate" ) +quit
}

# Make internal Docker IP address available to processes.
export INTERNAL_IP=`ip route get 1 | awk '{print $(NF-2);exit}'`

if [ -d "./sbox-public" ]; then
    migrate
fi

if [ "${AUTO_UPDATE}" == "1" ] && [ -n "${SRCDS_APPID}" ]; then 
    if [ -d "./steamcmd" ]; then
    ./steamcmd/steamcmd.sh +@sSteamCmdForcePlatformBitness 64 +force_install_dir /home/container +login anonymous +app_update ${SRCDS_APPID} $( [[ -z ${SRCDS_BETAID} ]] || printf %s "-beta ${SRCDS_BETAID}" ) $( [[ -z ${SRCDS_BETAPASS} ]] || printf %s "-betapassword ${SRCDS_BETAPASS}" ) $( [[ "${VALIDATE}" == "1" ]] && printf %s "validate" ) +quit
    fi
    if [ -d "./steam" ]; then
    ./steam/steamcmd.sh +@sSteamCmdForcePlatformBitness 64 +force_install_dir /home/container +login anonymous +app_update ${SRCDS_APPID} $( [[ -z ${SRCDS_BETAID} ]] || printf %s "-beta ${SRCDS_BETAID}" ) $( [[ -z ${SRCDS_BETAPASS} ]] || printf %s "-betapassword ${SRCDS_BETAPASS}" ) $( [[ "${VALIDATE}" == "1" ]] && printf %s "validate" ) +quit
    fi
else
    echo -e "Not updating game server as auto update was set to 0. Starting Server"
fi

# Replace Startup variables.
MODIFIED_STARTUP=$(echo -e ${STARTUP} | sed -e 's/{{/${/g' -e 's/}}/}/g')
echo "customer@apollopanel:~# ${MODIFIED_STARTUP}"

# Run the Server.
eval ${MODIFIED_STARTUP}