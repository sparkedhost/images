sleep 1

cd /home/container

if [[ ! "${ANIV_COMMUNITY_OWNER_STEAM_ID:-}" =~ ^[0-9]{17}$ ]]; then
    echo "Set Community Owner Steam ID to your personal 17-digit SteamID64 before starting the server." >&2
    exit 1
fi

if [ "${AUTO_UPDATE}" == "1" ]; then 
    if [ -d "./steamcmd" ]; then
    ./steamcmd/steamcmd.sh +@sSteamCmdForcePlatformBitness 64 +force_install_dir /home/container +login anonymous +app_update ${SRCDS_APPID} $( [[ -z ${SRCDS_BETAID} ]] || printf %s "-beta ${SRCDS_BETAID}" ) $( [[ -z ${SRCDS_BETAPASS} ]] || printf %s "-betapassword ${SRCDS_BETAPASS}" ) $( [[ -z ${VALIDATE} ]] || printf %s "validate" ) +quit
    fi
    if [ -d "./steam" ]; then
    ./steam/steamcmd.sh +@sSteamCmdForcePlatformBitness 64 +force_install_dir /home/container +login anonymous +app_update ${SRCDS_APPID} $( [[ -z ${SRCDS_BETAID} ]] || printf %s "-beta ${SRCDS_BETAID}" ) $( [[ -z ${SRCDS_BETAPASS} ]] || printf %s "-betapassword ${SRCDS_BETAPASS}" ) $( [[ -z ${VALIDATE} ]] || printf %s "validate" ) +quit
    fi
else
    echo -e "Not updating game server as auto update was set to 0. Starting Server"
fi

if [ ! -x ./aniv_server.x86_64 ]; then
    echo "ANEURISM IV server executable is missing; check the SteamCMD installation." >&2
    exit 1
fi

export ANIV_MAP="${MAP}"
export ANIV_MAX_PLAYERS="${MAX_PLAYERS}"
export ANIV_SERVER_PASSWORD="${PASSWORD}"

config_dir="${ANIV_DATA_ROOT:-${HOME}/.config/unity3d/Vellocet/ANEURISM IV}"
mkdir -p "${config_dir}"
if [ ! -e "${config_dir}/customserverrules.cfg" ]; then
    : > "${config_dir}/customserverrules.cfg"
fi

MODIFIED_STARTUP=$(echo ${STARTUP} | sed -e 's/{{/${/g' -e 's/}}/}/g')

echo -e "\033[1;33mcustomer@apollopanel:~\$\033[0m ${MODIFIED_STARTUP}"

exec /bin/bash -c "${MODIFIED_STARTUP}"
