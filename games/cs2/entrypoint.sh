sleep 1

cd /home/container

if [ "${AUTO_UPDATE}" == "1" ]; then 
    ./steamcmd/steamcmd.sh +@sSteamCmdForcePlatformBitness 64 +force_install_dir /home/container +login anonymous +app_update ${SRCDS_APPID} $( [[ -z ${SRCDS_BETAID} ]] || printf %s "-beta ${SRCDS_BETAID}" ) $( [[ -z ${SRCDS_BETAPASS} ]] || printf %s "-betapassword ${SRCDS_BETAPASS}" ) $( [[ -z ${VALIDATE} ]] || printf %s "validate" ) +quit
else
    echo -e "Not updating game server as auto update was set to 0. Starting Server"
fi

MODIFIED_STARTUP=$(echo ${STARTUP} | sed -e 's/{{/${/g' -e 's/}}/}/g')

echo -e "\033[1;33mcustomer@sparkedhost:~\$\033[0m ${MODIFIED_STARTUP}"

eval ${MODIFIED_STARTUP}
