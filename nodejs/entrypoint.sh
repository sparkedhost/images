#!/bin/bash
cd /home/container

# # Check if NODE_OPTIONS is already set
# if [[ -z "$NODE_OPTIONS" ]]; then
#   # If not set, set it to --use-openssl-ca
#   export NODE_OPTIONS="--use-openssl-ca"
# else
#   # If already set, append --use-openssl-ca
#   export NODE_OPTIONS="$NODE_OPTIONS --use-openssl-ca"
# fi

# Output Current Software Versions
NODE_VER=`node -v 2>&1 | head -1 | cut -d'"' -f2 | sed '/^1\./s///'`
echo "NodeJS version: ${NODE_VER}"

NPM_VER=`npm -v 2>&1 | head -1 | cut -d'"' -f2 | sed '/^1\./s///'`
echo "NPM version: ${NPM_VER}"

YARN_VER=`yarn -v 2>&1 | head -1 | cut -d'"' -f2 | sed '/^1\./s///'`
echo "Yarn version: ${YARN_VER}"

GIT_VER=`git --version 2>&1 | cut -d' ' -f3 | sed '/^1\./s///'`
echo "Git version: ${GIT_VER}"

DJS_VER=`npm list --omit peer --depth 0 discord.js | grep -oE '[0-9.]+$'`
if [ -z "$DJS_VER" ]
then
    DJS_VER="(not installed)"
fi
echo "Discord.js version: ${DJS_VER}"

# Make internal Docker IP address available to processes.
export INTERNAL_IP=`ip route get 1 | awk '{print $(NF-2);exit}'`

# Disable NPM update notifications (https://github.com/sparkedhost/images/issues/28)
echo "update-notifier=false" > ~/.npmrc

# Replace Startup Variables
MODIFIED_STARTUP=$(echo -e ${STARTUP} | sed -e 's/{{/${/g' -e 's/}}/}/g')
echo "customer@apollopanel:~# ${MODIFIED_STARTUP}"

# Run the Server.
eval ${MODIFIED_STARTUP}
