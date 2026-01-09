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

sleep 1

cd /home/container

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

# Setup NSS Wrapper for use ($NSS_WRAPPER_PASSWD and $NSS_WRAPPER_GROUP have been set by the Dockerfile)
export USER_ID=$(id -u)
export GROUP_ID=$(id -g)
envsubst < /passwd.template > ${NSS_WRAPPER_PASSWD}
export LD_PRELOAD=/usr/lib/x86_64-linux-gnu/libnss_wrapper.so

MODIFIED_STARTUP=$(echo ${STARTUP} | sed -e 's/{{/${/g' -e 's/}}/}/g')

echo -e "\033[1;33mcustomer@apollopanel:~\$\033[0m ${MODIFIED_STARTUP}"

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
eval ${MODIFIED_STARTUP}
