#!/bin/bash
cd /home/container

# Print current Go version
GO_VER=`go version 2>&1 | awk -F ' ' '{print $3}'`
echo "Go version: ${GO_VER}"

# Make internal Docker IP address available to processes
export INTERNAL_IP=`ip route get 1 | awk '{print $(NF-2);exit}'`

# Replace startup variables.
MODIFIED_STARTUP=$(echo "${STARTUP}" | sed -e 's/{{/${/g' -e 's/}}/}/g' | eval echo "$(cat -)")

# Print startup command to console
echo -e "\033[1;33mcustomer@apollopanel:~\$\033[0m ${MODIFIED_STARTUP}"

# Run the server.
eval ${MODIFIED_STARTUP}