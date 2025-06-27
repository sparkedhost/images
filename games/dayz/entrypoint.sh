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

eval ${MODIFIED_STARTUP}
