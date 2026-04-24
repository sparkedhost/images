sleep 1

dst_mod_fix() {
    [[ $SRCDS_APPID != 343050 ]] && return
    cp -f  steamcmd/linux64/steamclient.so  bin64/lib64/steamclient.so
}

cd /home/container

if [ "${AUTO_UPDATE}" == "1" ] && [ -n "${SRCDS_APPID}" ]; then 
    if [ -d "./steamcmd" ]; then
    ./steamcmd/steamcmd.sh +@sSteamCmdForcePlatformBitness 64 +force_install_dir /home/container +login anonymous +app_update ${SRCDS_APPID} $( [[ -z ${SRCDS_BETAID} ]] || printf %s "-beta ${SRCDS_BETAID}" ) $( [[ -z ${SRCDS_BETAPASS} ]] || printf %s "-betapassword ${SRCDS_BETAPASS}" ) $( [[ "${VALIDATE}" == "1" ]] && printf %s "validate" ) +quit
    fi
    if [ -d "./steam" ]; then
    ./steam/steamcmd.sh +@sSteamCmdForcePlatformBitness 64 +force_install_dir /home/container +login anonymous +app_update ${SRCDS_APPID} $( [[ -z ${SRCDS_BETAID} ]] || printf %s "-beta ${SRCDS_BETAID}" ) $( [[ -z ${SRCDS_BETAPASS} ]] || printf %s "-betapassword ${SRCDS_BETAPASS}" ) $( [[ "${VALIDATE}" == "1" ]] && printf %s "validate" ) +quit
    fi
elif [ "${AUTO_UPDATE}" != "1" ]; then
    echo -e "Not updating game server as auto update was set to 0. Starting Server"
fi

dst_mod_fix

MODIFIED_STARTUP=$(echo ${STARTUP} | sed -e 's/{{/${/g' -e 's/}}/}/g')

echo -e "\033[1;33mcustomer@apollopanel:~\$\033[0m ${MODIFIED_STARTUP}"

eval ${MODIFIED_STARTUP}
