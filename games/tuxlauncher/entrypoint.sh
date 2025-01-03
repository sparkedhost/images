#!/bin/bash

sleep 1

cd /home/container

# Color Codes
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Define log file path and send stder and sdout to logfile - Only used if DEBUG is enabled in the admin panel / egg
if [ "${DEBUG}" == "1" ]; then
    echo -e "${RED}Debug logging is enabled! Check ./Astrotux.log for errors on startup..${NC}"
    LOG_FILE="/home/container/AstroTux.log"
    log_message() {
        echo "$(date +'%Y-%m-%d %H:%M:%S') $1" >> "$LOG_FILE"
        }
    exec > >(tee -a "$LOG_FILE")
    exec 2>&1
    log_message "Game Logging Started."
fi

echo -e "${RED} ---------------------------------------------------------- ${NC}"
echo -e "${GREEN}Running on Debian ${GREEN} $(cat /etc/debian_version)${NC}"
echo -e "${GREEN}Kernel Version: ${GREEN} $(uname -r)${NC}"
echo -e "${GREEN}Current Time Zone: ${GREEN} $(cat /etc/timezone)${NC}"
echo -e "${GREEN}Current Wine Version:${GREEN} $(wine --version)${NC}"
echo -e "${RED} ---------------------------------------------------------- ${NC}"

if [ -f "/home/container/AstroTuxVersion" ]; then
    echo -e "${YELLOW}[WARNING]: ${NC}Python has been removed! Please ensure your egg is up to date and a reinstall has been ran!${NC}"
    sleep 5
fi

# Make internal Docker IP address available to processes.
INTERNAL_IP=$(ip route get 1 | awk '{print $(NF-2);exit}')
export INTERNAL_IP
mkdir -p $WINEPREFIX

# Check if AUTO_UPDATE is not set or set to 1 to update TuxServer
if [ -z "${AUTO_UPDATE}" ] || [ "${AUTO_UPDATE}" == "1" ]; then
    GIT_API=$(curl --silent "https://api.github.com/repos/JoeJoeTV/AstroTuxLauncher/releases/latest")
    DOWNLOAD_URL=$(echo ${GIT_API} | jq .assets | jq -r .[].browser_download_url | grep -i "AstroTuxLauncher")
    VERSION=$(echo ${DOWNLOAD_URL} | grep -oP '(?<=/download/)[^/]+')
    if [ -f "/home/container/AstroTuxVersion" ]; then
        STORED_VERSION=$(cat "/home/container/AstroTuxVersion")
    else
        STORED_VERSION=""
    fi
    if [ "${VERSION}" != "${STORED_VERSION}" ]; then
        echo -e "${BLUE}[UPDATER]: ${NC}Current version ${YELLOW}${STORED_VERSION}${NC} is outdated. Updating to ${YELLOW}${VERSION}${NC}"
        echo "${VERSION}" > /home/container/AstroTuxVersion
        echo -e "${BLUE}[UPDATER]: ${NC}Downloading the latest version of TuxLauncher. This may take a moment!"
        curl -sSL "${DOWNLOAD_URL}" -o /home/container/AstroTuxLauncher
        chmod +x /home/container/AstroTuxLauncher
    fi
else
    echo -e "${BLUE}[UPDATER]: ${NC}Not updating game server as AUTO_UPDATE was Disabled. Starting Server!"
fi

# Replace Startup Variables
MODIFIED_STARTUP=$(echo ${STARTUP} | sed -e 's/{{/${/g' -e 's/}}/}/g')
echo -e "\033[1;33mcustomer@apollopanel:~\$\033[0m ${MODIFIED_STARTUP}"

eval ${MODIFIED_STARTUP}
