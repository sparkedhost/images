sleep 5
cd /home/container

VERSION_FILE="/home/container/fivem_version.txt"

if [[ "${AUTO_UPDATE}" == "1" ]]; then
    RELEASE_PAGE=$(curl -sSL https://runtime.fivem.net/artifacts/fivem/build_proot_linux/master/)
    CHANGELOGS_PAGE=$(curl -sSL https://changelogs-live.fivem.net/api/changelog/versions/linux/server)

    if [[ "${FIVEM_VERSION}" == "recommended" || -z "${FIVEM_VERSION}" ]]; then
        TARGET_VERSION=$(echo "$CHANGELOGS_PAGE" | jq -r '.recommended')
        DOWNLOAD_LINK=$(echo "$CHANGELOGS_PAGE" | jq -r '.recommended_download')
    elif [[ "${FIVEM_VERSION}" == "latest" ]]; then
        TARGET_VERSION=$(echo "$CHANGELOGS_PAGE" | jq -r '.latest')
        DOWNLOAD_LINK=$(echo "$CHANGELOGS_PAGE" | jq -r '.latest_download')
    else
        VERSION_LINK=$(echo "${RELEASE_PAGE}" | grep -Eo '".*/*.tar.xz"' | sed 's/\"//g' | sed 's/\.\///1' | grep -i "${FIVEM_VERSION}" | grep -o =.* | tr -d '=')
        if [[ -z "$VERSION_LINK" ]]; then
            echo "Defaulting to recommended version as the requested version was invalid."
            TARGET_VERSION=$(echo "$CHANGELOGS_PAGE" | jq -r '.recommended')
            DOWNLOAD_LINK=$(echo "$CHANGELOGS_PAGE" | jq -r '.recommended_download')
        else
            TARGET_VERSION="${VERSION_LINK}"
            DOWNLOAD_LINK="https://runtime.fivem.net/artifacts/fivem/build_proot_linux/master/${VERSION_LINK}"
        fi
    fi

    if [[ -n "${DOWNLOAD_URL}" ]]; then
        if curl --output /dev/null --silent --head --fail "${DOWNLOAD_URL}"; then
            echo "Overriding download link with DOWNLOAD_URL."
            DOWNLOAD_LINK="${DOWNLOAD_URL}"
        else
            echo "DOWNLOAD_URL is invalid. Exiting."
            exit 2
        fi
    fi

    if [[ -f "$VERSION_FILE" ]]; then
        LAST_VERSION=$(cat "$VERSION_FILE")
    else
        LAST_VERSION=""
    fi

    echo "Current installed version: $LAST_VERSION"
    echo "Target version: $TARGET_VERSION"

    if [[ "$LAST_VERSION" == "$TARGET_VERSION" ]]; then
        echo "Version is up-to-date, skipping download."
    else
        echo "New version detected. Downloading from: ${DOWNLOAD_LINK}"
        curl -sSL "${DOWNLOAD_LINK}" -o "${DOWNLOAD_LINK##*/}"

        FILETYPE=$(file -F ',' "${DOWNLOAD_LINK##*/}" | cut -d',' -f2 | cut -d' ' -f2)
        if [[ "$FILETYPE" == "gzip" ]]; then
            tar xzvf "${DOWNLOAD_LINK##*/}"
        elif [[ "$FILETYPE" == "Zip" ]]; then
            unzip "${DOWNLOAD_LINK##*/}"
        elif [[ "$FILETYPE" == "XZ" ]]; then
            tar xvf "${DOWNLOAD_LINK##*/}"
        else
            echo "Unknown filetype: $FILETYPE. Exiting."
            exit 2
        fi

        rm -rf "${DOWNLOAD_LINK##*/}" run.sh
        echo "$TARGET_VERSION" > "$VERSION_FILE"
        echo "Updated to version: $TARGET_VERSION"
    fi
fi

## Fix Port issue with txAdmin
BASE_DIR="/home/container/txData"
SERVER_PORT="${SERVER_PORT:-30123}"

if [[ ! -d "$BASE_DIR" ]]; then
    echo "Directory $BASE_DIR does not exist. Skipping port fix."
else
    CFG_FILE=$(find "$BASE_DIR" -type f -name "server.cfg" | head -n 1)
    if [[ -z "$CFG_FILE" ]]; then
        echo "server.cfg not found in $BASE_DIR. Skipping port fix."
    else
        sed -i -E "s|(endpoint_add_tcp\s+\"0\.0\.0\.0:)[0-9]+\"|\1${SERVER_PORT}\"|g" "$CFG_FILE"
        sed -i -E "s|(endpoint_add_udp\s+\"0\.0\.0\.0:)[0-9]+\"|\1${SERVER_PORT}\"|g" "$CFG_FILE"
        echo "✔ Updated endpoints in: $CFG_FILE with port $SERVER_PORT"
    fi
fi

mkdir -p logs/

MODIFIED_STARTUP=$(echo "${STARTUP}" | sed -e 's/{{/${/g' -e 's/}}/}/g')

echo -e "\033[1;33mcustomer@apollopanel:~\$\033[0m ${MODIFIED_STARTUP}"
eval "${MODIFIED_STARTUP}"
