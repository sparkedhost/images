#!/bin/bash

sleep 1

# Switch to the container's working directory
cd /home/container || exit 1

ZIP_FILE="game${SRCDS_APPID}.zip"
REMOTE_URL="https://modpack-cdn.sparkedhost.us/games/game${SRCDS_APPID}.zip"

if [ "${AUTO_UPDATE}" == "1" ]; then
    if [ -f "${ZIP_FILE}" ]; then
        REMOTE_MODIFIED=$(curl -sI "${REMOTE_URL}" | grep -i "Last-Modified" | sed 's/Last-Modified: //I' | xargs)
        REMOTE_MODIFIED_NORMALIZED=$(date -d "${REMOTE_MODIFIED}" "+%Y-%m-%d %H:%M:%S")
        LOCAL_MODIFIED=$(stat -c %y "${ZIP_FILE}" | cut -d '.' -f1)

        if [[ "${REMOTE_MODIFIED_NORMALIZED}" == "${LOCAL_MODIFIED}" ]]; then
            echo -e "The server is already up to date. No update needed."
        else
            echo -e "The server is outdated. Updating now!"
            wget -q -O "${ZIP_FILE}" "${REMOTE_URL}"
            if ! unzip -t "${ZIP_FILE}" > /dev/null; then
                rm -f "${ZIP_FILE}"
                exit 1
            fi
            unzip -o "${ZIP_FILE}"
        fi
    else
        wget -q -O "${ZIP_FILE}" "${REMOTE_URL}"
        if ! unzip -t "${ZIP_FILE}" > /dev/null; then
            rm -f "${ZIP_FILE}"
            exit 1
        fi
        unzip -o "${ZIP_FILE}"
    fi
else
    echo -e "Not updating game server as auto update is off. Starting Server"
fi

chmod +x ./${SERVER_BINARY}

# Define the log file path with a timestamp
LOG_FILE="/home/container/serverprofile/rpt/arma3server_$(date '+%m_%d_%Y_%H%M%S').rpt"

# Ensure the logs directory exists
mkdir -p /home/container/serverprofile/rpt

# Clear HC cache, if specified
if [[ ${CLEAR_CACHE} == "1" ]]; then
    echo -e "\n${GREEN}[STARTUP]: ${CYAN}Clearing Headless Client profiles cache...${NC}"
    for profileDir in ./serverprofile/home/*
    do
        [ "$profileDir" = "./serverprofile/home/Player" ] && continue
        rm -rf $profileDir
    done
fi

# Setup NSS Wrapper for use ($NSS_WRAPPER_PASSWD and $NSS_WRAPPER_GROUP have been set by the Dockerfile)
export USER_ID=$(id -u)
export GROUP_ID=$(id -g)
envsubst < /passwd.template > ${NSS_WRAPPER_PASSWD}

if [[ ${SERVER_BINARY} == *"x64"* ]]; then # Check which libnss-wrapper architecture to run, based off the server binary name
    export LD_PRELOAD=/usr/lib/x86_64-linux-gnu/libnss_wrapper.so
else
    export LD_PRELOAD=/usr/lib/i386-linux-gnu/libnss_wrapper.so
fi

# Replace Startup Variables
modifiedStartup=`eval echo $(echo ${STARTUP} | sed -e 's/{{/${/g' -e 's/}}/}/g')`

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

# Start the Server
echo -e "\n${GREEN}[STARTUP]:${NC} Starting server with the following startup command:"
echo -e "${CYAN}${modifiedStartup}${NC}\n"
if [[ "$STARTUP_PARAMS" == *"-noLogs"* ]]; then
	${modifiedStartup}
else
    ${modifiedStartup} 2>&1 | tee -a "$LOG_FILE"
fi

if [ $? -ne 0 ]; then
    echo -e "\n${RED}PTDL_CONTAINER_ERR: There was an error while attempting to run the start command.${NC}\n"
    exit 1
fi