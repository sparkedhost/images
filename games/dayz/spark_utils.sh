#!/bin/bash

# General Utils

setup_nss_wrapper(){
    # Setup NSS Wrapper for use ($NSS_WRAPPER_PASSWD and $NSS_WRAPPER_GROUP have been set by the Dockerfile)
    export USER_ID=$(id -u)
    export GROUP_ID=$(id -g)
    envsubst < /passwd.template > ${NSS_WRAPPER_PASSWD}
    export LD_PRELOAD=/usr/lib/x86_64-linux-gnu/libnss_wrapper.so
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
RunSteamCMD() { #[Input: int server=0 mod=1; int id]
    local steamcmd_log steamcmd_dir game_id workshop_dir updateAttempt  steamcmdExitCode 
    workshop_dir="./Steam/steamapps/workshop"
    steamcmd_dir="$SERVER_HOME/steamcmd"
    steamcmd_log="${steamcmd_dir}/steamcmd.log" 
    # Clear previous SteamCMD log
    if [[ -f "${steamcmd_log}" ]]; then
        rm -f "${steamcmd_log:?}"
    fi
    updateAttempt=0
    while (( $updateAttempt < $STEAMCMD_ATTEMPTS )); do # Loop for specified number of attempts
        # Increment attempt counter
        updateAttempt=$((updateAttempt+1))

        if (( $updateAttempt > 1 )); then # Notify if not first attempt
            echo -e "Re-Attempting download/update in 3 seconds... (Attempt ${updateAttempt} of ${STEAMCMD_ATTEMPTS})"
            sleep 3
        fi

        # Check if updating server or mod
        if [[ $1 == 0 ]]; then # Server
            ${steamcmd_dir}/steamcmd.sh +force_install_dir /home/container "+login \"${STEAM_USER}\" \"${STEAM_PASS}\"" +app_update $2 $extraFlags $validateServer +quit | tee -a "${steamcmd_log}"
        else # Mod
            ${steamcmd_dir}/steamcmd.sh "+login \"${STEAM_USER}\" \"${STEAM_PASS}\"" +workshop_download_item $GAME_ID $2 +quit | tee -a "${steamcmd_log}"
        fi

        # Error checking for SteamCMD
        steamcmdExitCode=${PIPESTATUS[0]}
        if [[ -n $(grep -i "error\|failed" "${steamcmd_log}" | grep -iv "setlocal\|SDL\|thread") ]]; then # Catch errors (ignore setlocale, SDL, and thread priority warnings)
            # Soft errors
            if [[ -n $(grep -i "Timeout downloading item" "${steamcmd_log}") ]]; then # Mod download timeout
                echo -e "[UPDATE]: Timeout downloading Steam Workshop mod: \"${modName}\" (${2})"
                echo -e "(This is expected for particularly large mods)"
            elif [[ -n $(grep -i "0x402\|0x6\|0x602" "${steamcmd_log}") ]]; then # Connection issue with Steam
                echo -e "[UPDATE]: Connection issue with Steam servers."
                echo -e "(Steam servers may currently be down, or a connection cannot be made reliably)"
            # Hard errors
            elif [[ -n $(grep -i "Password check for AppId" "${steamcmd_log}") ]]; then # Incorrect beta branch password
                echo -e "[UPDATE]: Incorrect password given for beta branch. Skipping download..."
                echo -e "(Check your \"[ADVANCED] EXTRA FLAGS FOR STEAMCMD\" startup parameter)"
                break
            # Fatal errors
            elif [[ -n $(grep -i "Invalid Password\|two-factor\|No subscription" "${steamcmd_log}") ]]; then # Wrong username/password, Steam Guard is turned on, or host is using anonymous account
                echo -e "[UPDATE]: Cannot login to Steam - Improperly configured account and/or credentials"
                echo -e "Please contact your administrator/host and give them the following message:"
                echo -e "Your Egg, or your client's server, is not configured with valid Steam credentials."
                echo -e "Either the username/password is wrong, or Steam Guard is not properly configuredaccording to this egg's documentation/README."
                exit 1
            elif [[ -n $(grep -i "Download item" "${steamcmd_log}") ]]; then # Steam account does not own base game for mod downloads, or unknown
                echo -e "[UPDATE]: Cannot download mod - Download failed"
                echo -e "While unknown, this error is likely due to your host's Steam account not owning the base game."
                echo -e "(Please contact your administrator/host if this issue persists)"
                exit 1
            elif [[ -n $(grep -i "0x202\|0x212" "${steamcmd_log}") ]]; then # Not enough disk space
                echo -e "[UPDATE]: Unable to complete download - Not enough storage"
                echo -e "You have run out of your allotted disk space."
                echo -e "Please contact your administrator/host for potential storage upgrades."
                exit 1
            elif [[ -n $(grep -i "0x606" "${modifiedStartup_log}") ]]; then # Disk write failure
                echo -e "[UPDATE]: Unable to complete download - Disk write failure"
                echo -e "This is normally caused by directory permissions issues,but could be a more serious hardware issue."
                echo -e "(Please contact your administrator/host if this issue persists)"
                exit 1
            else # Unknown caught error
                echo -e "[UPDATE]: An unknown error has occurred with SteamCMD. Skipping download..."
                echo -e "(Please contact your administrator/host if this issue persists)"
                break
            fi
        elif [[ $steamcmdExitCode != 0 ]]; then # Unknown fatal error
            echo -e "[UPDATE]: SteamCMD has crashed for an unknown reason! (Exit code: ${steamcmdExitCode})"
            echo -e "(Please contact your administrator/host for support)"
            cp -r /tmp/dumps /home/container/dumps
            exit $steamcmdExitCode
        else # Success!
            if [[ $1 == 0 ]]; then # Server
                echo -e "[UPDATE]: Game server is up to date!"
            else # Mod -- this will work for arma3 and dayz, for others we can add cases
                find "${workshop_dir}/content/${GAME_ID}/$2" -name "*.bikey" -type f -exec cp -t "keys" {} +
                rm -rf "./@$2"
                mkdir "./@$2"
                cp -al "${workshop_dir}/content/${GAME_ID}/$2/"* "@$2/"
                # Make the mods contents all lowercase
                ModsLowercase @$2
                echo -e "[UPDATE]: Mod download/update successful!"
            fi
            break
        fi
        if (( $updateAttempt == $STEAMCMD_ATTEMPTS )); then # Notify if failed last attempt
            if [[ $1 == 0 ]]; then # Server
                echo -e "Final attempt made! Unable to complete game server update. Skipping..."
                echo -e "(Please try again at a later time)"
                sleep 3
            else # Mod
                echo -e "Final attempt made! Unable to complete mod download/update. Skipping..."
                echo -e "(You may try again later, or manually upload this mod to your server via SFTP)"c
                sleep 3
            fi
        fi
    done
}

solve_mods(){
    if [[ -n ${MODS} ]] && [[ ${MODS} != *\; ]]; then # 
        MODS="${MODS};"
    fi

    if [[ -f ${MOD_FILE} ]] && [[ -n "$(cat ${MOD_FILE} | grep 'ModContainer')" ]]; then
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

install_update_mods() { #[Input: str list of mods]
    local latest_update update_available mod_missing
    [[ -z $1 ]] && return
    [[ $1 -eq 0 ]] && return
    echo -e "[MOD_INSTALLATION]: Checking for missing mods"
    for modID in $(echo "$1" | sed -e 's/@//g'); do
        if [[ $modID =~ ^[0-9]+$ ]]; then # Only check mods that are in ID-form
            [[ -d @$modID ]] && rmdir "@$modID" 2>/dev/null
            mod_missing=false
            if [[ ! -d @$modID ]] || [[ -z "$(find "@$modID" -mindepth 1 -print -quit 2>/dev/null)" ]]; then
                mod_missing=true
                echo -e "[MOD_INSTALLATION]: Downloading missing Mod: \"${modName}\" (${modID})"
            fi

            update_available=false
            if ! $mod_missing && check_mod_update "$modID"; then
                update_available=true
                echo -e "[MOD_INSTALLATION]: Mod update found for: \"${modName}\" (${modID})"
            fi

            if $mod_missing || $update_available; then
                modName=$(curl -sL https://steamcommunity.com/sharedfiles/filedetails/changelog/$modID | grep 'workshopItemTitle' | cut -d'>' -f2 | cut -d'<' -f1)
                if [[ -z $modName ]]; then # Set default name if unavailable
                    modName="[NAME UNAVAILABLE]"
                fi
                echo -e "[MOD_INSTALLATION]: Attempting mod download via SteamCMD..."
                RunSteamCMD 1 $modID
            fi
        fi
    done
}

install_steamcmd(){
    mkdir -p $SERVER_HOME/steamcmd $SERVER_HOME/steamapps
    curl -sSL -o steamcmd.tar.gz https://steamcdn-a.akamaihd.net/client/installer/steamcmd_linux.tar.gz
    tar -xzvf steamcmd.tar.gz -C $SERVER_HOME/steamcmd
    cd $SERVER_HOME/steamcmd
    ./steamcmd.sh +quit
    mkdir -p $SERVER_HOME/.steam/sdk{32,64}
    cp -v linux32/steamclient.so $SERVER_HOME/.steam/sdk32/steamclient.so
    cp -v linux64/steamclient.so $SERVER_HOME/.steam/sdk64/steamclient.so
}



check_mod_update(){
    local last_local_timestamp last_remote_timestamp remote_modified remote_url mod_id=$1
    [[ $MOD_AUTO_UPDATE == "0" ]] && return 0
    echo -e "[MOD_INSTALLATION]: Checking for mod update for $mod_id"
    last_remote_timestamp=$(curl -sL https://steamcommunity.com/sharedfiles/filedetails/changelog/$mod_id | grep '<p id=' | head -1 | cut -d'"' -f2)

    last_local_timestamp=$(find "@${mod_id}" -mindepth 1 -print -quit 2>/dev/null | xargs stat -c%Y)

    if [[ -z $last_local_timestamp ]]; then
        return 0
    fi

    if [[ -n $last_remote_timestamp ]] && [[ $last_local_timestamp -lt $last_remote_timestamp ]]; then
        return 0
    else
        return 1
    fi
}



# Removes duplicate items from a semicolon delimited string
RemoveDuplicates() { #[Input: str - Output: printf of new str]
    if [[ -n $1 ]]; then # If nothing to compare, skip to prevent extra semicolon being returned
        echo $1 | sed -e 's/;/\n/g' | sort -u | xargs printf '%s;'
    fi
}

# Takes a directory (string) as input, and recursively makes all files & folders lowercase.
ModsLowercase() {
    local src dst mod_path=$1
    echo -e "Making mod ${mod_path} files/folders lowercase..."
    while IFS= read -r -d '' src; do
        dst="$(dirname "${src}")/$(basename "${src}" | tr '[A-Z]' '[a-z]')"
        if [ "${src}" != "${dst}" ]; then
            [ ! -e "${dst}" ] && mv -T "${src}" "${dst}"
        fi
    done < <(find "./${mod_path}" -depth -print0)
}

mods_lowercase(){
    # Make mods lowercase, if specified
    if [[ ${MODS_LOWERCASE} == "1" ]]; then
        for modDir in $allMods; do
            [[ -d $modDir ]] && ModsLowercase $modDir
        done
    fi

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