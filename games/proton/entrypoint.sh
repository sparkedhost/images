sleep 1

source /spark-utils.sh

if [[ -f "/usr/local/bin/proton" ]] && ! setup_proton_compatibility; then
    exit 0
fi

cd /home/container || exit 1

if [ "${AUTO_UPDATE}" == "1" ]; then
    STEAM_LOGIN="+login anonymous"
    if [[ -n "${STEAM_USER:-}" && -n "${STEAM_PASS:-}" ]]; then
        STEAM_LOGIN="+login \"${STEAM_USER}\" \"${STEAM_PASS}\""
    fi

    ./steamcmd/steamcmd.sh +force_install_dir /home/container "${STEAM_LOGIN}" $( [[ "${WINDOWS_INSTALL}" == "1" ]] && printf %s '+@sSteamCmdForcePlatformType windows' ) +app_update ${SRCDS_APPID} $( [[ -z ${SRCDS_BETAID} ]] || printf %s "-beta ${SRCDS_BETAID}" ) $( [[ -z ${SRCDS_BETAPASS} ]] || printf %s "-betapassword ${SRCDS_BETAPASS}" ) $( [[ -z ${VALIDATE} ]] || printf %s "validate" ) +quit
else
    echo -e "Not updating game server as auto update was set to 0. Starting Server"
fi

game_pre_startup
startup_game
