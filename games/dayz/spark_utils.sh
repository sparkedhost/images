#!/bin/bash

# General Utils

setup_nss_wrapper(){
    # Setup NSS Wrapper for use ($NSS_WRAPPER_PASSWD and $NSS_WRAPPER_GROUP have been set by the Dockerfile)
    export USER_ID=$(id -u)
    export GROUP_ID=$(id -g)
    envsubst < /passwd.template > ${NSS_WRAPPER_PASSWD}
    export LD_PRELOAD=/usr/lib/x86_64-linux-gnu/libnss_wrapper.so
}

mods_lowercase(){
    # Make mods lowercase, if specified
    if [[ ${MODS_LOWERCASE} == "1" ]]; then
        for modDir in $allMods; do
            [[ -d $modDir ]] && ModsLowercase $modDir
        done
    fi

}

rotate_log() {
    local log_path max_rotations base old new
    log_path="$1"
    max_rotations=5

    base="${log_path%.log}"

    # Rotate older logs
    for ((i=max_rotations-1; i>=1; i--)); do
        old="${base}.${i}.log"
        new="${base}.$((i + 1)).log"
        [[ -f "$old" ]] && cp -f "$old" "$new"
    done

    # Rotate current log
    [[ -f "$log_path" ]] && cp -f "$log_path" "${base}.1.log"

    rm -f "$log_path"
}

# Steam Utils

solve_mods(){
    if [[ -n ${MODS} ]] && [[ ${MODS} != *\; ]]; then # 
        MODS="${MODS};"
    fi

    if [[ -f ${MOD_FILE} ]] && [[ -n "$(cat ${MOD_FILE} | grep 'Created by DayZ Launcher')" ]]; then
        MODS+=$(cat ${MOD_FILE} | grep 'id=' | cut -d'=' -f3 | cut -d'"' -f1 | xargs printf '@%s;')
    fi

    MODS=$(RemoveDuplicates ${MODS}) # Remove duplicate mods from CLIENT_MODS, if present

    if [[ -n ${SERVERMODS} ]] && [[ ${SERVERMODS} != *\; ]]; then
        allMods="${SERVERMODS};"
    else
        allMods=${SERVERMODS}
    fi

    allMods+=$MODS # Add all client-side mods to the master mod list
    allMods=$(RemoveDuplicates ${allMods}) # Remove duplicate mods from allMods, if present
    allMods=$(echo $allMods | sed -e 's/;/ /g')
}

# Dayz Utils
add_to_dayzsa() {
    local atempts max_attempts response
    attempts=0
    max_attempts=3
    while [ $attempts -lt $max_attempts ]; do
        response=$(curl -s "https://dayzsalauncher.com/api/v1/query/$SERVER_IP:$QUERY_PORT")
        echo "DayZ SA Launcher registration attempt $((attempts + 1))"

        if echo "$response" | grep -q '"status":0'; then
            echo "✅ Server successfully registered with DayZ SA Launcher."
            return 0
        elif echo "$response" | grep -q '"error":"Timeout has occurred"'; then
            echo "⚠️ Timeout error, will retry..."
        else
            echo "⚠️ Unexpected response, will retry..."
        fi

        attempts=$((attempts + 1))
        sleep 10
    done

    echo "❌ Failed to register server with DayZ SA Launcher after $max_attempts attempts."
    return 1
}

startup_dayz(){
    GAME_ID=221100 
    solve_mods
    install_update_mods "$allMods"
    mods_lowercase
    # Add logFile if not present
    grep -q '^logFile' serverDZ.cfg || sed -i '/passwordAdmin = /a logFile = "server_console.log";' serverDZ.cfg
    grep -q '^steamQueryPort' serverDZ.cfg || sed -i '/passwordAdmin = /a steamQueryPort = '"${STEAM_QUERY_PORT}"';' serverDZ.cfg

    # Rotate the log so it doesn't bloat up
    rotate_log "serverprofile/server_console.log" 
    modifiedStartup=$(echo ${STARTUP} | sed -e 's/{{/${/g' -e 's/}}/}/g')

    echo -e "\033[1;33mcustomer@apollopanel:~\$\033[0m ${modifiedStartup}"
    setup_nss_wrapper
    if [ "${DAYZSA_AUTO_ADD}" = "1" ]; then
        (
        # Sleep to allow initialization without disturbing the log file
        sleep 30
        latest_rpt=$(ls -t serverprofile/*.RPT | head -1 )
        while true; do
            
            if grep -q "Initializing spawners" "$latest_rpt"; then
                echo "Server started, attempting to register with DayZ SA Launcher."
                add_to_dayzsa &
                break
            else
                echo "Waiting for server to start up before adding to DayzSA Launcher"
                sleep 20
            fi
        done
        ) &
    fi
    eval ${modifiedStartup}
}

# Arma 3 Utils
start_headless_clients(){
# Start Headless Clients if applicable
    if [[ ${HC_NUM} > 0 ]]; then
        echo -e "\n${GREEN}[STARTUP]:${NC} Starting ${CYAN}${HC_NUM}${NC} Headless Client(s)."
        for i in $(seq ${HC_NUM})
        do
            if [[ ${HC_HIDE} == "1" ]];
            then
                ./${SERVER_BINARY} -client -connect=127.0.0.1 -port=${SERVER_PORT} -password="${SERVER_PASSWORD}" -profiles=./serverprofile -bepath=./battleye -mod="${CLIENT_MODS}" ${STARTUP_PARAMS} > /dev/null 2>&1 &
            else
                ./${SERVER_BINARY} -client -connect=127.0.0.1 -port=${SERVER_PORT} -password="${SERVER_PASSWORD}" -profiles=./serverprofile -bepath=./battleye -mod="${CLIENT_MODS}" ${STARTUP_PARAMS} &
            fi
            echo -e "${GREEN}[STARTUP]:${CYAN} Headless Client $i${NC} launched."
        done
    fi
}
clear_hc_cache(){
    # Clear HC cache, if specified
    if [[ ${CLEAR_CACHE} == "1" ]]; then
        echo -e "\n${GREEN}[STARTUP]: ${CYAN}Clearing Headless Client profiles cache...${NC}"
        for profileDir in ./serverprofile/home/*
        do
            [ "$profileDir" = "./serverprofile/home/Player" ] && continue
            rm -rf $profileDir
        done
    fi
}
startup_arma3(){
    GAME_ID=107410
    LOG_FILE="/home/container/serverprofile/rpt/arma3server_$(date '+%m_%d_%Y_%H%M%S').rpt"
    mkdir -p /home/container/serverprofile/rpt
    clear_hc_cache
    solve_mods
    install_update_mods "$allMods"
    mods_lowercase
    start_headless_clients
    setup_nss_wrapper
    # Replace Startup Variables
    modifiedStartup=`eval echo $(echo ${STARTUP} | sed -e 's/{{/${/g' -e 's/}}/}/g')`

    # Start the Server
    if [[ "$STARTUP_PARAMS" == *"-noLogs"* ]]; then
        ${modifiedStartup}
    else
        ${modifiedStartup} 2>&1 | tee -a "$LOG_FILE"
    fi

    if [ $? -ne 0 ]; then
        echo -e "\n${RED}PTDL_CONTAINER_ERR: There was an error while attempting to run the start command.${NC}\n"
        exit 1
    fi


    if [[ "$STARTUP_PARAMS" == *"-noLogs"* ]]; then
        ${modifiedStartup}
    else
        ${modifiedStartup} 2>&1 | tee -a "$LOG_FILE"
    fi


}

startup_game(){
    case $SRCDS_APPID in
        233780)
            startup_arma3
        ;;
        223350)
            startup_dayz
        ;;
    esac
}