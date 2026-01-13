#!/usr/bin/env bash
# UPDATE_SERVER AUTO_UPDATE_CHOICE TXADMIN_ENABLE TXADMIN_PORT STEAM_WEBAPIKEY DOWNLOAD_URL FIVEM_VERSION SERVER_HOSTNAME FIVEM_LICENSE MAX_PLAYERS 

# Global Variables and configuration
TXADMIN_DIR="/home/container/txData"
TXADMIN_CONFIG_FILE="/home/container/txData/default/config.json"
SERVER_PORT="${SERVER_PORT:-30123}"
VERSION_FILE="/home/container/fivem_version.txt" 
TXADMIN_INSTALLED=false

if [[ -f "$VERSION_FILE" ]]; then
    LAST_VERSION=$(cat "$VERSION_FILE")
else
    LAST_VERSION=""
fi

if [[ -f $TXADMIN_CONFIG_FILE ]]; then
    TXADMIN_INSTALLED=true
    TXADMIN_VERSION=$(jq '.version' "$TXADMIN_CONFIG_FILE" -r)
    if [[ $TXADMIN_VERSION == "2" ]];then
        TXADMIN_SERVER_PATH=$(jq -r '.server.dataPath' $TXADMIN_CONFIG_FILE)
    else
        TXADMIN_SERVER_PATH=$(jq -r '.fxRunner.serverDataPath' $TXADMIN_CONFIG_FILE)
    fi
fi
[[ $TXADMIN_SERVER_PATH ]] && SERVER_CONFIG_FILE=$(find "$TXADMIN_SERVER_PATH" -type f -name "server.cfg" | head -n 1)


# Functions
add_logo(){
    # Needs imagemagick
    local  size new_image
    new_image=$(find ./ -maxdepth 1 -name "*.png"  | head -n 1)
    [[ -z $new_image ]] && new_image=$(find ./ -maxdepth 1 -name "*.jpg"  | head -n 1)
    [[ -z $new_image ]] && new_image=$(find ./ -maxdepth 1 -name "*.jpeg"  | head -n 1)
    [[ -z $new_image ]] && return 1
    if [[ -f $new_image ]];  then
    if [[ $(file -b --mime-type "$new_image") != "image/png" ]]; then
        convert "$new_image" "${new_image%.*}.png"
        new_image="${new_image%.*}.png"
    fi
    size=$(identify -format "%wx%h" "$new_image")
    if [[ "$size" != "96x96" ]]; then
        convert "$new_image" -resize 96x96\! "${new_image%.*}.png"
        new_image="${new_image%.*}.png"
    fi
    fi
    sed -i '/load_server_icon/d; /sv_hostname.*/a load_server_icon "'"$new_image"'"' server.cfg
}

update_resources(){
    if git clone -v https://github.com/citizenfx/cfx-server-data.git /tmp &>/dev/null; then
        cp -Rf /tmp/resources/* resources/
        echo "[STARTUP]: CitizenFX Resources updated successfully!"
        return 0
    else
        echo -e "[STARTUP]: Git clone operation failed, skipping..."
        return 1
    fi
}

# If session manager does not exist, the server will not work properly. It's safe to assume the default resources were broken in some way
fix_default_resources(){
    local session_manager_check
    session_manager_check=$(find ./resources -type d -name sessionmanager | head -1) 
    [[ -z ${session_manager_check} ]] && update_resources 
    return 0
}

fix_missing_artifacts(){
    if [[ ! -d alpine || ! -f alpine/opt/cfx-server/ld-musl-x86_64.so.1 ]]; then
        LAST_VERSION="" # Resetting last version so updating works
        update_artifacts
    fi
}

generate_download_link(){
    local release_page changelogs_page version_link
    release_page=$(curl -sSL https://runtime.fivem.net/artifacts/fivem/build_proot_linux/master/)
    changelogs_page=$(curl -sSL https://changelogs-live.fivem.net/api/changelog/versions/linux/server)

    if [[ "${FIVEM_VERSION}" == "recommended" || -z "${FIVEM_VERSION}" ]]; then
        TARGET_VERSION=$(echo "$changelogs_page" | jq -r '.recommended')
        DOWNLOAD_LINK=$(echo "$changelogs_page" | jq -r '.recommended_download')
    elif [[ "${FIVEM_VERSION}" == "latest" ]]; then
        TARGET_VERSION=$(echo "$changelogs_page" | jq -r '.latest')
        DOWNLOAD_LINK=$(echo "$changelogs_page" | jq -r '.latest_download')
    else
        version_link=$(echo "${release_page}" | grep -Eo '".*/*.tar.xz"' | sed 's/\"//g' | sed 's/\.\///1' | grep -i "${FIVEM_VERSION}" | grep -o =.* | tr -d '=')
        if [[ -z "$version_link" ]]; then
            echo "Defaulting to recommended version as the requested version was invalid."
            TARGET_VERSION=$(echo "$changelogs_page" | jq -r '.recommended')
            DOWNLOAD_LINK=$(echo "$changelogs_page" | jq -r '.recommended_download')
        else
            TARGET_VERSION="${version_link}"
            DOWNLOAD_LINK="https://runtime.fivem.net/artifacts/fivem/build_proot_linux/master/${version_link}"
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
}

update_artifacts(){
    local filetype
    generate_download_link # Gives us DOWNLOAD_LINK and TARGET_VERSION globals

    echo "Current installed version: $LAST_VERSION"
    echo "Target version: $TARGET_VERSION"

    if [[ "$LAST_VERSION" == "$TARGET_VERSION" ]]; then
        echo "Version is up-to-date, skipping download."
        return 0 
    fi

    echo "New version detected. Downloading from: ${DOWNLOAD_LINK}"
    curl -sSL "${DOWNLOAD_LINK}" -o "${DOWNLOAD_LINK##*/}"

    filetype=$(file -F ',' "${DOWNLOAD_LINK##*/}" | cut -d',' -f2 | cut -d' ' -f2)
    if [[ "$filetype" == "gzip" ]]; then
        tar xzvf "${DOWNLOAD_LINK##*/}"
    elif [[ "$filetype" == "Zip" ]]; then
        unzip "${DOWNLOAD_LINK##*/}"
    elif [[ "$filetype" == "XZ" ]]; then
        tar xvf "${DOWNLOAD_LINK##*/}"
    else
        echo "Unknown filetype: $filetype. Exiting."
        exit 2
    fi

    rm -rf "${DOWNLOAD_LINK##*/}" run.sh
    echo "$TARGET_VERSION" > "$VERSION_FILE"
    echo "Updated to version: $TARGET_VERSION"
}

fix_configuration(){
    if [[ -z "$SERVER_CONFIG_FILE" ]]; then
        echo "server.cfg not found in $TXADMIN_SERVER_PATH. Skipping port fix."
    else
        sed -i -E "s|(endpoint_add_tcp\s+\"0\.0\.0\.0:)[0-9]+\"|\1${SERVER_PORT}\"|g" "$cfg_file"
        sed -i -E "s|(endpoint_add_udp\s+\"0\.0\.0\.0:)[0-9]+\"|\1${SERVER_PORT}\"|g" "$cfg_file"

        echo "✔ Updated endpoints in: $cfg_file with port $SERVER_PORT"
    fi
}

sleep 5
cd /home/container

if [[ ${UPDATE_SERVER} == 1 ]]; then
    case $AUTO_UPDATE_CHOICE in
        both)
            update_artifacts
            update_resources
        ;;
        artifacts)
            update_artifacts
        ;;
        resources)
            update_resources
        ;;
    esac
fi

fix_missing_artifacts
fix_default_resources
fix_configuration
add_logo 

mkdir -p logs/

MODIFIED_STARTUP=$(echo "${STARTUP}" | sed -e 's/{{/${/g' -e 's/}}/}/g')

echo -e "\033[1;33mcustomer@apollopanel:~\$\033[0m ${MODIFIED_STARTUP}"
eval "${MODIFIED_STARTUP}"
